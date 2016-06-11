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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RedirectRouteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $rootNode = array_key_exists('root_node', $options) ? $options['root_node'] : [];
        $builder
            ->add(
                'parentDocument',
                TreeModelType::class,
                ['choice_list' => [], 'select_root_node' => true, 'root_node' => $rootNode]
            )
            ->add('name', TextType::class)
            ->add('routeName', TextType::class, ['required' => false])
            ->add('uri', TextType::class, ['required' => false])
            ->add(
                'routeTarget',
                TreeModelType::class,
                ['choice_list' => [], 'required' => false, 'root_node' => $rootNode]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'root_node' => null
        ]);
    }
}
