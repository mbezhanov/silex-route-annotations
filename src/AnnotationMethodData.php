<?php

namespace Bezhanov\Silex\Routing;

class AnnotationMethodData
{
    private $method;

    private $annotation;

    public function __construct(\ReflectionMethod $method, Route $annotation)
    {
        $this->method = $method;
        $this->annotation = $annotation;
    }

    public function getMethod(): \ReflectionMethod
    {
        return $this->method;
    }

    public function getAnnotation(): Route
    {
        return $this->annotation;
    }
}
