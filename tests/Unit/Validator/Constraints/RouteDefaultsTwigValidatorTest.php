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

use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaults;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTwigValidator;
use Twig\Loader\LoaderInterface;

class RouteDefaultsTwigValidatorTest extends RouteDefaultsValidatorTest
{
    protected function mockEngine()
    {
        return $this->createMock(LoaderInterface::class);
    }

    protected function createValidator()
    {
        return new RouteDefaultsTwigValidator($this->controllerResolver, $this->engine);
    }

    public function testNoTemplateViolationWithoutTwig()
    {
        $this->validator = new RouteDefaultsTwigValidator($this->controllerResolver, null);
        $this->validator->validate(
            ['_template' => 'NotExistingBundle:Foo:bar.html.twig'],
            new RouteDefaults(['message' => 'my message'])
        );

        $this->assertNoViolation();
    }
}
