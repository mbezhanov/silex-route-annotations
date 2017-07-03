<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedClasses;

use Bezhanov\Silex\Routing\Route;

class BarController extends AbstractController
{
    /**
     * @Route("/bar", methods={"GET"})
     */
    public function barAction()
    {
    }
}
