<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Validator\Constraints;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTemplatingValidator;

class RouteDefaultsTemplatingValidatorTest extends RouteDefaultsValidatorTest
{
    protected function mockEngine()
    {
        return $this->createMock(EngineInterface::class);
    }

    protected function createValidator()
    {
        return new RouteDefaultsTemplatingValidator($this->controllerResolver, $this->engine);
    }
}
