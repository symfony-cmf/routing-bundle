<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Controller;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Controller\RedirectController;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class RedirectControllerTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new RedirectController($this->buildMock('Symfony\Component\Routing\RouterInterface'));
    }

    // the rest is covered by functional test
}
