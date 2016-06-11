<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Form\Type;

use Sonata\DoctrinePHPCRAdminBundle\Form\Type\TreeModelType;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteGeneralData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteGeneralType extends AbstractType
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * RouteGeneralType constructor.
     * @param ModelManager $modelManager
     *
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => false], ['help' => 'form.help_name'])
            ->add(
                'parentDocument',
                TreeModelType::class,
                [
                    'choice_list' => [],
                    'select_root_node' => $options['select_root_node'],
                    'root_node' => $options['root_node'],
                    'model_manager' => $this->modelManager,
                    'repository_name' => $options['repository_name'],
                ]
            );
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RouteGeneralData::class,
            'root_node' => null,
            'select_root_node' => true,
            'repository_name' => null,
        ]);
    }
}
