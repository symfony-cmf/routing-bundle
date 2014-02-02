<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Metadata\Driver;

use Symfony\Cmf\Bundle\RoutingBundle\Metadata\ClassMetadata;
use Doctrine\Common\Annotations\Reader;

class AnnotationDriver implements AbstractDriver
{
    protected $reader;

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $templateAnnotation = $this->reader->getClassAnnotation(
            $class,
            'Symfony\Cmf\Bundle\RoutingBundle\Metadata\Annotations\Template'
        );
        $controllerAnnotation = $this->reader->getClassAnnotation(
            $class,
            'Symfony\Cmf\Bundle\RoutingBundle\Metadata\Annotations\Controller'
        );


        $meta = new ClassMetadata($class->name);

        if (null !== $templateAnnotation) {
            $meta->template = $templateAnnotation->resource;
        }

        if (null !== $controllerAnnotation) {
            $meta->controller = $controllerAnnotation->controller;
        }

        $this->validateMetadata($meta);

        return $meta;
    }
}
