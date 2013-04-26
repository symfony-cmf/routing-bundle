<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Listener;

use Symfony\Cmf\Bundle\RoutingBundle\Listener\IdPrefix;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class IdPrefixTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new IdPrefix('test');
    }

    // the rest is covered by functional test
}
