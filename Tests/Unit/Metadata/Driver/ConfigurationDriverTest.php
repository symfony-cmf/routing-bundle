<?php

namespace Unit\Metadata\Driver;

use Symfony\Cmf\Bundle\RoutingBundle\Metadata\Driver\ConfigurationDriver;

class ConfigurationDriverTest extends \PHPUnit_Framework_TestCase
{
    protected $driver;

    public function setUp()
    {
        $this->driver = new ConfigurationDriver;
    }

    public function testDriver()
    {
        $this->driver->registerMapping('stdClass', array(
            'template' => 'foobar.html.twig',
            'controller' => 'FoobarController',
        ));

        $refl = new \ReflectionClass('stdClass');
        $meta = $this->driver->loadMetadataForClass($refl);

        $this->assertEquals('foobar.html.twig', $meta->template);
        $this->assertEquals('FoobarController', $meta->controller);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage but no controller specified
     */
    public function testDriverMissingController()
    {
        $this->driver->registerMapping('stdClass', array(
            'template' => 'foobar.html.twig',
        ));

        $refl = new \ReflectionClass('stdClass');
        $meta = $this->driver->loadMetadataForClass($refl);
    }
}
