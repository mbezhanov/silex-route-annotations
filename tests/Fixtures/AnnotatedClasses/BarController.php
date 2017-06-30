<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedClasses;

use Symfony\Component\Routing\Annotation\Route;

class BarController extends AbstractController
{
    /**
     * @Route("/bar", methods={"GET"})
     */
    public function barAction()
    {
    }
}
