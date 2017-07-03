<?php

namespace Bezhanov\Silex\Routing;

use Doctrine\Common\Annotations\Reader;

/**
 * AnnotationClassLoader loads routing information from a PHP class and its methods.
 *
 * @see \Symfony\Component\Routing\Loader\AnnotationClassLoader
 */
class AnnotationClassLoader
{
    protected $reader;

    protected $routeAnnotationClass = Route::class;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function load(string $class)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class->getName()));
        }

        $annotationMethodDataCollection = [];
        $globals = $this->getGlobals($class);

        foreach ($class->getMethods() as $method) {
            $annotation = $this->reader->getMethodAnnotation($method, $this->routeAnnotationClass);
            if ($annotation) {
                if (!$method->isPublic()) {
                    throw new \RuntimeException(sprintf('Method "%s" from class "%s" must be declared public', $method->getName(), $class->getName()));
                }
                if (!preg_match('#Action$#', $method->getName())) {
                    throw new \RuntimeException(sprintf('Method "%s" from class "%s" should have a name ending in "Action"', $method->getName(), $class->getName()));
                }
                /* @var Route $annotation */
                $annotationMethodDataCollection[] = new AnnotationMethodData($method, $this->configureAnnotation($annotation, $globals, $class, $method));
            }
        }

        return new AnnotationClassData($class, $annotationMethodDataCollection);
    }

    protected function configureAnnotation(Route $annotation, array $globals, \ReflectionClass $class, \ReflectionMethod $method): Route
    {
        $name = $annotation->getName();
        if (null === $name) {
            $name = $this->getDefaultRouteName($class, $method);
        }

        $defaults = array_replace($globals['defaults'], $annotation->getDefaults());
        foreach ($method->getParameters() as $param) {
            if (false !== strpos($globals['path'] . $annotation->getPath(), sprintf('{%s}', $param->getName())) && !isset($defaults[$param->getName()]) && $param->isDefaultValueAvailable()) {
                $defaults[$param->getName()] = $param->getDefaultValue();
            }
        }
        $requirements = array_replace($globals['requirements'], $annotation->getRequirements());
        $options = array_replace($globals['options'], $annotation->getOptions());
        $schemes = array_merge($globals['schemes'], $annotation->getSchemes());
        $methods = array_merge($globals['methods'], $annotation->getMethods());

        $host = $annotation->getHost();
        if (null === $host) {
            $host = $globals['host'];
        }

        $condition = $annotation->getCondition();
        if (null === $condition) {
            $condition = $globals['condition'];
        }

        $path = $globals['path'] . $annotation->getPath();

        $annotation->setPath($path);
        $annotation->setName($name);
        $annotation->setDefaults($defaults);
        $annotation->setRequirements($requirements);
        $annotation->setOptions($options);
        $annotation->setHost($host);
        $annotation->setSchemes($schemes);
        $annotation->setMethods($methods);
        $annotation->setCondition($condition);

        if (!empty($globals['service'])) {
            $annotation->setService($globals['service']);
        }

        return $annotation;
    }

    /**
     * @see \Symfony\Component\Routing\Loader\AnnotationClassLoader::getDefaultRouteName()
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     * @return string
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        return strtolower(str_replace('\\', '_', $class->name).'_'.$method->name);
    }

    /**
     * @see \Symfony\Component\Routing\Loader\AnnotationClassLoader::getGlobals()
     * @param \ReflectionClass $class
     * @return array
     */
    protected function getGlobals(\ReflectionClass $class)
    {
        $globals = [
            'path' => '',
            'requirements' => [],
            'options' => [],
            'defaults' => [],
            'schemes' => [],
            'methods' => [],
            'host' => '',
            'condition' => '',
            'service' => '',
        ];

        if ($annot = $this->reader->getClassAnnotation($class, $this->routeAnnotationClass)) {
            if (null !== $annot->getPath()) {
                $globals['path'] = $annot->getPath();
            }

            if (null !== $annot->getRequirements()) {
                $globals['requirements'] = $annot->getRequirements();
            }

            if (null !== $annot->getOptions()) {
                $globals['options'] = $annot->getOptions();
            }

            if (null !== $annot->getDefaults()) {
                $globals['defaults'] = $annot->getDefaults();
            }

            if (null !== $annot->getSchemes()) {
                $globals['schemes'] = $annot->getSchemes();
            }

            if (null !== $annot->getMethods()) {
                $globals['methods'] = $annot->getMethods();
            }

            if (null !== $annot->getHost()) {
                $globals['host'] = $annot->getHost();
            }

            if (null !== $annot->getCondition()) {
                $globals['condition'] = $annot->getCondition();
            }

            if (null !== $annot->getService()) {
                $globals['service'] = $annot->getService();
            }
        }

        return $globals;
    }
}
