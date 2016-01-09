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
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Model\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Util\Sf2CompatUtil;
use PHPCR\Util\PathHelper;

class RouteAdmin extends Admin
{
    protected $translationDomain = 'CmfRoutingBundle';

    /**
     * Root path for the route parent selection.
     *
     * @var string
     */
    protected $routeRoot;

    /**
     * Root path for the route content selection.
     *
     * @var string
     */
    protected $contentRoot;

    /**
     * Full class name for content that can be referenced by a route.
     *
     * @var string
     */
    protected $contentClass;

    /**
     * @var ControllerResolverInterface
     *
     * @deprecated Since 1.4, use the RouteDefaults validator on your document.
     */
    protected $controllerResolver;

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('path', 'text')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form.group_general', array(
                'translation_domain' => 'CmfRoutingBundle',
            ))
                ->add(
                    'parent',
                    Sf2CompatUtil::getFormTypeName('doctrine_phpcr_odm_tree'),
                    array('choice_list' => array(), 'select_root_node' => true, 'root_node' => $this->routeRoot)
                )
                ->add('name', Sf2CompatUtil::getFormTypeName('text'))
        ->end();

        if (null === $this->getParentFieldDescription()) {
            $formMapper
                ->with('form.group_general', array(
                    'translation_domain' => 'CmfRoutingBundle',
                ))
                    ->add('content', Sf2CompatUtil::getFormTypeName('doctrine_phpcr_odm_tree'), array('choice_list' => array(), 'required' => false, 'root_node' => $this->contentRoot))
                ->end()
                ->with('form.group_advanced', array(
                    'translation_domain' => 'CmfRoutingBundle',
                ))
                    ->add('variablePattern', Sf2CompatUtil::getFormTypeName('text'), array('required' => false), array('help' => 'form.help_variable_pattern'))
                    ->add(
                        'defaults',
                        Sf2CompatUtil::getFormTypeName('sonata_type_immutable_array'),
                        array('keys' => $this->configureFieldsForDefaults($this->getSubject()->getDefaults()))
                    )
                    ->add(
                        'options',
                        Sf2CompatUtil::getFormTypeName('sonata_type_immutable_array'),
                        array('keys' => $this->configureFieldsForOptions($this->getSubject()->getOptions())),
                        array('help' => 'form.help_options')
                    )
                ->end()
            ->end();
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', 'doctrine_phpcr_nodename');
    }

    public function setRouteRoot($routeRoot)
    {
        // make limitation on base path work
        parent::setRootPath($routeRoot);
        // TODO: fix widget to show root node when root is selectable
        // https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/issues/148
        $this->routeRoot = PathHelper::getParentPath($routeRoot);
    }

    public function setContentRoot($contentRoot)
    {
        $this->contentRoot = $contentRoot;
    }

    /**
     * @deprecated Since 1.4, use the RouteDefaults validator on your document.
     */
    public function setControllerResolver($controllerResolver)
    {
        $this->controllerResolver = $controllerResolver;
    }

    public function getExportFormats()
    {
        return array();
    }

    /**
     * Provide default route defaults and extract defaults from $dynamicDefaults.
     *
     * @param array $dynamicDefaults
     *
     * @return array Value for sonata_type_immutable_array
     */
    protected function configureFieldsForDefaults($dynamicDefaults)
    {
        $defaults = array(
            '_controller' => array('_controller', Sf2CompatUtil::getFormTypeName('text'), array('required' => false)),
            '_template' => array('_template', Sf2CompatUtil::getFormTypeName('text'), array('required' => false)),
            'type' => array('type', Sf2CompatUtil::getFormTypeName('cmf_routing_route_type'), array(
                'empty_value' => '',
                'required' => false,
            )),
        );

        foreach ($dynamicDefaults as $name => $value) {
            if (!isset($defaults[$name])) {
                $defaults[$name] = array($name, Sf2CompatUtil::getFormTypeName('text'), array('required' => false));
            }
        }

        //parse variable pattern and add defaults for tokens - taken from routecompiler
        /** @var $route Route */
        $route = $this->subject;
        if ($route && $route->getVariablePattern()) {
            preg_match_all('#\{\w+\}#', $route->getVariablePattern(), $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            foreach ($matches as $match) {
                $name = substr($match[0][0], 1, -1);
                if (!isset($defaults[$name])) {
                    $defaults[$name] = array($name, Sf2CompatUtil::getFormTypeName('text'), array('required' => true));
                }
            }
        }

        if ($route && $route->getOption('add_format_pattern')) {
            $defaults['_format'] = array('_format', Sf2CompatUtil::getFormTypeName('text'), array('required' => true));
        }
        if ($route && $route->getOption('add_locale_pattern')) {
            $defaults['_locale'] = array('_locale', Sf2CompatUtil::getFormTypeName('text'), array('required' => false));
        }

        return $defaults;
    }

    /**
     * Provide default options and extract options from $dynamicOptions.
     *
     * @param array $dynamicOptions
     *
     * @return array Value for sonata_type_immutable_array
     */
    protected function configureFieldsForOptions(array $dynamicOptions)
    {
        $isSf28 = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
        $checkboxType = $isSf28 ? 'Symfony\Component\Form\Extension\Core\Type\CheckboxType' : 'checkbox';

        $options = array(
            'add_locale_pattern' => array('add_locale_pattern', $checkboxType, array('required' => false, 'label' => 'form.label_add_locale_pattern', 'translation_domain' => 'CmfRoutingBundle')),
            'add_format_pattern' => array('add_format_pattern', $checkboxType, array('required' => false, 'label' => 'form.label_add_format_pattern', 'translation_domain' => 'CmfRoutingBundle')),
            'add_trailing_slash' => array('add_trailing_slash', $checkboxType, array('required' => false, 'label' => 'form.label_add_trailing_slash', 'translation_domain' => 'CmfRoutingBundle')),
        );

        foreach ($dynamicOptions as $name => $value) {
            if (!isset($options[$name])) {
                $options[$name] = array($name, 'text', array('required' => false));
            }
        }

        return $options;
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

    public function toString($object)
    {
        return $object instanceof Route && $object->getId()
            ? $object->getId()
            : $this->trans('link_add', array(), 'SonataAdminBundle')
            ;
    }
}
