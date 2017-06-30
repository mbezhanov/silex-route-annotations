<?php

namespace Bezhanov\Silex\Routing\Tests;

use Bezhanov\Silex\Routing\RouteAnnotationsProvider;
use Bezhanov\Silex\Routing\Tests\Fixtures\IncorrectCacheImplementation;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Silex\Application;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\HttpFoundation\Request;

class RouteAnnotationsProviderTest extends TestCase
{
    private const EMPTY_CONTROLLER_DIRECTORY = __DIR__ . '/Fixtures/EmptyFolder';
    private const NOT_EMPTY_CONTROLLER_DIRECTORY = __DIR__ . '/Fixtures/AnnotatedClasses';

    public function testRegister()
    {
        $loader = require __DIR__ . '/../vendor/autoload.php';
        AnnotationRegistry::registerLoader([$loader, 'loadClass']);

        $app = new Application();
        $app->register(new RouteAnnotationsProvider(), [
            'routing.controller_dir' => self::NOT_EMPTY_CONTROLLER_DIRECTORY,
        ]);
        $app->boot();
        $routes = $app['controllers']->flush();
        $this->assertCount(3, $routes);
    }

    public function testRegisterWithoutControllerDir()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->runApplication();
    }

    public function testRegisterWithIncorrectCacheImplementation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->runApplication(self::EMPTY_CONTROLLER_DIRECTORY, new IncorrectCacheImplementation());
    }

    public function testRegisterWithCacheHit()
    {
        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->shouldBeCalledTimes(1)->willReturn(true);
        $cacheItem->get()->shouldBeCalledTimes(1)->willReturn([]);
        $cacheAdapter = $this->prophesize(AbstractAdapter::class);
        $cacheAdapter->getItem('routing.annotation_data')->shouldBeCalledTimes(1)->willReturn($cacheItem->reveal());
        $this->runApplication(self::EMPTY_CONTROLLER_DIRECTORY, $cacheAdapter->reveal());
    }

    public function testRegisterWithoutCacheHit()
    {
        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->shouldBeCalledTimes(1)->willReturn(false);
        $cacheItem->set([])->shouldBeCalledTimes(1);
        $cacheAdapter = $this->prophesize(AbstractAdapter::class);
        $cacheAdapter->getItem('routing.annotation_data')->shouldBeCalledTimes(1)->willReturn($cacheItem->reveal());
        $cacheAdapter->save($cacheItem->reveal())->shouldBeCalledTimes(1);
        $this->runApplication(self::EMPTY_CONTROLLER_DIRECTORY, $cacheAdapter->reveal());
    }

    private function runApplication(string $controllerPath = null, $cacheAdapter = null)
    {
        $app = new Application();
        $app->register(new RouteAnnotationsProvider(), [
            'routing.controller_dir' => $controllerPath,
            'routing.cache_adapter' => $cacheAdapter,
        ]);
        $app->handle(Request::createFromGlobals());
    }
}
