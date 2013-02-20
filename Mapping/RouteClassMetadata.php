<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping;

use Metadata\ClassMetadata;
use Metadata\MergeableInterface;

/**
 * Metadata for Route 
 *
 * @author M. de Krijger <mdekrijger@netvlies.nl>
 * @author Daniel Leech <daniel@dantleech.com
 */
class RouteClassMetadata extends ClassMetadata implements \Serializable, MergeableInterface
{
    /**
     * Base path to use for routes (optional)
     */
    public $basePath;

    /**
     * Method to use for getting the route name
     */
    public $routeNameMethod;

    /**
     * Whether, in case of a path conflict, we should keep autogenerating paths until
     * we do not have a conflict, or if we should just throw an Exception.
     */
    public $resolvePathConflicts = false;

    public function serialize()
    {
        return serialize(array(
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->basePath,
            $this->routeName,
            $this->updateBasePath,
            $this->updateRouteName
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->name,
            $this->methodMetadata,
            $this->propertyMetadata,
            $this->fileResources,
            $this->createdAt,
            $this->basePath,
            $this->routeName,
            $this->updateBasePath,
            $this->updateRouteName
            ) = unserialize($str);

        $this->reflection = new \ReflectionClass($this->name);
    }


    public function merge(MergeableInterface $object)
    {

        if(!is_null($object->basePath)){
            $this->basePath = $object->basePath;
        }

        if(!is_null($object->routeName)){
            $this->routeName = $object->routeName;
        }

        if(!is_null($object->updateBasePath)){
            $this->updateBasePath = $object->updateBasePath;
        }

        if(!is_null($object->updateRouteName)){
            $this->updateRouteName = $object->updateRouteName;
        }
    }
}
