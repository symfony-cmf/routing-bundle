<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Form\Data\Converter;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\Converter\RouteAdvancedDataConverter;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteAdvancedData;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteAdvancedDataConverterTest extends AbstractRouteConverterTest
{
    /**
     * {@inheritdoc}
     */
    protected function buildConverter()
    {
        return new RouteAdvancedDataConverter();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildDTO()
    {
        $dto = new RouteAdvancedData();
        $dto->variablePattern = 'pattern';
        $dto->defaults['_controller'] = 'test:indexAction';
        $dto->options['key'] = 'value';
        $dto->options['compiler_class'] = 'Symfony\\Component\\Routing\\RouteCompiler';

        return $dto;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRoute()
    {
        $route = new Route();
        $route->setVariablePattern('pattern');
        $route->setDefault('_controller', 'test:indexAction');
        $route->setOptions(['key' => 'value', 'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler']);

        return $route;
    }

    protected function buildStartingDTO()
    {
        return new RouteAdvancedData();
    }
}
