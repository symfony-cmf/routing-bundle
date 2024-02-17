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

use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTemplatingValidator;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class RouteDefaultsTemplatingValidatorTest extends RouteDefaultsValidatorTest
{
    protected function mockEngine()
    {
        return $this->createMock(EngineInterface::class);
    }

    protected function setUp(): void
    {
        if (!class_exists(EngineInterface::class)) {
            $this->markTestSkipped();
        }

        parent::setUp();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new RouteDefaultsTemplatingValidator($this->controllerResolver, $this->engine);
    }
}
