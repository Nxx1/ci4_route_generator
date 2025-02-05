<?php

namespace Akara\RouteGenerator;

use CodeIgniter\Controller;
use ReflectionClass;

class RouteScanner
{
    public function scan(string $controllerNamespace)
    {
        $controllerClasses = $this->getControllerClasses($controllerNamespace);

        $routes = [];

        foreach ($controllerClasses as $class) {
            $reflectionClass = new ReflectionClass($class);

            foreach ($reflectionClass->getMethods() as $method) {
                $routeInfo = $this->processAttributes($method);
                if ($routeInfo) {
                    $routes[] = $routeInfo;
                }
            }
        }

        return $routes;
    }

    private function getControllerClasses($namespace)
    {
        $controllerClasses = [];
        $namespaceDir = $this->convertNamespaceToPath($namespace);

        // Check if the namespace directory exists
        if (is_dir($namespaceDir)) {
            log_message('debug', "Namespace directory: $namespaceDir");

            // Recursive directory iterator to scan all subdirectories
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($namespaceDir));

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {

                    $fullPath = $fileInfo->getPathname();
                    log_message('debug', "File Path: " . $fullPath);

                    // Convert the full path to a namespace
                    $relativePath = str_replace(ROOTPATH, '', $fullPath);
                    $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);

                    // Replace directory separator with a backslash (\)
                    $relativePath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
                    
                    // Capitalize each part of the path
                    $relativePath = implode('\\', array_map('ucwords', explode('\\', $relativePath)));

                    // Remove the file extension for class name
                    $className = str_replace('.php', '', $relativePath);

                    // Ensure the class exists and is a valid controller
                    if (class_exists($className)) {
                        $reflectionClass = new ReflectionClass($className);
                        if ($reflectionClass->isSubclassOf(Controller::class) && !$reflectionClass->isAbstract()) {
                            $controllerClasses[] = $className;
                            log_message('debug', "Class Name: " . $className);
                        }
                    }
                }
            }
        }

        return $controllerClasses;
    }

    private function processAttributes($method): array
    {
        $routeInfo = ['method' => '', 'path' => '', 'action' => '', 'filter' => []];

        foreach ($method->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if ($instance instanceof RoutePath) {
                $routeInfo['method'] = strtolower($instance->method);
                $routeInfo['path'] = $instance->path;
                $routeInfo['filter'] = $instance->filters ?? [];
            }
        }

        $routeInfo['action'] = $method->getDeclaringClass()->getName() . '::' . $method->getName();
        $routeInfo['class']['name'] = $method->getDeclaringClass()->getName();
        $routeInfo['class']['method'] = $method->getName();
        return $routeInfo;
    }

    private function convertNamespaceToPath($namespace)
    {
        $namespace = trim($namespace, '\\');
        return ROOTPATH . str_replace('\\', DIRECTORY_SEPARATOR, lcfirst($namespace));
    }
}
