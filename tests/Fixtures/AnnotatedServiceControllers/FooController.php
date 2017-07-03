<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedServiceControllers;

use Bezhanov\Silex\Routing\Route;

/**
 * @Route(service="foo.bar_baz")
 */
class FooController
{
    /**
     * @Route("/foo")
     */
    public function fooAction()
    {
    }
}
