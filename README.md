# silex-route-annotations

A service provider that allows you to use [@Route](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html) annotations in your Silex applications, in order to define routes directly in your Controllers.

## Installation

Install the library through Composer:

```bash
composer require mbezhanov/silex-route-annotations
```

## Registering

To enable @Route annotations, register the Service Provider with your Application:

```php
<?php 

use Bezhanov\Silex\Routing\RouteAnnotationsProvider;
use Silex\Application;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$app = new Application();

$app->register(new RouteAnnotationsProvider(), [
    // it is required to specify the full path, where your Controllers reside
    'routing.controller_dir' => __DIR__ . '/../src/App/Controllers',
    // not required, but highly recommended to specify a Cache Adapter, in order to use caching
    'routing.cache_adapter' => new FilesystemAdapter('routing', 0, __DIR__ . '/../var/cache'),
]);

```

## Basic Usage

Registering the Service Provider with the Application allows you to use @Route annotations, identical to the ones from [SensioFrameworkExtraBundle](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html) / [Symfony](https://symfony.com) in your Controllers.
```php
<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedClasses;

use Bezhanov\Silex\Routing\Route;

/**
 * @Route("/foo")
 */
class FooController
{
    /**
     * @Route("/bar/{lorem}")
     */
    public function barAction($lorem = 'ipsum')
    {
        // ...
    }
}

```

## Service Controllers

As your application grows, you may find yourself utilizing the [Service Controllers](https://silex.sensiolabs.org/doc/2.0/providers/service_controller.html) mechanism that Silex provides, in order to inject external dependencies into your Controllers. The library gets you covered in such cases, as it allows you to have your Controllers instantiated through the Service Container out of the box:

```php
<?php

// Service Controller definition:
$app['foo.bar_baz'] = function ($app) {
    return new App\Controller\FooController($app['some_other_service']);
};

// Controller example:

namespace App\Controller;

use Bezhanov\Silex\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="foo.bar_baz") 
 */
class FooController
{
    private $service;
    
    public function __construct(SomeOtherService $service)
    {
        $this->service = $service;
    }
    
    /**
     * @Route("/foo")
     */
    public function fooAction()
    {
        $result = $this->service->doSomething();
        return new Response($result, Response::HTTP_OK);
    }
}

```

You can also omit the **service** option entirely from your @Route declaration, as long as your service ID matches the ID derived from your fully-qualified class name (FQCN), e.g.

```php
<?php

$app['app.controller.manufacturer_controller'] = function (Application $app) {
    return new App\Controller\FooController($app['some_other_service']);
};

```

Here, the Controller will be automatically instantiated through the Service Container, and you don't have to explicitly specify a **service** attribute in your class-level @Route annotation.

## Contributing

This library is in its early stages of development. All contributions are welcome. Before opening PRs, make sure that all tests are passing, and that code coverage is satisfactory:

```bash
phpunit tests --coverage-html coverage
```
