<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Form\Type;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Form type for rendering a checkbox with a label that can contain links to pages
 *
 * Usage: supply an array with content_ids with the form type options. The form type will generate the
 * urls using the router and replace the array keys from the content_ids array with the urls in the form types label
 *
 * A typical use case is a checkbox the user needs to check to accept terms that are on a different page that has a
 * dynamic route.
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class TermsFormType extends AbstractType
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('content_ids', $options['content_ids']);
        parent::buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $contentIds = $form->getAttribute('content_ids');
        $paths = array();
        foreach ($contentIds as $key => $id) {
            $paths[$key] = $this->router->generate(null, array('content_id' => $id));
        }
        $view->vars['content_paths'] = $paths;
        parent::buildView($view, $form, $options);
    }

    public function getDefaultOptions(array $options)
    {
        return array('content_ids' => array());
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'cmf_routing_terms_form_type';
    }

    public function getParent()
    {
        return 'checkbox';
    }

}
