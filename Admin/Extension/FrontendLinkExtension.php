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

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Admin extension to add a frontend link to the edit tab implementing the
 * RouteReferrersReadInterface.
 *
 * @author Frank Neff <fneff89@gmail.com>
 */
class FrontendLinkExtension extends AdminExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    ) {
        if (!$subject = $admin->getSubject()) {
            return;
        }

        if (!$subject instanceof RouteReferrersReadInterface) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s can only be used on subjects which implement Symfony\Cmf\Component\Routing\RouteReferrersReadInterface!',
                    __CLASS__
                )
            );
        }

        $uri = $this->router->generate($subject);

        $menu->addChild('Open in frontend', array('uri' => $uri, 'linkAttributes' => array('target' => '_blank')));
    }

}
