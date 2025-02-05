<?php

namespace Akara\RouteGenerator;

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

            // Format for adding to Routes.php
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

        log_message('info', 'Routes have been auto-added to ' . $routesFilePath);
    }
}
