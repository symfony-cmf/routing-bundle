<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Form\Data\Converter;

use Symfony\Cmf\Bundle\CoreBundle\Form\Data\Converter;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\Converter\RouteGeneralDataConverter;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteGeneralData;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteGeneralDataConverterTest extends AbstractRouteConverterTest
{

    /**
     * @return object
     */
    protected function buildStartingDTO()
    {
        return new RouteGeneralData();
    }

    /**
     * @return Converter
     */
    protected function buildConverter()
    {
        return new RouteGeneralDataConverter();
    }

    /**
     * @return Route
     */
    protected function buildRoute()
    {
        $route = new Route();
        $route->setName('some-name');
        $route->setParentDocument(new Route());

        return $route;
    }

    /**
     * @return object
     */
    protected function buildDTO()
    {
        $dto = new RouteGeneralData();
        $dto->name = 'some-name';
        $dto->parentDocument = new Route();

        return $dto;
    }
}
