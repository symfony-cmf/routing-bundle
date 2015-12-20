<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RouteTypeType extends AbstractType
{
    protected $routeTypes = array();
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = array();
        foreach ($this->routeTypes as $routeType) {
            $choices[$routeType] = 'route_type.'.$routeType;
        }

        $resolver->setDefaults(array(
            'choices' => $choices,
            'translation_domain' => 'CmfRoutingBundle',
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove when Symfony <2.7 support is dropped.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * Register a route type.
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
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType' : 'choice';
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove when Symfony <2.8 support is dropped.
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cmf_routing_route_type';
    }
}
