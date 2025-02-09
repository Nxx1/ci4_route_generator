# Codeigniter 4 Route Generator

The **Codeigniter 4 Route Generator** is a PHP package designed to automate the generation of routes for CodeIgniter 4 applications. It scans your controller classes, extracts route information from custom attributes, and automatically generates route definitions in a specified routes file.

## Installation

To install the package via Composer, run the following command:

```bash
composer require akara/ci4-route-generator
```

## Usage

### 1. Define Routes Using Attributes

In your controller classes, use the `RoutePath` attribute to define routes. For example:

```php
namespace App\Controllers;

use Akara\RouteGenerator\RoutePath;
use CodeIgniter\Controller;

class UserController extends Controller
{
    #[RoutePath('/users', 'get')]
    public function index()
    {
        // Your logic here
    }

    #[RoutePath('/users/{id}', 'get', ['auth'])]
    public function show($id)
    {
        // Your logic here
    }
}
```

### 2. Run the Route Generator

To generate routes, run the following command in your terminal:

```bash
php spark make:route
```

This command will scan all controller classes within the specified namespaces, extract route information, and append the generated routes to the `Config/RoutesCustom.php` file.

### 3. Include Generated Routes

Add the following line to `app/config/Routes.php` to include the generated routes:

```bash
namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

include_once('RoutesCustom.php');

```

With these steps, your routes will be automatically generated and added to your application, simplifying the management of routes.

## Code Documentation

### `GenerateRoutes` Command

The `GenerateRoutes` command is responsible for initiating the route generation process.

#### Properties

- **`$group`**: The command group (e.g., `Generators`).
- **`$name`**: The command name (e.g., `make:route`).
- **`$description`**: A brief description of the command.

#### Methods

- **`run(array $params)`**: Executes the route generation process. It retrieves all namespaces from the autoloader, initializes the `CustomRouter`, and writes the generated routes to the routes file.

### `CustomRouter` Class

The `CustomRouter` class extends the CodeIgniter `Router` and handles the generation of routes based on the scanned controller classes.

#### Properties

- **`$controllerNamespaces`**: An array of namespaces to scan for controller classes.

#### Methods

- **`__construct(RouteCollectionInterface $routes, RequestInterface $request = null, array $controllerNamespace = [])`**: Initializes the `CustomRouter` with the provided routes, request, and namespaces.
- **`initialize()`**: Scans the specified namespaces for controller classes and generates routes based on the `RoutePath` attributes.
- **`addRoutesToFile(array $routes)`**: Writes the generated routes to the `RoutesCustom.php` file.

### `RoutePath` Attribute

The `RoutePath` attribute is used to define route information in controller methods.

#### Properties

- **`$path`**: The route path.
- **`$method`**: The HTTP method (e.g., `get`, `post`).
- **`$filters`**: An array of filters to apply to the route.

### `RouteScanner` Class

The `RouteScanner` class is responsible for scanning controller classes and extracting route information from the `RoutePath` attributes.

#### Methods

- **`scan(string $controllerNamespace)`**: Scans the specified namespace for controller classes and extracts route information.
- **`getControllerClasses($namespace)`**: Retrieves all controller classes within the specified namespace.
- **`processAttributes($method)`**: Processes the `RoutePath` attributes of a controller method and returns the route information.
- **`convertNamespaceToPath($namespace)`**: Converts a namespace to a filesystem path.

## Example

### Controller with `RoutePath` Attributes

```php
namespace App\Controllers;

use Akara\RouteGenerator\RoutePath;
use App\Filters\SessionFilters;
use CodeIgniter\Controller;

class UserController extends Controller
{
    #[RoutePath('/users', 'GET')]
    public function index()
    {
        // Your logic here
    }

    #[RoutePath('/users/{id}', 'GET', [SessionFilters::class])]
    public function show($id)
    {
        // Your logic here
    }
}
```

### Generated Routes

After running the `make:route` command, the following routes will be added to `Config/RoutesCustom.php`:

```php
<?php

// Auto-generated routes
$routes->get('/users',  [App\Controllers\UserController::class, 'index'], ['filter' => []]);
$routes->get('/users/{id}',  [App\Controllers\UserController::class, 'show'], ['filter' => [auth::class]]);
```

## Contributing

Contributions are welcome! Please open an issue or submit a pull request on the [GitHub repository](https://github.com/Nxx1/ci4_route_generator).

## License

This package is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).