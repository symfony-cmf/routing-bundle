<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Form\Data\Converter;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\Converter\RouteAdvancedDataConverter;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\Converter\RouteDataConverter;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\Converter\RouteGeneralDataConverter;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteAdvancedData;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteData;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteGeneralData;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteDataConverterTest extends AbstractRouteConverterTest
{
    /**
     * {@inheritdoc}
     */
    protected function buildStartingDTO()
    {
        return new RouteData();
    }

    /**
     * {@inheritdoc}
     */
    protected function buildConverter()
    {
        $converter = new RouteDataConverter();
        $converter->setRouteAdvancedDataConverter(new RouteAdvancedDataConverter());
        $converter->setRouteGeneralDataConverter(new RouteGeneralDataConverter());

        return $converter;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRoute()
    {
        $route = new Route();
        $route->setName('some-name');
        $route->setParentDocument(new Route());
        $route->setVariablePattern('pattern');
        $route->setDefault('_controller', 'test:indexAction');
        $route->setOptions(['key' => 'value', 'compiler_class' => 'Symfony\\Component\\Routing\\RouteCompiler']);

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildDTO()
    {
        $dto = new RouteData();

        $dtoAdvanced = new RouteAdvancedData();
        $dtoAdvanced->variablePattern = 'pattern';
        $dtoAdvanced->defaults['_controller'] = 'test:indexAction';
        $dtoAdvanced->options['key'] = 'value';
        $dtoAdvanced->options['compiler_class'] = 'Symfony\\Component\\Routing\\RouteCompiler';
        $dto->routeAdvanced = $dtoAdvanced;

        $dtoGeneral = new RouteGeneralData();
        $dtoGeneral->name = 'some-name';
        $dtoGeneral->parentDocument = new Route();
        $dto->routeGeneral = $dtoGeneral;

        return $dto;
    }
}
