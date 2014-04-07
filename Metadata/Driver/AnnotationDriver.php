<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Metadata\Driver;

use Symfony\Cmf\Bundle\RoutingBundle\Metadata\ClassMetadata;
use Doctrine\Common\Annotations\Reader;

class AnnotationDriver extends AbstractDriver
{
    protected $reader;
    protected $genericController;

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function setGenericController($genericController)
    {
        $this->genericController = $genericController;
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

        if (null === $meta->controller) {
            $meta->controller = $this->genericController;
        }

        $this->validateMetadata($meta);

        return $meta;
    }
}
