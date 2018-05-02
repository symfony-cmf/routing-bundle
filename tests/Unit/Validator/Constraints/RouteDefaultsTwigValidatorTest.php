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

use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTwigValidator;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Twig\Loader\LoaderInterface;

class RouteDefaultsTwigValidatorTest extends RouteDefaultsValidatorTest
{
    protected function setUp()
    {
        $this->controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $this->engine = $this->createMock(LoaderInterface::class);

        parent::setUp();
    }

    protected function createValidator()
    {
        return new RouteDefaultsTwigValidator($this->controllerResolver, $this->engine);
    }
}
