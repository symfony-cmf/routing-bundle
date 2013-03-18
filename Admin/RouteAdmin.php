<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;

class RouteAdmin extends MinimalRouteAdmin
{
    /**
     * Those two properties are required for having two admin classes for the same document
     */
    protected $baseRouteName = 'symfony_cmf_routing_extra_route_admin';
    protected $baseRoutePattern = 'bundle/routingextra/route';

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('form.group_general')
                ->add('variablePattern', 'text', array('required' => false))
                ->add('routeContent', 'doctrine_phpcr_odm_tree', array('choice_list' => array(), 'required' => false, 'root_node' => $this->contentRoot))
                ->add('defaults', 'sonata_type_immutable_array', array('keys' => $this->configureFieldsForDefaults()))
            ->end();
    }
}
