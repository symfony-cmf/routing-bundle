<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Admin;

use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaults;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsValidator;

class RouteDefaultsValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $controllerResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $templating;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var RouteDefaultsValidator
     */
    private $validator;

    /**
     * @var RouteDefaults
     */
    private $constraint;

    public function setUp()
    {
        $this->controllerResolver = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');
        $this->templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContextInterface');
        $this->constraint = new RouteDefaults();
        $this->validator = new RouteDefaultsValidator($this->controllerResolver, $this->templating);
        $this->validator->initialize($this->context);
    }

    public function testCorrectControllerPath()
    {
        $this
            ->context
            ->expects($this->never())
            ->method('addViolation')
        ;

        $this->validator->validate(array('_controller' => 'FrameworkBundle:Redirect:redirect'), $this->constraint);
    }

    public function testControllerPathViolation()
    {
        $message = 'Invalid controller';

        $this
            ->controllerResolver
            ->expects($this->any())
            ->method('getController')
            ->will($this->throwException(new \LogicException($message)))
        ;

        $this
            ->context
            ->expects($this->once())
            ->method('addViolation')
            ->with($this->equalTo($message))
        ;

        $this->validator->validate(array('_controller' => 'NotExistingBundle:Foo:bar'), $this->constraint);
    }

    public function testCorrectTemplate()
    {
        $this
            ->templating
            ->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true))
        ;

        $this
            ->context
            ->expects($this->never())
            ->method('addViolation')
        ;

        $this->validator->validate(array('_template' => 'TwigBundle::layout.html.twig'), $this->constraint);
    }

    public function testTemplateViolation()
    {
        $this
            ->templating
            ->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false))
        ;

        $this
            ->context
            ->expects($this->once())
            ->method('addViolation')
            ->with($this->equalTo($this->constraint->message))
        ;

        $this->validator->validate(array('_template' => 'NotExistingBundle:Foo:bar.html.twig'), $this->constraint);
    }
}
