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
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\PrefixInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingExceptionInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @return void
     * @throws InvalidConfigurationException
     */
    public function configureTabMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    ) {
        if (!$subject = $admin->getSubject()) {
            return;
        }

        if (!$subject instanceof RouteReferrersReadInterface && !$subject instanceof Route) {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s can only be used on subjects which implement Symfony\Cmf\Component\Routing\RouteReferrersReadInterface or Symfony\Component\Routing\Route!',
                    __CLASS__
                )
            );
        }

        if ($subject instanceof PrefixInterface && !is_string($subject->getId())) {
            // we have an unpersisted dynamic route 
            return;
        }

        try {
            $uri = $this->router->generate($subject);
        } catch (RoutingExceptionInterface $e) {
            // we have no valid route
            return;
        }

        $menu->addChild(
            $this->translator->trans('admin.menu_frontend_link_caption', array(), 'CmfRoutingBundle'),
            array(
                'uri' => $uri,
                'attributes' => array(
                    'class' => 'sonata-admin-menu-item',
                    'role' => 'menuitem'
                ),
                'linkAttributes' => array(
                    'class' => 'sonata-admin-frontend-link',
                    'role' => 'button',
                    'target' => '_blank',
                    'title' => $this->translator->trans('admin.menu_frontend_link_title', array(), 'CmfRoutingBundle')
                )
            )
        );
    }

}
