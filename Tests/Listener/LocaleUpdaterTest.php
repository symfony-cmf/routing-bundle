<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Listener;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Listener\LocaleUpdater;
use Doctrine\ODM\PHPCR\Event\MoveEventArgs;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class LocaleUpdaterTest extends CmfUnitTestCase
{
    /** @var LocaleUpdater */
    protected $listener;
    protected $routeMock;
    protected $dmMock;

    public function setUp()
    {
        $this->listener = new LocaleUpdater('/prefix/path', array('en', 'de'));
        $this->routeMock = $this->buildMock('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route');
        $this->dmMock = $this->buildMock('Doctrine\ODM\PHPCR\DocumentManager');
    }
    public function testMoved()
    {
        $moveArgs = new MoveEventArgs(
            $this->routeMock,
            $this->dmMock,
            '/prefix/path/de/my/route',
            '/prefix/path/en/my/route'
        );

        $this->routeMock->expects($this->once())
            ->method('setDefault')
            ->with('_locale', 'en')
        ;
        $this->routeMock->expects($this->once())
            ->method('setRequirement')
            ->with('_locale', 'en')
        ;

        $this->listener->postMove($moveArgs);
    }

    public function testLoad()
    {
        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->routeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('/prefix/path/de/my/route'))
        ;

        $this->routeMock->expects($this->once())
            ->method('setDefault')
            ->with('_locale', 'de')
        ;
        $this->routeMock->expects($this->once())
            ->method('setRequirement')
            ->with('_locale', 'de')
        ;

        $this->listener->postLoad($args);
    }

    public function testNolocaleUrl()
    {
        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->routeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('/prefix/path/my/route'))
        ;

        $this->routeMock->expects($this->never())
            ->method('setDefault')
        ;
        $this->routeMock->expects($this->never())
            ->method('setRequirement')
        ;

        $this->listener->postLoad($args);
    }

    public function testHaslocale()
    {
        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->routeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('/prefix/path/de/my/route'))
        ;

        $this->routeMock->expects($this->once())
            ->method('getDefault')
            ->with('_locale')
            ->will($this->returnValue('some'))
        ;

        $this->routeMock->expects($this->never())
            ->method('setDefault')
        ;
        $this->routeMock->expects($this->never())
            ->method('setRequirement')
        ;

        $this->listener->postLoad($args);
    }
}
