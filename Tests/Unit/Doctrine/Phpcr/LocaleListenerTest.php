<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Listener;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\LocaleListener;
use Doctrine\ODM\PHPCR\Event\MoveEventArgs;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class LocaleListenerTest extends CmfUnitTestCase
{
    /** @var LocaleListener */
    protected $listener;
    protected $routeMock;
    protected $dmMock;

    public function setUp()
    {
        $this->listener = new LocaleListener('/prefix/path', array('en', 'de'));
        $this->routeMock = $this->buildMock('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route');
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

        $this->routeMock->expects($this->once())
            ->method('getRequirement')
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
