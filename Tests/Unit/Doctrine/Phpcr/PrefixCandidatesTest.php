<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Phpcr;

use Doctrine\Common\Collections\ArrayCollection;
use PHPCR\Query\QueryInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\PrefixCandidates;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class PrefixCandidatesTest extends \PHPUnit_Framework_Testcase
{

    public function testAddPrefix()
    {
        $candidates = new PrefixCandidates(array('/routes'));
        $this->assertEquals(array('/routes'), $candidates->getPrefixes());
        $candidates->addPrefix('/simple');
        $this->assertEquals(array('/routes', '/simple'), $candidates->getPrefixes());
        $candidates->setPrefixes(array('/other'));
        $this->assertEquals(array('/other'), $candidates->getPrefixes());
    }

    public function testGetCandidates()
    {
        $request = Request::create('/my/path.html');

        $candidates = new PrefixCandidates(array('/routes', '/simple'));
        $paths = $candidates->getCandidates($request);

        $this->assertEquals(
            array(
                '/routes/my/path.html',
                '/routes/my/path',
                '/routes/my',
                '/routes',
                '/simple/my/path.html',
                '/simple/my/path',
                '/simple/my',
                '/simple',
            ),
            $paths
        );
    }

    public function testIsCandidate()
    {
        $candidates = new PrefixCandidates(array('/routes'));
        $this->assertTrue($candidates->isCandidate('/routes'));
        $this->assertTrue($candidates->isCandidate('/routes/my/path'));
        $this->assertFalse($candidates->isCandidate('/other/my/path'));
        $this->assertFalse($candidates->isCandidate('/route'));
    }

    public function testGetQueryRestriction()
    {
    }

}
