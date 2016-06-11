<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Form\Data;

use Symfony\Cmf\Bundle\RoutingBundle\Model\Route;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteGeneralData
{
    /**
     * Parent route document.
     *
     * @var Route
     */
    public $parentDocument;

    /**
     * Name of a route.
     *
     * @var string
     */
    public $name;
}
