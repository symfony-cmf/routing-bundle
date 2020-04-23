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
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\ValidationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ValidationPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ValidationPass());
    }

    /**
     * It should register the PHPCR documents for validation only when:
     *  - the PHP backend is enabled AND
     *  - the validator service is available.
     *
     * @dataProvider provideDocumentsValidationContext
     */
    public function testRegisterDocumentsValidation($hasPhpcr, $hasValidator, $shouldBeRegistered)
    {
        if ($hasPhpcr) {
            $this->setParameter('cmf_routing.backend_type_phpcr', null);
        }

        if ($hasValidator) {
            $this->registerService('validator.builder', \stdClass::class);
        }

        $this->compile();

        if ($shouldBeRegistered) {
            $r = new \ReflectionClass(ValidationPass::class);
            $dir = \dirname($r->getFilename());

            $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
                'validator.builder',
                'addXmlMappings',
                [["{$dir}/../../Resources/config/validation-phpcr.xml"]]
            );
        } elseif ($hasValidator) {
            $definition = $this->container->findDefinition('validator.builder');

            $this->assertFalse($definition->hasMethodCall('addXmlMappings'));
        } else {
            $this->assertContainerBuilderNotHasService('validator.builder');
        }
    }

    /**
     * Provides the contexts in witch the documents validation registering occurs.
     *
     * A context is:
     *
     *  `[$hasPhpcr, $hasValidator, $shouldBeRegistered]`
     *
     * where:
     *  - _$hasPhpcr: Is the PHPCR backend enabled ?
     *  - _$hasValidator: Is the validator available ?
     *  - _$shouldBeRegistered_: Should the documents validation be registered ?
     */
    public function provideDocumentsValidationContext()
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false],
        ];
    }
}
