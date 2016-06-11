<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\WebTest\Form\Type;

use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteGeneralData;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Type\RouteGeneralType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteGeneralTypeTest extends TypeTestCase
{
    private $type;
    private $modelManagerMock;

    public function setUp()
    {
        parent::setUp();
        $this->modelManagerMock = $this->getMockBuilder(ModelManager::class)->disableOriginalConstructor()->getMock();
        $this->type = new RouteGeneralType($this->modelManagerMock);
    }

    public function testSubmitValidData()
    {
        $this->modelManagerMock
            ->expects($this->any())
            ->method('find')
            ->with($this->equalTo(null), $this->equalTo('/parend/doc'))
            ->will($this->returnValue(new \stdClass()));
        $formData = ['parentDocument' => '/parend/doc', 'name' => 'some-name'];
        $form = $this->factory->create($this->type);

        // submit the data to the form directly
        $form->submit($formData);

        $dto = new RouteGeneralData();
        $dto->parentDocument = new \stdClass();
        $dto->name = 'some-name';

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
        $optionsResolver = new OptionsResolver();

        $this->type->configureOptions($optionsResolver);

        $options = $optionsResolver->resolve();

        $this->assertEquals([
            'data_class' => RouteGeneralData::class,
            'root_node' => null,
            'select_root_node' => true,
            'repository_name' => null,
        ], $options);
    }
}
