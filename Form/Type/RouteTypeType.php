<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Locale\Locale;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RouteTypeType extends AbstractType
{
    protected $routeTypes;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->routeTypes,
        ));
    }

    /**
     * Register a route type
     *
     * @param string $type
     */
    public function addRouteType($type)
    {
        $this->routeTypes[$type] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'symfony_cmf_routing_extra_route_type';
    }
}

