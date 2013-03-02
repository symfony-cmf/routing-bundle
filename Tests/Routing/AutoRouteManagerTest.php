<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Routing;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\AutoRouteManager;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\RouteClassMetadata;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

class AutoRouteManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dm = $this->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');
        $this->slugifier = $this->getMock('Symfony\Cmf\Bundle\RoutingExtraBundle\Util\SlugifierInterface');

        $this->routeMetadata = $this->getMockBuilder('Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\RouteClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->document = new TestDocument;
        $this->parentRoute = new \stdClass;

        $this->odmMetadata = new ClassMetadata('Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Routing\TestDocument');
        $this->phpcrSession = $this->getMock('PHPCR\SessionInterface');

        $this->autoRouteManager = new AutoRouteManager(
            $this->dm,
            $this->metadataFactory,
            $this->slugifier,
            '/default/path'
        );
    }

    public function testUpdateRouteForDocument_notPersisted()
    {
        // getMetadata ...
        $this->metadataFactory->expects($this->once())
            ->method('getMetadataForClass')
            ->with('Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Routing\TestDocument')
            ->will($this->returnValue($this->routeMetadata));

        $this->dm->expects($this->once())
            ->method('getClassMetadata')
            ->with('Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Routing\TestDocument')
            ->will($this->returnValue($this->odmMetadata));

        // isExistingDocument ...
        $this->dm->expects($this->once())
            ->method('getPhpcrSession')
            ->will($this->returnValue($this->phpcrSession));

        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->will($this->returnValue(false));

        // getRouteName
        $this->routeMetadata->expects($this->once())
            ->method('getRouteNameMethod')
            ->will($this->returnValue('getRouteName'));

        $this->slugifier->expects($this->once())
            ->method('slugify')
            ->with('test route')
            ->will($this->returnValue('test-route'));

        // getParentRoute
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/default/path')
            ->will($this->returnValue($this->parentRoute));

        $autoRoute = $this->autoRouteManager->updateAutoRouteForDocument($this->document);

        $this->assertEquals('test-route', $autoRoute->getName());
        $this->assertSame($this->parentRoute, $autoRoute->getParent());
    }
}

class TestDocument
{
    public function getRouteName()
    {
        return 'test route';
    }
}
