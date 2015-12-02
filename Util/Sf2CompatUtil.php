<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Util;

final class Sf2CompatUtil
{
    private static $isAtLeastSymfony28;
    private static $formTypes = array(
        'text' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
        'doctrine_phpcr_odm_tree' => 'Sonata\DoctrinePHPCRAdminBundle\Form\Type\TreeModelType',
        'sonata_type_immutable_array' => 'Sonata\CoreBundle\Form\Type\ImmutableArrayType',
        'sonata_type_collection' => 'Sonata\CoreBundle\Form\Type\CollectionType',
        'cmf_routing_route_type' => 'Symfony\Cmf\Bundle\RoutingBundle\Form\Type\RouteTypeType',
    );

    public static function registerFormType($name, $fqcn)
    {
        self::$formTypes[$name] = $fqcn;
    }

    public static function getFormTypeName($name)
    {
        if (!self::isAtLeastSymfony28()) {
            return $name;
        }

        if (!isset(self::$formTypes[$name])) {
            throw new \InvalidArgumentException(sprintf('Unknown form type "%s", please register the FQCN of this form type to the Sf2CompatUtil first.', $name));
        }

        return self::$formTypes[$name];
    }

    public static function isAtLeastSymfony28()
    {
        if (null === self::$isAtLeastSymfony28) {
            self::$isAtLeastSymfony28 = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
        }

        return self::$isAtLeastSymfony28;
    }
}
