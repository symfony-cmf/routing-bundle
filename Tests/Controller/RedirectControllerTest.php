<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Controller;

use Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class RedirectControllerTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new RedirectController($this->buildMock('Symfony\Component\Routing\RouterInterface'));
    }

    // the rest is covered by functional test
}
