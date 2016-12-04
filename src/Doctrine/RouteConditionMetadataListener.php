<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata as OrmClassMetadata;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata as PhpcrClassMetadata;

/**
 * Metadata listener to remove mapping for condition field if the field does not exist.
 *
 * The condition option was only added in Symfony 2.4 and is missing from 2.3.
 * When we drop Symfony 2.3 support, this listener can be dropped.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class RouteConditionMetadataListener implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * Handle the load class metadata event: remove translated attribute from
     * fields and remove the locale mapping if present.
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        if (!property_exists('Symfony\Component\Routing\Route', 'condition')) {
            return; // nothing to do
        }

        $meta = $eventArgs->getClassMetadata();
        $refl = $meta->getReflectionClass();
        if (null !== $refl && 'Symfony\Component\Routing\Route' !== $refl->getName()) {
            return;
        }

        if ($meta instanceof OrmClassMetadata) {
            /* @var $meta OrmClassMetadata */
            $meta->mapField(array(
                'fieldName' => 'condition',
                'columnName' => 'condition_expr',
                'type' => 'string',
                'nullable' => true,
            ));
        } elseif ($meta instanceof PhpcrClassMetadata) {
            /* @var $meta PhpcrClassMetadata */
            $meta->mapField(array(
                'fieldName' => 'condition',
                'type' => 'string',
                'nullable' => true,
            ));
        } else {
            throw new \LogicException(sprintf('Class metadata was neither PHPCR nor ORM but %s', get_class($meta)));
        }
    }
}
