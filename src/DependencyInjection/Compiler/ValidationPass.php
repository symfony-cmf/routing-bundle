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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register validation files if their storage is activated.
 */
class ValidationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('cmf_routing.backend_type_phpcr') && $container->hasDefinition('validator.builder')) {
            $container
                ->getDefinition('validator.builder')
                ->addMethodCall('addXmlMappings', [[__DIR__.'/../../Resources/config/validation-phpcr.xml']]);
        }
    }
}
