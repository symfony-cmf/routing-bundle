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
use Doctrine\ODM\PHPCR\Event\MoveEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\LocaleListener;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\PrefixCandidates;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

class LocaleListenerTest extends TestCase
{
    private LocaleListener $listener;

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

        $this->listener = new LocaleListener($this->candidates, ['en', 'de']);
        $this->routeMock = $this->createMock(Route::class);
        $this->dmMock = $this->createMock(DocumentManager::class);
    }

    public function testNoRoute(): void
    {
        $args = new LifecycleEventArgs($this, $this->dmMock);
        $originalArgs = clone $args;
        $this->listener->postLoad($args);
        $this->listener->postPersist($args);
        $this->assertEquals($originalArgs, $args);
    }

    public function testNoPrefixMatch(): void
    {
        $this->routeMock->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/outside/de/my/route')
        ;

        $this->routeMock->expects($this->never())
            ->method('setDefault')
        ;
        $this->routeMock->expects($this->never())
            ->method('setRequirement')
        ;

        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->listener->postLoad($args);
    }

    private function prepareMatch()
    {
        $this->routeMock->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/routes/de/my/route')
        ;

        $this->routeMock->expects($this->once())
            ->method('setDefault')
            ->with('_locale', 'de')
        ;
        $this->routeMock->expects($this->once())
            ->method('setRequirement')
            ->with('_locale', 'de')
        ;

        return new LifecycleEventArgs($this->routeMock, $this->dmMock);
    }

    public function testLoad(): void
    {
        $this->listener->postLoad($this->prepareMatch());
    }

    public function testPersist(): void
    {
        $this->listener->postPersist($this->prepareMatch());
    }

    public function testSecond(): void
    {
        $this->routeMock->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/simple/de')
        ;

        $this->routeMock->expects($this->once())
            ->method('setDefault')
            ->with('_locale', 'de')
        ;
        $this->routeMock->expects($this->once())
            ->method('setRequirement')
            ->with('_locale', 'de')
        ;

        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);
        $this->listener->postLoad($args);
    }

    public function testMoveNoRoute(): void
    {
        $moveArgs = new MoveEventArgs(
            $this,
            $this->dmMock,
            '/cms/routes/de/my/route',
            '/cms/routes/en/my/route'
        );
        $originalArgs = clone $moveArgs;

        $this->listener->postMove($moveArgs);
        $this->assertEquals($originalArgs, $moveArgs);
    }

    public function testMoved(): void
    {
        $moveArgs = new MoveEventArgs(
            $this->routeMock,
            $this->dmMock,
            '/cms/routes/de/my/route',
            '/cms/routes/en/my/route'
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

    public function testSetLocales(): void
    {
        $this->listener->setLocales(['xx']);
        $reflection = new \ReflectionClass(LocaleListener::class);
        $locales = $reflection->getProperty('locales');
        $locales->setAccessible(true);
        $this->assertSame(['xx'], $locales->getValue($this->listener));
    }

    public function testHasLocale(): void
    {
        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->routeMock->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/routes/de/my/route')
        ;

        $this->routeMock->expects($this->once())
            ->method('getDefault')
            ->with('_locale')
            ->willReturn('some')
        ;

        $this->routeMock->expects($this->once())
            ->method('getRequirement')
            ->with('_locale')
            ->willReturn('some')
        ;

        $this->routeMock->expects($this->never())
            ->method('setDefault')
        ;
        $this->routeMock->expects($this->never())
            ->method('setRequirement')
        ;

        $this->listener->postLoad($args);
    }

    /**
     * URL without locale, addLocalePattern not set.
     */
    public function testNoLocaleUrl(): void
    {
        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);

        $this->routeMock->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/routes/my/route')
        ;

        $this->routeMock->expects($this->never())
            ->method('setDefault')
        ;
        $this->routeMock->expects($this->never())
            ->method('setRequirement')
        ;

        $this->listener->postLoad($args);
    }

    /**
     * URL without locale, addLocalePattern set.
     */
    public function testLocalePattern(): void
    {
        $this->routeMock->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/simple/something')
        ;

        $this->routeMock->expects($this->once())
            ->method('setOption')
            ->with('add_locale_pattern', true)
        ;

        $this->listener->setAddLocalePattern(true);
        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);
        $this->listener->postLoad($args);
    }

    /**
     * URL without locale, set available translations.
     */
    public function testAvailableTranslations(): void
    {
        $this->routeMock->expects($this->once())
            ->method('getId')
            ->willReturn('/cms/simple/something')
        ;

        $this->dmMock->expects($this->once())
            ->method('isDocumentTranslatable')
            ->with($this->routeMock)
            ->willReturn(true)
        ;
        $this->dmMock->expects($this->once())
            ->method('getLocalesFor')
            ->with($this->routeMock, true)
            ->willReturn(['en', 'de', 'fr'])
        ;
        $this->routeMock->expects($this->once())
            ->method('setRequirement')
            ->with('_locale', 'en|de|fr')
        ;

        $this->listener->setUpdateAvailableTranslations(true);
        $args = new LifecycleEventArgs($this->routeMock, $this->dmMock);
        $this->listener->postLoad($args);
    }
}
