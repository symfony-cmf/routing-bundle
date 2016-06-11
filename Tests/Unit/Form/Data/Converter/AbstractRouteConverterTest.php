<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Form\Data\Converter;

use Symfony\Cmf\Bundle\CoreBundle\Form\Data\Converter;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
abstract class AbstractRouteConverterTest  extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $converter;

    public function setUp()
    {
        $this->converter = $this->buildConverter();
    }

    public function testToDocument()
    {
        $expectedRoute = $this->buildRoute();
        $dto = $this->buildDTO();

        $actualRoute = $this->converter->toDocument($dto, new Route());

        $this->assertEquals($actualRoute, $expectedRoute);
    }

    public function testToDTO()
    {
        $route = $this->buildRoute();
        $expectedDTO = $this->buildDTO();

        $actualDTO = $this->converter->toDataTransferObject($route, $this->buildStartingDTO());

        $this->assertEquals($actualDTO, $expectedDTO);
    }

    /**
     * @return object
     */
    abstract protected function buildStartingDTO();

    /**
     * @return Converter
     */
    abstract protected function buildConverter();

    /**
     * @return Route
     */
    abstract protected function buildRoute();

    /**
     * @return object
     */
    abstract protected function buildDTO();

}
