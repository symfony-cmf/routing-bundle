<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Metadata\ClassMetadata;

abstract class AbstractDriver implements DriverInterface
{
    public function validateMetadata(ClassMetadata $meta)
    {
        if (
            null !== $meta->template &&
            null == $meta->controller
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Template specified for class "%s" but no controller specified.' .PHP_EOL.
                'You must either explicitly map a controller to the class or define'.PHP_EOL.
                'a "generic_controller" in the main configuration.'
            , $meta->name));
        }
    }
}
