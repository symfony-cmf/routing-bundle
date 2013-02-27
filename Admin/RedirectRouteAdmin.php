<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Admin;

use Doctrine\Common\Util\Debug;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Symfony\Component\HttpFoundation\Request;

class RedirectRouteAdmin extends Admin
{
    protected $translationDomain = 'SymfonyCmfRoutingExtraBundle';

     /**
     * Root path for the route parent selection
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
                // TODO: show resulting url; strip /cms/routes and prepend eventual route prefix
                // ->add('path', 'text', array('label' => 'URL', 'attr' => array('readonly' => 'readonly')))
                ->add('parent', 'doctrine_phpcr_odm_tree', array('choice_list' => array(), 'select_root_node' => true, 'root_node' => $this->routeRoot))
                ->add('name', 'text')
                ->add('routeName', 'text', array('required' => false))
                ->add('uri', 'text', array('required' => false))
                ->add('routeTarget', 'doctrine_phpcr_odm_tree', array('choice_list' => array(), 'required' => false, 'root_node' => $this->routeRoot))
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name',  'doctrine_phpcr_string')
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

}
