<?php

namespace Symfony\Cmf\Bundle\BlogBundle\Mapping;

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
    public $basePath;

    public $routeName;

    public $updateBasePath;

    public $updateRouteName = true;

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
