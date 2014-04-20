<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Twig\Extension;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Cmf\Bundle\RoutingBundle\Twig\Extension\RoutingExtension;

class RoutingExtensionTest extends CmfUnitTestCase
{
    protected $generator;

    public function setUp()
    {
        $this->notFoundGenerator = $this->buildMock('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface');
        $this->notFoundGenerator->expects($this->any())
            ->method('generate')
            ->will($this->throwException(new RouteNotFoundException));
    }

    public function testCmfDocumentPathLogsRouteNotFoundException()
    {
        $logger = $this->buildMock('Psr\Log\LoggerInterface');

        $logger->expects($this->once())
            ->method('log');

        $extension = new RoutingExtension($this->notFoundGenerator, $logger);

        $extension->getDocumentPath('/test/bad/path');
    }

    public function testCmfDocumentUrlLogsRouteNotFoundException()
    {
        $logger = $this->buildMock('Psr\Log\LoggerInterface');

        $logger->expects($this->once())
            ->method('log');

        $extension = new RoutingExtension($this->notFoundGenerator, $logger);

        $extension->getDocumentUrl('/test/bad/path');
    }

}
