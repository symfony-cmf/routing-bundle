<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteTypeType extends AbstractType
{
    protected array $routeTypes = [];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [];
        foreach ($this->routeTypes as $routeType) {
            $choices['route_type.'.$routeType] = $routeType;
        }

        $resolver->setDefaults([
            'choices' => $choices,
            'translation_domain' => 'CmfRoutingBundle',
        ]);
    }

    /**
     * Register a route type.
     */
    public function addRouteType(string $type): void
    {
        $this->routeTypes[$type] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'cmf_routing_route_type';
    }
}
