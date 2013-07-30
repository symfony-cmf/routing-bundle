<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Admin extension to add routes tab to content implementing the
 * RouteReferrersWriteInterface.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class RouteReferrersExtension extends AdminExtension
{
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form.group_routes', array(
                'translation_domain' => 'CmfRoutingBundle',
            ))
            ->add(
                'routes',
                'sonata_type_collection',
                array(
                    'by_reference' => false,
                ),
                array(
                    'edit' => 'inline',
                    'inline' => 'table',
                ))
            ->end()
        ;
    }
}
