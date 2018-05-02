<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler;

use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTemplatingValidator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * To avoid a BC-Break: If templating component exists, we will use the validator using general templating
 * from FrameworkBundle.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class TemplatingValidatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('templating')) {
            $validatorDefinition = $container->getDefinition('cmf_routing.validator.route_defaults');
            $validatorDefinition->setClass(RouteDefaultsTemplatingValidator::class);
            $validatorDefinition->replaceArgument(1, new Reference('templating'));
        }
    }
}
