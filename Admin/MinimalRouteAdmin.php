<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\HttpFoundation\Request;

/**
 * This is a minimal admin class for routes
 * It has less fields than the general RouteAdmin to provide an simpler user interface for creating routes
 * Usually it is used for including a subform for routes directly in the admin class of a content document such as
 * Symfony\Cmf\Bundle\ContentBundle\Document\StaticContent
 */
class MinimalRouteAdmin extends RouteAdmin
{

    /**
     * Those two properties are required for having two admin classes for the same document
     */
    protected $baseRouteName = 'symfony_cmf_routing_extra_minimal_route_admin';
    protected $baseRoutePattern = 'symfony_cmf/routing_extra/minimalRoute';

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('form.group_general')
                ->remove('routeContent')
                ->remove('variablePattern')
                ->remove('defaults')
            ->end();
    }

}
