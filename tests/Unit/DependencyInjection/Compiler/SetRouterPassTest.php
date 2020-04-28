<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetRouterPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SetRouterPass());
    }

    public function testMapperPassReplacesRouterAlias()
    {
        $this->container->setParameter('cmf_routing.replace_symfony_router', true);

        $this->compile();

        $this->assertContainerBuilderHasAlias('router', 'cmf_routing.router');
    }

    public function testMapperPassDoesNotReplaceRouterAlias()
    {
        $this->container->setParameter('cmf_routing.replace_symfony_router', false);

        $this->compile();

        $this->assertFalse($this->container->hasAlias('router'));
    }
}
