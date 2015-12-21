<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Symfony\Cmf\Bundle\RoutingBundle\Model\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Util\Sf2CompatUtil;

class RedirectRouteAdmin extends Admin
{
    protected $translationDomain = 'CmfRoutingBundle';

    /**
     * Root path for the route parent selection.
     *
     * @var string
     */
    protected $routeRoot;

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('path', 'text')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form.group_general')
                ->add('parent', Sf2CompatUtil::getFormTypeName('doctrine_phpcr_odm_tree'), array('choice_list' => array(), 'select_root_node' => true, 'root_node' => $this->routeRoot))
                ->add('name', Sf2CompatUtil::getFormTypeName('text'))
                ->add('routeName', Sf2CompatUtil::getFormTypeName('text'), array('required' => false))
                ->add('uri', Sf2CompatUtil::getFormTypeName('text'), array('required' => false))
                ->add('routeTarget', Sf2CompatUtil::getFormTypeName('doctrine_phpcr_odm_tree'), array('choice_list' => array(), 'required' => false, 'root_node' => $this->routeRoot))
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', 'doctrine_phpcr_nodename')
        ;
    }

    public function setRouteRoot($routeRoot)
    {
        $this->routeRoot = $routeRoot;
    }

    public function getExportFormats()
    {
        return array();
    }

    public function toString($object)
    {
        return $object instanceof Route && $object->getId()
            ? $object->getId()
            : $this->trans('link_add', array(), 'SonataAdminBundle')
        ;
    }
}
