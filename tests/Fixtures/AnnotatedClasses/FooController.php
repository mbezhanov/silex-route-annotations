<?php

namespace Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedClasses;

use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/foo", host="m.example.com", condition="request.headers.get('User-Agent') matches '/firefox/i'")
 */
class FooController
{
    /**
     * @Route("/foo", methods={"GET"})
     */
    public function fooAction()
    {
    }

    /**
     * @Route("/bar/{id}")
     * @param $id
     */
    public function barAction($id = 'test')
    {

    }
}
