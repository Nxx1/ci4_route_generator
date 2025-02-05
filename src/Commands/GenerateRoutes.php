<?php

namespace Akara\RouteGenerator\Commands;

use Akara\RouteGenerator\CustomRouter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class GenerateRoutes extends BaseCommand
{
    protected $group       = 'Generators';
    protected $name        = 'make:route';
    protected $description = 'Run route generation';

    public function run(array $params)
    {
        CLI::write('Running route generator...');

        // Get all namespaces
        $autoload = Services::autoloader()->getNamespace();

        $namespaces = [];

        foreach ($autoload as $key => $value) {
            $namespaces[] = $key . "\\Controllers";
        }

        $routes = Services::routes();
        $customRouter = new CustomRouter($routes, Services::request(), $namespaces);
        $customRouter->initialize();

        CLI::write('Generate routes complete!');
    }
}
