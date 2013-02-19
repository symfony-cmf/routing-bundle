<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\Driver;

use Metadata\Driver\DriverInterface;
use Metadata\ClassMetadata;
use Doctrine\Common\Annotations\Reader;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\RouteClassMetadata;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\Annotations as CMFRouting;

/**
 * Annotation driver for routing extra bundle
 *
 * @author M. de Krijger <mdekrijger@netvlies.nl>
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AnnotationDriver implements DriverInterface
{

    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new RouteClassMetadata($class->getName());

        $classAnnotations = $this->reader->getClassAnnotations($class);
        foreach ($classAnnotations as $classAnnot) {
            if ($classAnnot instanceof CMFRouting\AutoRoute) {
                $classMetadata->basePath = $classAnnot->basePath;
                $classMetadata->routeName = $classAnnot->routeName;
                $classMetadata->updateBasePath = $classAnnot->updateBasePath;
                $classMetadata->updateRouteName = $classAnnot->updateRouteName;
            }
        }

        foreach ($this->getPropertyAnnotations() as $propAnnot) {
            if ($propAnnot instanceof CMFRouting\DefaultRoute) {
                // allow only once
                throw new \Exception('Implement me!');
            }

            if ($propAnnot instanceof CMFRouting\ParentRoutes) {
                // allow only once
                throw new \Exception('Implement me!');
            }

            if ($propAnnot instanceof CMFRouting\Permalink) {
                // allow only once
                throw new \Exception('Implement me!');
            }
        }

        foreach ($this->getMethodAnnotations() as $methodAnnot) {
            if ($methodAnnot instanceof CMFRouting\RouteName) {
                // allow only once
                throw new \Exception('Implement me!');
            }
        }

        return $classMetadata;
    }
}
