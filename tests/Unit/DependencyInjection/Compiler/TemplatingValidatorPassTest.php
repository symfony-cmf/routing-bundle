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
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\TemplatingValidatorPass;
use Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTemplatingValidator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TemplatingValidatorPassTest extends AbstractCompilerPassTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->registerValidatorService();
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TemplatingValidatorPass());
    }

    /**
     * It should replace the validator class with the templating one and
     * provide the _templating_ service as second argument.
     */
    public function testReplacesValidator()
    {
        $this->registerService('templating', \stdClass::class);

        $this->compile();

        $definition = $this->container->getDefinition('cmf_routing.validator.route_defaults');

        $this->assertEquals(RouteDefaultsTemplatingValidator::class, $definition->getClass());
        $this->assertEquals(['foo', new Reference('templating')], $definition->getArguments());
    }

    /**
     * It should leave the validator untouched when the _templating_ service
     * is not available.
     */
    public function testDoesNotReplaceValidator()
    {
        $this->compile();

        $definition = $this->container->getDefinition('cmf_routing.validator.route_defaults');

        $this->assertEquals(\stdClass::class, $definition->getClass());
        $this->assertEquals(['foo', 'bar'], $definition->getArguments());
    }

    private function registerValidatorService()
    {
        $definition = $this->registerService('cmf_routing.validator.route_defaults', \stdClass::class);

        $definition->setArguments(['foo', 'bar']);
    }
}
