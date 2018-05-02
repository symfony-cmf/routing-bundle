<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Form\Type;

use Symfony\Cmf\Bundle\RoutingBundle\Form\Type\RouteTypeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteTypeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouteTypeType
     */
    private $type;

    public function setUp()
    {
        $this->type = new RouteTypeType();
    }

    public function testSetDefaultOptions()
    {
        $type = new RouteTypeType();
        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $this->assertInternalType('array', $options['choices']);
    }

    public function testDefaultsSet()
    {
        $this->type->addRouteType('foobar');
        $this->type->addRouteType('barfoo');

        $optionsResolver = $this->createMock(OptionsResolver::class);
        $optionsResolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'choices' => [
                    'foobar' => 'route_type.foobar',
                    'barfoo' => 'route_type.barfoo',
                ],
                'translation_domain' => 'CmfRoutingBundle',
            ]);

        $this->type->configureOptions($optionsResolver);
    }
}
