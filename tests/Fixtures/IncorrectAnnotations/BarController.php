<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\IncorrectAnnotations;

use Symfony\Component\Routing\Annotation\Route;

class BarController
{
    /**
     * @Route("/bar")
     */
    public function bar()
    {
    }
}
