<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\IncorrectAnnotations;

use Bezhanov\Silex\Routing\Route;

class FooController
{
    /**
     * @Route("/foo")
     */
    private function fooAction()
    {
    }
}
