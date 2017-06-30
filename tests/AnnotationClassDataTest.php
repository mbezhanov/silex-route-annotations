<?php

namespace Bezhanov\Silex\Routing\Tests;

use Bezhanov\Silex\Routing\AnnotationClassData;
use Bezhanov\Silex\Routing\AnnotationMethodData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Annotation\Route;

class AnnotationClassDataTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $reflectionClass = $this->prophesize(\ReflectionClass::class)->reveal();
        $annotationMethodDataCollection = [
            $this->prophesize(AnnotationMethodData::class)->reveal(),
            $this->prophesize(AnnotationMethodData::class)->reveal(),
            $this->prophesize(AnnotationMethodData::class)->reveal(),
        ];
        $annotationClassData = new AnnotationClassData($reflectionClass, $annotationMethodDataCollection);
        $this->assertSame($reflectionClass, $annotationClassData->getClass());
        $this->assertSame($annotationMethodDataCollection, $annotationClassData->getAnnotationMethodDataCollection());
    }
}
