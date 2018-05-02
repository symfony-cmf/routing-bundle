<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @deprecated Will be removed in 3.0. Use RouteDefaultsTwigValidator with twig as template engine instead.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class RouteDefaultsTemplatingValidator extends ConstraintValidator
{
    private $controllerResolver;

    /**
     * @var EngineInterface
     */
    private $templating;

    public function __construct(ControllerResolverInterface $controllerResolver, EngineInterface $templating)
    {
        $this->controllerResolver = $controllerResolver;
        $this->templating = $templating;
    }

    public function validate($defaults, Constraint $constraint)
    {
        if (isset($defaults['_controller']) && null !== $defaults['_controller']) {
            $controller = $defaults['_controller'];

            $request = new Request([], [], ['_controller' => $controller]);

            try {
                $this->controllerResolver->getController($request);
            } catch (\LogicException $e) {
                $this->context->addViolation($e->getMessage());
            }
        }

        if (isset($defaults['_template']) && null !== $defaults['_template']) {
            $template = $defaults['_template'];

            if (false === $this->templating->exists($template)) {
                $this->context->addViolation($constraint->message, ['%name%' => $template]);
            }
        }
    }
}
