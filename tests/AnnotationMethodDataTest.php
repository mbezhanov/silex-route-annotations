<?php

namespace Bezhanov\Silex\Routing\Tests;

use Bezhanov\Silex\Routing\AnnotationMethodData;
use Bezhanov\Silex\Routing\Route;
use PHPUnit\Framework\TestCase;

class AnnotationMethodDataTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $reflectionMethod = $this->prophesize(\ReflectionMethod::class)->reveal();
        $routeAnnotation = $this->prophesize(Route::class)->reveal();
        $annotationMethodMetadata = new AnnotationMethodData($reflectionMethod, $routeAnnotation);
        $this->assertSame($reflectionMethod, $annotationMethodMetadata->getMethod());
        $this->assertSame($routeAnnotation, $annotationMethodMetadata->getAnnotation());
    }
}
