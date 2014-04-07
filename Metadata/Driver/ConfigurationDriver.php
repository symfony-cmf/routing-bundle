<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Metadata\Driver;

use Symfony\Cmf\Bundle\RoutingBundle\Metadata\ClassMetadata;

/**
 * Driver which operates from explicitly defined mappings
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ConfigurationDriver extends AbstractDriver
{
    protected $reader;
    protected $metadata = array();

    /**
     * Eagerly loads the class metadata
     */
    public function registerMapping($classFqn, $mapping)
    {
        $meta = new ClassMetadata($classFqn);

        if (isset($mapping['template'])) {
            $meta->template = $mapping['template'];
        }

        if (isset($mapping['controller'])) {
            $meta->controller = $mapping['controller'];
        }

        $this->validateMetadata($meta);

        $this->metadata[$classFqn] = $meta;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if (!isset($this->metadata[$class->name])) {
            return null;
        }

        return $this->metadata[$class->name];
    }
}

