<?php

namespace Bezhanov\Silex\Routing;

class AnnotationClassData
{
    private $class;

    private $annotationMethodDataCollection;

    /**
     * @param \ReflectionClass $class
     * @param AnnotationMethodData[] $annotationMethodDataCollection
     */
    public function __construct(\ReflectionClass $class, array $annotationMethodDataCollection)
    {
        $this->class = $class;
        $this->annotationMethodDataCollection = $annotationMethodDataCollection;
    }

    public function getClass(): \ReflectionClass
    {
        return $this->class;
    }

    /**
     * @return AnnotationMethodData[]
     */
    public function getAnnotationMethodDataCollection(): array
    {
        return $this->annotationMethodDataCollection;
    }
}
