<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Admin;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaults;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsValidator;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

class RouteDefaultsValidatorTest extends AbstractConstraintValidatorTest
{
    private $controllerResolver;
    private $templating;

    protected function setUp()
    {
        $this->controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $this->templating = $this->createMock(EngineInterface::class);

        parent::setUp();
    }

    protected function createValidator()
    {
        return new RouteDefaultsValidator($this->controllerResolver, $this->templating);
    }

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
        $this->templating->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true))
        ;

        $this->validator->validate(['_template' => 'TwigBundle::layout.html.twig'], $this->constraint);

        $this->assertNoViolation();
    }

    public function testTemplateViolation()
    {
        $this
            ->templating
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
