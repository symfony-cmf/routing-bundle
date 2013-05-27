<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RouteTypeType extends AbstractType
{
    protected $routeTypes = array();
    protected $translator;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
        return 'cmf_routing_route_type';
    }
}

