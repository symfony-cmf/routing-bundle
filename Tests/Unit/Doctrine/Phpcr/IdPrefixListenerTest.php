<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\IdPrefixListener;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class IdPrefixListenerTest extends CmfUnitTestCase
{
    public function testConstructor()
    {
        new IdPrefixListener('test');
    }

    // the rest is covered by functional test
}
