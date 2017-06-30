# silex-route-annotations

A service provider that allows you to use [@Route](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html) annotations in your Silex applications, in order to define routes directly in your Controllers.

## Installation

Install the library through Composer:

```bash
composer require mbezhanov/silex-route-annotations
```

## Usage

Register the Service Provider with your Application:

```php
<?php 

use Bezhanov\Silex\Routing\RouteAnnotationsProvider;
use Silex\Application;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$app = new Application();

$app->register(new RouteAnnotationsProvider(), [
    // mandatory to specify the path to your Controllers
    'routing.controller_dir' => __DIR__ . '/../src/App/Controllers',
    // not mandatory, but recommended to specify Cache Adapter
    'routing.cache_adapter' => new FilesystemAdapter('routing', 0, __DIR__ . '/../var/cache'),
]);

```

Then you can use the standard @Route annotations, familiar from [SensioFrameworkExtraBundle](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html) / [Symfony](https://symfony.com) like this:

```php
<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedClasses;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/foo")
 */
class FooController
{
    /**
     * @Route("/bar")
     */
    public function barAction()
    {
        // ...
    }

    /**
     * @Route("/baz/{lorem}")
     */
    public function bazAction($lorem = 'ipsum')
    {
        // ...
    }
}

```

## Contributing

This library is in its early stages of development, and all contributions are welcome. Before opening PRs, make sure that all tests are passing, and that code coverage is satisfactory:

```bash
phpunit tests --coverage-html coverage
```
