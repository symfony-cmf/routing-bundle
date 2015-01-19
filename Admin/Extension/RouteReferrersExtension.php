<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Admin extension to add routes tab to content implementing the
 * RouteReferrersInterface.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class RouteReferrersExtension extends AdminExtension
{
    /**
     * @var string
     */
    private $formGroup;

    /**
     * @var string
     */
    private $formTab;

    public function __construct($formGroup = 'form.group_routes', $formTab = 'form.tab_routing')
    {
        $this->formGroup = $formGroup;
        $this->formTab = $formTab;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->formTab, 'form.tab_routing' === $this->formTab
                ? array('translation_domain' => 'CmfRoutingBundle', 'tab' => true)
                : array('tab' => true)
            )
                ->with($this->formGroup, 'form.group_routes' === $this->formGroup
                    ? array('translation_domain' => 'CmfRoutingBundle')
                    : array()
                )
                    ->add(
                        'routes',
                        'sonata_type_collection',
                        array(),
                        array(
                            'edit' => 'inline',
                            'inline' => 'table',
                        )
                    )
                ->end()
            ->end()
        ;
    }
}
