<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Listener;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Listener\IdPrefix;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class IdPrefixTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new IdPrefix('test');
    }

    // the rest is covered by functional test
}
