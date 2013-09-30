<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
