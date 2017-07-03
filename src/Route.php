<?php

namespace Bezhanov\Silex\Routing;

/**
 * @Annotation
 */
class Route extends \Symfony\Component\Routing\Annotation\Route
{
    private $service;

    public function setService($service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }
}
