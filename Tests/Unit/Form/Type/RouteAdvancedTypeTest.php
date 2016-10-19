<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Form\Type;

use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteAdvancedData;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Type\RouteAdvancedType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteAdvancedTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = ['variablePattern' => 'pattern'];
        $form = $this->factory->create(RouteAdvancedType::class);

        // submit the data to the form directly
        $form->submit($formData);

        $dto = new RouteAdvancedData();
        $dto->variablePattern = 'pattern';

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($dto, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSetDefaultOptions()
    {
        $type = new RouteAdvancedType();
        $optionsResolver = new OptionsResolver();

        $type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $this->assertInternalType('array', $options['options_keys']);
        $this->assertInternalType('array', $options['options_keys']);
    }
}
