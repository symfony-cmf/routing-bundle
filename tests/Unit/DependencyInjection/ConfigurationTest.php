<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    protected function getContainerExtension(): ExtensionInterface
    {
        return new CmfRoutingExtension();
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function testSupportsAllConfigFormats()
    {
        $expectedConfiguration = [
            'chain' => [
                'routers_by_id' => [
                    'cmf_routing.router' => 300,
                    'router.default' => 100,
                ],
                'replace_symfony_router' => true,
            ],
            'dynamic' => [
                'route_collection_limit' => 0,
                'generic_controller' => 'acme_main.controller:mainAction',
                'controllers_by_type' => [
                    'editable' => 'acme_main.some_controller:editableAction',
                ],
                'controllers_by_class' => [
                    'Symfony\Cmf\Bundle\ContentBundle\Document\StaticContent' => 'cmf_content.controller:indexAction',
                ],
                'templates_by_class' => [
                    'Symfony\Cmf\Bundle\ContentBundle\Document\StaticContent' => 'CmfContentBundle:StaticContent:index.html.twig',
                ],
                'persistence' => [
                    'phpcr' => [
                        'enabled' => true,
                        'route_basepaths' => [
                            '/cms/routes',
                            '/simple',
                        ],
                        'manager_name' => null,
                        'enable_initializer' => true,
                    ],
                    'orm' => [
                        'enabled' => false,
                        'manager_name' => null,
                        'route_class' => 'Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route',
                    ],
                ],
                'enabled' => true,
                'default_controller' => null,
                'uri_filter_regexp' => '',
                'route_filters_by_id' => [],
                'locales' => ['en', 'fr'],
                'limit_candidates' => 20,
                'auto_locale_pattern' => true,
                'match_implicit_locale' => true,
                'url_generator' => 'cmf_routing.generator',
            ],
        ];

        $formats = array_map(function ($path) {
            return __DIR__.'/../../Fixtures/fixtures/'.$path;
        }, [
            'config/config.yml',
            'config/config.xml',
            'config/config.php',
        ]);

        foreach ($formats as $format) {
            $this->assertProcessedConfigurationEquals($expectedConfiguration, [$format]);
        }
    }
}
