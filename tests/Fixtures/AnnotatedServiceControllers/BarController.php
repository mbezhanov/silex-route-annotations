<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedServiceControllers;

use Bezhanov\Silex\Routing\Route;

class BarController
{
    /**
     * @Route("/bar")
     */
    public function barAction()
    {

    }
}
