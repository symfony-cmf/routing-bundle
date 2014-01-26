<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Enhancer;

use Symfony\Component\HttpFoundation\Request;
use Metadata\MetadataFactoryInterface;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;

/**
 * This enhancer performs the same role as its counterpart
 * in the Routing component, but it uses the Metadata factory.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class FieldByClassEnhancer implements RouteEnhancerInterface
{
    /**
     * @var string field for the className class
     */
    protected $classField;

    /**
     * @var string field to write hashmap lookup result into
     */
    protected $targetField;

    /**
     * @var string field to write hashmap lookup result into
     */
    protected $metaField;

    /**
     * @var Metadata\MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @param string $className the field name of the class
     * @param string $targetField the field name to set from the map
     * @param array  $map    the map of class names to field values
     */
    public function __construct(
        $classField,
        $targetField, 
        $metaField,
        MetadataFactoryInterface $metadataFactory
    )
    {
        $this->classField = $classField;
        $this->targetField = $targetField;
        $this->metaField = $metaField;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * If the class name is mapped
     *
     * {@inheritDoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        if (isset($defaults[$this->targetField])) {
            return $defaults;
        }

        // return if the field containg the class name has not been set
        if (! isset($defaults[$this->classField])) {
            return $defaults;
        }

        $className = get_class($defaults[$this->classField]);

        $meta = $this->metadataFactory->getMetadataForClass($className);

        if (null !== $meta) {
            $meta = $meta->getOutsideClassMetadata();
            $defaults[$this->targetField] = $meta->{$this->metaField};
        }

        return $defaults;
    }
}
