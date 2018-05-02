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
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

/*
 * @Todo: Remove this when we drop Symfony 2.x support
 */
if (!class_exists(ConstraintValidatorTestCase::class)) {
    abstract class HackBaseClass extends AbstractConstraintValidatorTest
    {
    }
} else {
    abstract class HackBaseClass extends ConstraintValidatorTestCase
    {
    }
}

abstract class RouteDefaultsValidatorTest extends HackBaseClass
{
    protected $controllerResolver;

    protected $engine;

    public function testCorrectControllerPath()
    {
        $this->validator->validate(['_controller' => 'FrameworkBundle:Redirect:redirect'], new RouteDefaults());

        $this->assertNoViolation();
    }

    public function testControllerPathViolation()
    {
        $this->controllerResolver->expects($this->any())
            ->method('getController')
            ->will($this->throwException(new \LogicException('Invalid controller')))
        ;

        $this->validator->validate(['_controller' => 'NotExistingBundle:Foo:bar'], new RouteDefaults());

        $this->buildViolation('Invalid controller')->assertRaised();
    }

    public function testCorrectTemplate()
    {
        $this->engine->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true))
        ;

        $this->validator->validate(['_template' => 'TwigBundle::layout.html.twig'], $this->constraint);

        $this->assertNoViolation();
    }

    public function testTemplateViolation()
    {
        $this
            ->engine
            ->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false))
        ;

        $this->validator->validate(
            ['_template' => 'NotExistingBundle:Foo:bar.html.twig'],
            new RouteDefaults(['message' => 'my message'])
        );

        $this->buildViolation('my message')
            ->setParameter('%name%', 'NotExistingBundle:Foo:bar.html.twig')
            ->assertRaised();
    }
}
