<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Routing;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\AutoRouteManager;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\RouteClassMetadata;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;

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

        $this->testRoute1 = $this->getMock('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute');
        $this->testRoute2 = $this->getMock('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute');
    }

    protected function bootstrapRouteMetadata()
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
    }

    protected function bootstrapExistingDocument($isExisting)
    {
        // isExistingDocument ...
        $this->dm->expects($this->once())
            ->method('getPhpcrSession')
            ->will($this->returnValue($this->phpcrSession));

        $this->phpcrSession->expects($this->once())
            ->method('nodeExists')
            ->will($this->returnValue($isExisting));
    }

    protected function bootstrapRouteName()
    {
        // getRouteName
        $this->routeMetadata->expects($this->once())
            ->method('getRouteNameMethod')
            ->will($this->returnValue('getRouteName'));

        $this->slugifier->expects($this->once())
            ->method('slugify')
            ->with('test route')
            ->will($this->returnValue('test-route'));
    }

    protected function bootstrapParentRoute()
    {
        // getParentRoute
        $this->dm->expects($this->once())
            ->method('find')
            ->with(null, '/default/path')
            ->will($this->returnValue($this->parentRoute));
    }

    public function testUpdateRouteForDocument_notPersisted()
    {
        $this->bootstrapRouteMetadata();
        $this->bootstrapExistingDocument(false);
        $this->bootstrapRouteName();
        $this->bootstrapParentRoute();

        $autoRoute = $this->autoRouteManager->updateAutoRouteForDocument($this->document);

        $this->assertEquals('test-route', $autoRoute->getName());
        $this->assertSame($this->parentRoute, $autoRoute->getParent());
    }

    public function testUpdateRouteForDocument_withAutoRouteAndOtherReferrer()
    {
        $this->bootstrapRouteMetadata();
        $this->bootstrapExistingDocument(true);
        $this->bootstrapRouteName();
        $this->bootstrapParentRoute();

        $this->dm->expects($this->once())
            ->method('getReferrers')
            ->will($this->returnValue(new ArrayCollection(array(
                new \stdClass,
                $this->testRoute1,
            ))));

        $autoRoute = $this->autoRouteManager->updateAutoRouteForDocument($this->document);

        $this->assertSame($this->testRoute1, $autoRoute);
    }

    /**
     * @expectedException \Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\Exception\MoreThanOneAutoRoute
     */
    public function testUpdateRouteForDocument_withMoreThanOneAutoRoute()
    {
        $this->bootstrapRouteMetadata();
        $this->bootstrapExistingDocument(true);

        $this->dm->expects($this->once())
            ->method('getReferrers')
            ->will($this->returnValue(new ArrayCollection(array(
                $this->testRoute1,
                $this->testRoute2,
            ))));

        $this->autoRouteManager->updateAutoRouteForDocument($this->document);
    }
}

class TestDocument
{
    public function getRouteName()
    {
        return 'test route';
    }
}
