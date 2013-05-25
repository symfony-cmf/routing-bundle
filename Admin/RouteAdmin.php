<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Symfony\Cmf\Bundle\RoutingBundle\Document\Route;
use Symfony\Component\HttpFoundation\Request;

class RouteAdmin extends Admin
{
    protected $translationDomain = 'CmfRoutingBundle';

    /**
     * Root path for the route parent selection
     * @var string
     */
    protected $routeRoot;

    /**
     * Root path for the route content selection
     * @var string
     */
    protected $contentRoot;

    /**
     * Full class name for content that can be referenced by a route
     * @var string
     */
    protected $contentClass;

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', 'text')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form.group_general')
            ->add(
                'parent',
                'doctrine_phpcr_odm_tree',
                array('choice_list' => array(), 'select_root_node' => true, 'root_node' => $this->routeRoot)
            )
            ->add('name', 'text')
            ->end();

        if (null === $this->getParentFieldDescription()) {
            $formMapper
                ->with('form.group_general')
                ->add('variablePattern', 'text', array('required' => false))
                ->add('routeContent', 'doctrine_phpcr_odm_tree', array('choice_list' => array(), 'required' => false, 'root_node' => $this->contentRoot))
                ->add('defaults', 'sonata_type_immutable_array', array('keys' => $this->configureFieldsForDefaults()))
                ->end();
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', 'doctrine_phpcr_string');
    }

    public function setRouteRoot($routeRoot)
    {
        $this->routeRoot = $routeRoot;
    }

    public function setContentRoot($contentRoot)
    {
        $this->contentRoot = $contentRoot;
    }

    public function getExportFormats()
    {
        return array();
    }

    protected function configureFieldsForDefaults()
    {
        return array(
            array('_controller', 'text', array('required' => false)),
            array('_template', 'text', array('required' => false)),
            array('type', 'cmf_routing_route_type', array(
                'empty_value' => '',
                'required' => false,
            )),
        );
    }

    public function prePersist($object)
    {
        $defaults = array_filter($object->getDefaults());
        $object->setDefaults($defaults);
    }

    public function preUpdate($object)
    {
        $defaults = array_filter($object->getDefaults());
        $object->setDefaults($defaults);
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        $defaults = $object->getDefaults();

        if (isset($defaults['_controller'])) {
            $this->validateDefaultsController($errorElement, $object);
        }

        if (isset($defaults['_template'])) {
            $this->validateDefaultsTemplate($errorElement, $object);
        }
    }

    protected function validateDefaultsController(ErrorElement $errorElement, $object)
    {
        $defaults = $object->getDefaults();

        $controllerResolver = $this->getConfigurationPool()->getContainer()->get('controller_resolver');
        $controller = $defaults['_controller'];

        $request = new Request(array(), array(), array('_controller' => $controller));

        try {
            $controllerResolver->getController($request);
        } catch (\LogicException $e) {
            $errorElement
                ->with('defaults')
                ->addViolation($e->getMessage())
                ->end();
        }
    }

    protected function validateDefaultsTemplate(ErrorElement $errorElement, $object)
    {
        $defaults = $object->getDefaults();

        $templating = $this->getConfigurationPool()->getContainer()->get('templating');
        $template = $defaults['_template'];

        if (false === $templating->exists($template)) {
            $errorElement
                ->with('defaults')
                ->addViolation(sprintf('Template "%s" does not exist.', $template))
                ->end();
        }
    }
}
