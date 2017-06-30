<?php

namespace Bezhanov\Silex\Routing\Tests;

use Bezhanov\Silex\Routing\AnnotationClassData;
use Bezhanov\Silex\Routing\AnnotationClassLoader;
use Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedClasses\AbstractController;
use Bezhanov\Silex\Routing\Tests\Fixtures\AnnotatedClasses\FooController;
use Bezhanov\Silex\Routing\Tests\Fixtures\IncorrectAnnotations\BarController as IncorrectBarController;
use Bezhanov\Silex\Routing\Tests\Fixtures\IncorrectAnnotations\FooController as IncorrectFooController;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;

class AnnotationClassLoaderTest extends TestCase
{
    /**
     * @var AnnotationClassLoader
     */
    protected $classLoader;

    public function setUp()
    {
        $this->classLoader = new AnnotationClassLoader(new AnnotationReader());
        $loader = require __DIR__ . '/../vendor/autoload.php';
        AnnotationRegistry::registerLoader([$loader, 'loadClass']);
    }

    public function testLoad()
    {
        $annotationClassData = $this->classLoader->load(FooController::class);
        $this->assertInstanceOf(AnnotationClassData::class, $annotationClassData);
        $this->assertEquals(FooController::class, $annotationClassData->getClass()->getName());
        $this->assertCount(2, $annotationClassData->getAnnotationMethodDataCollection());
    }

    public function testLoadMissingClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->classLoader->load('MissingClass');
    }

    public function testLoadAbstractClass()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->classLoader->load(AbstractController::class);
    }

    public function testLoadClassWithAnnotatedPrivateMethods()
    {
        $this->expectException(\RuntimeException::class);
        $this->classLoader->load(IncorrectFooController::class);
    }

    public function testLoadClassWithIncorrectlyNamedAnnotatedMethods()
    {
        $this->expectException(\RuntimeException::class);
        $this->classLoader->load(IncorrectBarController::class);
    }
}
