<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\IncorrectAnnotations;

use Bezhanov\Silex\Routing\Route;

class BarController
{
    /**
     * @Route("/bar")
     */
    public function bar()
    {
    }
}
