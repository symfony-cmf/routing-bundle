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
use Doctrine\ODM\PHPCR\Query\Builder\ConstraintFactory;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooserInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\PrefixCandidates;
use Symfony\Component\HttpFoundation\Request;

class PrefixCandidatesTest extends TestCase
{
    public function testAddPrefix()
    {
        $candidates = new PrefixCandidates(['/routes']);
        $this->assertEquals(['/routes'], $candidates->getPrefixes());
        $candidates->addPrefix('/simple');
        $this->assertEquals(['/routes', '/simple'], $candidates->getPrefixes());
        $candidates->setPrefixes(['/other']);
        $this->assertEquals(['/other'], $candidates->getPrefixes());
    }

    public function testGetCandidates()
    {
        $request = Request::create('/my/path.html');

        $candidates = new PrefixCandidates(['/routes', '/simple']);
        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            [
                '/routes/my/path.html',
                '/routes/my/path',
                '/routes/my',
                '/routes',
                '/simple/my/path.html',
                '/simple/my/path',
                '/simple/my',
                '/simple',
            ],
            $paths
        );
    }

    public function testGetCandidatesPercentEncoded()
    {
        $request = Request::create('/my/path%20percent%20encoded.html');

        $candidates = new PrefixCandidates(['/routes', '/simple']);
        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            [
                '/routes/my/path percent encoded.html',
                '/routes/my/path percent encoded',
                '/routes/my',
                '/routes',
                '/simple/my/path percent encoded.html',
                '/simple/my/path percent encoded',
                '/simple/my',
                '/simple',
            ],
            $paths
        );
    }

    public function testGetCandidatesLocales()
    {
        $request = Request::create('/de/path.html');

        $candidates = new PrefixCandidates(['/routes', '/simple'], ['de', 'fr']);
        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            [
                '/routes/de/path.html',
                '/routes/de/path',
                '/routes/de',
                '/routes',
                '/simple/de/path.html',
                '/simple/de/path',
                '/simple/de',
                '/simple',
                '/routes/path.html',
                '/routes/path',
                '/simple/path.html',
                '/simple/path',
            ],
            $paths
        );
    }

    public function testGetCandidatesLocalesDm()
    {
        $request = Request::create('/de/path.html');

        $dmMock = $this->createMock(DocumentManager::class);
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($dmMock))
        ;
        $localeMock = $this->createMock(LocaleChooserInterface::class);
        $localeMock
            ->expects($this->once())
            ->method('setLocale')
            ->with('de')
        ;
        $dmMock
            ->expects($this->once())
            ->method('getLocaleChooserStrategy')
            ->will($this->returnValue($localeMock))
        ;

        $candidates = new PrefixCandidates(['/simple'], ['de', 'fr'], $managerRegistryMock);
        $candidates->getCandidates($request);
    }

    public function testGetCandidatesLocalesDmNoLocale()
    {
        $request = Request::create('/it/path.html');
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock
            ->expects($this->never())
            ->method('getManager')
        ;

        $candidates = new PrefixCandidates(['/simple'], ['de', 'fr'], $managerRegistryMock);
        $candidates->getCandidates($request);
    }

    public function testIsCandidate()
    {
        $candidates = new PrefixCandidates(['/routes']);
        $this->assertTrue($candidates->isCandidate('/routes'));
        $this->assertTrue($candidates->isCandidate('/routes/my/path'));
        $this->assertFalse($candidates->isCandidate('/other/my/path'));
        $this->assertFalse($candidates->isCandidate('/route'));
        $this->assertFalse($candidates->isCandidate('/routesnotsame'));
    }

    public function testRestrictQuery()
    {
        $orX = $this->createMock(ConstraintFactory::class);
        $orX->expects($this->once())
            ->method('descendant')
            ->with('/routes', 'd')
        ;
        $andWhere = $this->createMock(ConstraintFactory::class);
        $andWhere->expects($this->once())
            ->method('orX')
            ->will($this->returnValue($orX))
        ;
        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->once())
            ->method('andWhere')
            ->will($this->returnValue($andWhere))
        ;
        $qb->expects($this->once())
            ->method('getPrimaryAlias')
            ->will($this->returnValue('d'))
        ;

        $candidates = new PrefixCandidates(['/routes']);
        $candidates->restrictQuery($qb);
    }

    public function testRestrictQueryGlobal()
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->never())
            ->method('andWhere')
        ;

        $candidates = new PrefixCandidates(['/routes', '', '/other']);
        $candidates->restrictQuery($qb);
    }
}
