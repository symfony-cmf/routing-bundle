<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Routing;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\ContentAwareGenerator;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class ContentAwareGeneratorTest extends CmfUnitTestCase
{
    /** @var TestableContentAwareGenerator */
    protected $generator;

    public function setUp()
    {
        $provider = $this->getMock('Symfony\Cmf\Component\Routing\RouteProviderInterface');
        $this->generator = new TestableContentAwareGenerator($provider);

        $this->generator->setDefaultLocale('en');
    }

    public function testGetLocaleAttribute()
    {
        $attributes = array('_locale' => 'fr');
        $this->assertEquals('fr', $this->generator->getLocale($attributes));
    }

    public function testGetLocaleDefault()
    {
        $attributes = array();
        $this->assertEquals('en', $this->generator->getLocale($attributes));
    }

    public function testGetLocaleRequest()
    {
        $request = Request::create('/foo');
        $request->setDefaultLocale('de');
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container->expects($this->once())
            ->method('isScopeActive')
            ->with('request')
            ->will($this->returnValue(true))
        ;
        $container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request))
        ;
        $this->generator->setContainer($container);

        $attributes = array();
        $this->assertEquals('de', $this->generator->getLocale($attributes));
    }

}

/** overwrite getLocale to make it public */
class TestableContentAwareGenerator extends ContentAwareGenerator
{
    public function getLocale($parameters)
    {
        return parent::getLocale($parameters);
    }
}
