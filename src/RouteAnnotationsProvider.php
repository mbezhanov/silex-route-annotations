<?php

namespace Bezhanov\Silex\Routing;

use Doctrine\Common\Annotations\AnnotationReader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Annotation\Route;

class RouteAnnotationsProvider implements ServiceProviderInterface
{
    protected const ANNOTATION_DATA_CACHE_ITEM_KEY = 'routing.annotation_data';

    public function register(Container $container)
    {
        $container->extend('controllers', function (ControllerCollection $controllerCollection, Container $container) {

            if (!isset($container['routing.controller_dir']) || !is_dir($container['routing.controller_dir'])) {
                throw new \InvalidArgumentException('No controller directory found');
            }

            $cacheAdapter = $annotationDataCacheItem = null;

            if (isset($container['routing.cache_adapter'])) {
                $cacheAdapter = $container['routing.cache_adapter'];
                if (!$cacheAdapter instanceof AbstractAdapter) {
                    throw new \InvalidArgumentException(
                        sprintf('"routing.cache_adapter" must be an instance of "%s", "%s" given', AbstractAdapter::class, get_class($container['routing.cache_adapter']))
                    );
                }

                $annotationDataCacheItem = $cacheAdapter->getItem(static::ANNOTATION_DATA_CACHE_ITEM_KEY);

                if ($annotationDataCacheItem->isHit()) {
                    $this->addRoutes($controllerCollection, $annotationDataCacheItem->get());
                    return $controllerCollection;
                }
            }

            $fileLocator = new FileLocator();
            $reader = new AnnotationReader();
            $classLoader = new AnnotationClassLoader($reader, $controllerCollection);
            $directoryLoader = new AnnotationDirectoryLoader($fileLocator, $classLoader);
            $annotationClassDataCollection = $directoryLoader->load($container['routing.controller_dir']);

            if ($cacheAdapter && $annotationDataCacheItem) {
                $annotationDataCacheItem->set($annotationClassDataCollection);
                $cacheAdapter->save($annotationDataCacheItem);
            }
            $this->addRoutes($controllerCollection, $annotationClassDataCollection);

            return $controllerCollection;
        });
    }

    protected function addRoutes(ControllerCollection $controllerCollection, array $annotationClassDataCollection)
    {
        /** @var AnnotationClassData[] $annotationClassDataCollection */
        foreach ($annotationClassDataCollection as $classAnnotationData) {
            foreach ($classAnnotationData->getAnnotationMethodDataCollection() as $methodAnnotationData) {
                $this->addRoute($controllerCollection, $methodAnnotationData->getAnnotation(), $classAnnotationData->getClass(), $methodAnnotationData->getMethod());
            }
        }
    }

    protected function addRoute(ControllerCollection $controllerCollection, Route $annotation, \ReflectionClass $class, \ReflectionMethod $method)
    {
        $controller = $class->getName(). '::' . $method->getName();

        $route = $controllerCollection->match($annotation->getPath(), $controller)->bind($annotation->getName());

        /** @var \Silex\Route $route */
        $route->addDefaults($annotation->getDefaults())
            ->setRequirements($annotation->getRequirements())
            ->setOptions($annotation->getOptions())
            ->setHost($annotation->getHost())
            ->setSchemes($annotation->getSchemes())
            ->setMethods($annotation->getMethods())
            ->setCondition($annotation->getCondition());
    }
}
