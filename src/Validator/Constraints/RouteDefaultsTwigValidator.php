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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Twig\Loader\LoaderInterface;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class RouteDefaultsTwigValidator extends ConstraintValidator
{
    private $controllerResolver;

    /**
     * @var LoaderInterface|null
     */
    private $twig;

    public function __construct(ControllerResolverInterface $controllerResolver, ?LoaderInterface $twig)
    {
        $this->controllerResolver = $controllerResolver;
        $this->twig = $twig;
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

        if (null !== $this->twig && isset($defaults['_template']) && null !== $defaults['_template']) {
            $template = $defaults['_template'];

            if (false === $this->twig->exists($template)) {
                $this->context->addViolation($constraint->message, ['%name%' => $template]);
            }
        }
    }
}
