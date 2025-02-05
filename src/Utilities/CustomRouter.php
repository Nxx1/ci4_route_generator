<?php

namespace Akara\RouteGenerator\Utilities;

use CodeIgniter\Router\Router;
use CodeIgniter\Router\RouteCollectionInterface;
use CodeIgniter\HTTP\RequestInterface;
use Exception;

class CustomRouter extends Router
{
    private $controllerNamespaces;

    public function __construct(RouteCollectionInterface $routes, RequestInterface $request = null, array $controllerNamespace = [])
    {
        parent::__construct($routes, $request);

        $this->controllerNamespaces = $controllerNamespace;
    }

    public function initialize()
    {
        $scanner = new RouteScanner();

        $allRoutes = [];

        foreach ($this->controllerNamespaces as $namespace) {
            $routes = $scanner->scan($namespace);

            if (empty($routes)) {
                log_message('error', "No routes found in namespace: $namespace");
            } else {
                foreach ($routes as $route) {
                    $allRoutes[] = $route;
                }
            }
        }
        $this->addRoutesToFile($allRoutes);
    }

    protected function addRouteWithPattern($routeInfo)
    {
        // Check if 'method' key is set and is a non-empty string
        $method = strtolower($routeInfo['method']);
        // log_message('debug', "Processing route: Method: $method, Path: {$routeInfo['path']}, Action: {$routeInfo['action']}");

        if (!in_array($method, ['get', 'post', 'put', 'delete', 'patch', 'options', 'head', ''], true)) {
            log_message('error', "Unsupported HTTP method attempted: $method");
            throw new \Exception("Unsupported HTTP method  $method attempted in routing.");
        }

        if (!empty($method)) {
            $action = $routeInfo['action'];
            $path = $routeInfo['path'] . ($routeInfo['pattern'] ?? '');
            $filter = $routeInfo['filter'] ?? [];
            $params = [];

            // Add the route
            // Dynamically call the method on the RouteCollection
            preg_match_all('/\([^()]+\)/', $path, $match);

            if (isset($match[0][0])) {
                $actionArray = [$action];
                for ($i = 1; $i <= count($match[0]); $i++) {
                    $actionArray[] = '$' . $i;
                }

                $action = implode('/', $actionArray);
            }

            $actionexplode = explode('\\', $action);
            $action = end($actionexplode);
            array_pop($actionexplode);
            $actionnamespace = implode('\\', $actionexplode);
            // log_message('debug', "Adding route: Method: $method, Path: $path, Namespace: $actionnamespace, Action: $action");

            if (count($filter) > 0) {
                $params['filter'] = $filter;
            }

            $this->collection->$method("" . $path, "\\" . $actionnamespace . "\\" . $action, $params);
        }
    }

    protected function addRoutesToFile(array $routes)
    {
        // Define the path to your Routes.php file
        $routesFilePath = APPPATH . 'Config/RoutesCustom.php';

        // Open the Routes.php file
        if (!is_writable($routesFilePath)) {
            throw new Exception("The Routes.php file is not writable.");
        }

        // Read the current contents of Routes.php
        // $routesFileContent = file_get_contents($routesFilePath);
        $routesFileContent = "<?php\n";

        // Prepare the new routes to add
        $newRoutes = '';

        foreach ($routes as $route) {
            $method = strtolower($route['method']);
            $path = $route['path'];
            $className = $route['class']['name'];
            $classMethod = $route['class']['method'];

            $filters = '';
            foreach ($route['filter'] as $filter) {
                if ($filter != null) {

                    if ($filters != "") {
                        $filters .= ",";
                    }

                    $filters .= $filter . "::class";
                }
            }



            // Example format for adding to Routes.php
            if ($method != null) {
                $newRoutes .= "\$routes->$method('$path',  [$className::class, '$classMethod'], ['filter' => [$filters]]);\n";
            }
        }


        // Check if the routes are already present to avoid duplication
        if (strpos($routesFileContent, '$routes->') === false) {
            // If no routes exist in the file, append new routes at the end
            $routesFileContent .= "\n// Auto-generated routes\n" . $newRoutes;
        } else {
            // Otherwise, just append to the existing routes block
            $routesFileContent = preg_replace('/(\$routes->.*\n)(\s*\/\/\s*Auto-generated routes\n)/s', '$1' . $newRoutes, $routesFileContent);
        }

        // Save the modified content back to Routes.php
        file_put_contents($routesFilePath, $routesFileContent);

        log_message('info', 'Routes have been auto-added to Routes.php.');
    }
}
