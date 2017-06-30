<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\IncorrectAnnotations;

use Symfony\Component\Routing\Annotation\Route;

class FooController
{
    /**
     * @Route("/foo")
     */
    private function fooAction()
    {
    }
}
