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

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteData
{
    /**
     * @var RouteGeneralData
     */
    public $routeGeneral;

    /**
     * @var RouteAdvancedData
     */
    public $routeAdvanced;
}
