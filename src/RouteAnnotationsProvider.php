<?php

namespace Bezhanov\Silex\Routing;

use Doctrine\Common\Annotations\AnnotationReader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Config\FileLocator;

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
                    $this->addRoutes($container, $controllerCollection, $annotationDataCacheItem->get());
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
            $this->addRoutes($container, $controllerCollection, $annotationClassDataCollection);

            return $controllerCollection;
        });
    }

    protected function addRoutes(Container $container, ControllerCollection $controllerCollection, array $annotationClassDataCollection)
    {
        /** @var AnnotationClassData[] $annotationClassDataCollection */
        foreach ($annotationClassDataCollection as $classAnnotationData) {
            foreach ($classAnnotationData->getAnnotationMethodDataCollection() as $methodAnnotationData) {
                $this->addRoute($container, $controllerCollection, $methodAnnotationData->getAnnotation(), $classAnnotationData->getClass(), $methodAnnotationData->getMethod());
            }
        }
    }

    protected function addRoute(Container $container, ControllerCollection $controllerCollection, Route $annotation, \ReflectionClass $class, \ReflectionMethod $method)
    {
        $controller = $this->resolveController($container, $annotation, $class, $method);
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

    protected function resolveController(Container $container, Route $annotation, \ReflectionClass $class, \ReflectionMethod $method): string
    {
        if ($serviceName = $annotation->getService()) {
            if (!isset($container[$serviceName])) {
                throw new \RuntimeException(sprintf('No service "%s" found in the service container', $serviceName));
            }
            return $serviceName . ':' . $method->getName();
        } elseif ($serviceName = $this->guessService($class)) {
            if (isset($container[$serviceName])) {
                return $serviceName . ':' . $method->getName();
            }
        }
        return $class->getName() . '::' . $method->getName();
    }

    protected function guessService(\ReflectionClass $class): string
    {
        $className = str_replace('\\', '.', $class->getName());

        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $className));
    }
}
