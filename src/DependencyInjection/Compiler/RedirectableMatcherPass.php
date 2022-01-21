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

use Symfony\Cmf\Bundle\RoutingBundle\Routing\RedirectableRequestMatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * If enabled, change the nested matcher implementation to the redirectable matcher.
 */
final class RedirectableMatcherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // only replace the nested matcher if config tells us to
        if (!$container->hasParameter('cmf_routing.redirectable_url_matcher') || false === $container->getParameter('cmf_routing.redirectable_url_matcher')) {
            return;
        }

        $definition = new Definition(RedirectableRequestMatcher::class);

        $container->setDefinition('cmf_routing.redirectable_request_matcher', $definition)
            ->setDecoratedService('cmf_routing.nested_matcher', 'cmf_routing.nested_matcher.inner')
            ->addArgument(new Reference('cmf_routing.nested_matcher.inner'))
            ->addArgument(new Reference('router.request_context'));
    }
}
