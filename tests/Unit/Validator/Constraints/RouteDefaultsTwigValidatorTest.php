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

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaults;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTwigValidator;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Test\ConstraintViolationAssertion;
use Twig\Loader\LoaderInterface;

class RouteDefaultsTwigValidatorTest extends ConstraintValidatorTestCase
{
    private MockObject&ControllerResolverInterface $controllerResolver;
    private MockObject&LoaderInterface $loader;

    protected function setUp(): void
    {
        $this->controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $this->loader = $this->createMock(LoaderInterface::class);

        parent::setUp();
        $this->constraint = new RouteDefaults();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new RouteDefaultsTwigValidator($this->controllerResolver, $this->loader);
    }

    public function testNoTemplateViolationWithoutTwig(): void
    {
        $this->validator = new RouteDefaultsTwigValidator($this->controllerResolver, null);
        $this->validator->validate(
            ['_template' => 'NotExistingBundle:Foo:bar.html.twig'],
            new RouteDefaults(['message' => 'my message'])
        );

        $this->assertNoViolation();
    }

    public function testCorrectControllerPath(): void
    {
        $this->validator->validate(['_controller' => 'FrameworkBundle:Redirect:redirect'], new RouteDefaults());

        $this->assertNoViolation();
    }

    public function testControllerPathViolation(): void
    {
        $this->controllerResolver
            ->method('getController')
            ->willThrowException(new \LogicException('Invalid controller'))
        ;

        $this->validator->validate(['_controller' => 'NotExistingBundle:Foo:bar'], new RouteDefaults());

        (new ConstraintViolationAssertion($this->context, 'Invalid controller', new NotNull()))->assertRaised();
    }

    public function testCorrectTemplate(): void
    {
        $this->loader
            ->method('exists')
            ->willReturn(true)
        ;

        $this->validator->validate(['_template' => 'TwigBundle::layout.html.twig'], $this->constraint);

        $this->assertNoViolation();
    }

    public function testTemplateViolation(): void
    {
        $this
            ->loader
            ->method('exists')
            ->willReturn(false)
        ;

        $this->validator->validate(
            ['_template' => 'NotExistingBundle:Foo:bar.html.twig'],
            new RouteDefaults(['message' => 'my message'])
        );

        (new ConstraintViolationAssertion($this->context, 'my message', new NotNull()))
            ->setParameter('%name%', 'NotExistingBundle:Foo:bar.html.twig')
            ->assertRaised()
        ;
    }
}
