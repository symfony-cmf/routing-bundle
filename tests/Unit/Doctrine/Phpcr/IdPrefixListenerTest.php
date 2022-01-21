<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\IdPrefixListener;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\PrefixCandidates;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

final class IdPrefixListenerTest extends TestCase
{
    private IdPrefixListener $listener;

    private PrefixCandidates $candidates;

    /**
     * @var DocumentManager&MockObject
     */
    private DocumentManager $dmMock;

    /**
     * @var Route&MockObject
     */
    private Route $routeMock;

    public function setUp(): void
    {
        $this->candidates = new PrefixCandidates(['/cms/routes', '/cms/simple']);

        $this->dmMock = $this->createMock(DocumentManager::class);
        $this->routeMock = $this->createMock(Route::class);

        $this->listener = new IdPrefixListener($this->candidates);
    }

    public function testNoRoute(): void
    {
        $args = new LifecycleEventArgs($this, $this->dmMock);
        $originalArgs = clone $args;

        $this->listener->postLoad($args);
        $this->assertEquals($originalArgs, $args);
    }

    private function prepareMatch(): LifecycleEventArgs
    {
        $this->routeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/routes')
        ;
        $this->routeMock
            ->expects($this->once())
            ->method('setPrefix')
            ->with('/cms/routes')
        ;

        return new LifecycleEventArgs($this->routeMock, $this->dmMock);
    }

    public function testPostLoad(): void
    {
        $this->listener->postLoad($this->prepareMatch());
    }

    public function testPostPersist(): void
    {
        $this->listener->postPersist($this->prepareMatch());
    }

    public function testPostMove(): void
    {
        $this->listener->postMove($this->prepareMatch());
    }

    public function testSecond(): void
    {
        $this->routeMock
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('/cms/simple/test')
        ;
        $this->routeMock
            ->expects($this->once())
            ->method('setPrefix')
            ->with('/cms/simple')
        ;

        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->listener->postLoad($args);
    }

    public function testOutside(): void
    {
        $this->routeMock
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn('/outside')
        ;
        $this->routeMock
            ->expects($this->never())
            ->method('setPrefix')
        ;

        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->listener->postLoad($args);
    }
}
