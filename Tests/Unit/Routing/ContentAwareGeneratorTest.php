<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Routing;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Bundle\RoutingBundle\Routing\ContentAwareGenerator;

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
        $this->generator->context->setParameter('_locale', 'de');

        $attributes = array();
        $this->assertEquals('de', $this->generator->getLocale($attributes));
    }

}

/** overwrite getLocale to make it public */
class TestableContentAwareGenerator extends ContentAwareGenerator
{
    public $context;

    public function getLocale($parameters)
    {
        return parent::getLocale($parameters);
    }
}
