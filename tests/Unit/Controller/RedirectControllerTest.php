<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController;
use Symfony\Component\Routing\RouterInterface;

class RedirectControllerTest extends TestCase
{
    public function testConstructor()
    {
        new RedirectController($this->createMock(RouterInterface::class));
    }

    // the rest is covered by functional test
}
