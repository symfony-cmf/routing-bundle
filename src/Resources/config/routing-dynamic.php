<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('cmf_routing.uri_filter_regexp', null);

    $services->set('cmf_routing.enhancer.route_content', \Symfony\Cmf\Component\Routing\Enhancer\RouteContentEnhancer::class)
        ->args([
            '_route_object',
            '_content',
        ])
        ->tag('dynamic_router_route_enhancer', ['priority' => 100]);

    $services->set('cmf_routing.enhancer.default_controller', \Symfony\Cmf\Component\Routing\Enhancer\FieldPresenceEnhancer::class)
        ->private()
        ->args([
            null,
            '_controller',
            '%cmf_routing.default_controller%',
        ]);

    $services->set('cmf_routing.enhancer.explicit_template', \Symfony\Cmf\Component\Routing\Enhancer\FieldPresenceEnhancer::class)
        ->private()
        ->args([
            '_template',
            '_controller',
            '%cmf_routing.generic_controller%',
        ]);

    $services->set('cmf_routing.enhancer.controllers_by_type', \Symfony\Cmf\Component\Routing\Enhancer\FieldMapEnhancer::class)
        ->private()
        ->args([
            'type',
            '_controller',
            '%cmf_routing.controllers_by_type%',
        ]);

    $services->set('cmf_routing.enhancer.controllers_by_class', \Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer::class)
        ->private()
        ->args([
            '_content',
            '_controller',
            '%cmf_routing.controllers_by_class%',
        ]);

    $services->set('cmf_routing.enhancer.controller_for_templates_by_class', \Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer::class)
        ->private()
        ->args([
            '_content',
            '_controller',
            [],
        ]);

    $services->set('cmf_routing.enhancer.templates_by_class', \Symfony\Cmf\Component\Routing\Enhancer\FieldByClassEnhancer::class)
        ->private()
        ->args([
            '_content',
            '_template',
            '%cmf_routing.templates_by_class%',
        ]);

    $services->set('cmf_routing.enhancer.content_repository', \Symfony\Cmf\Component\Routing\Enhancer\ContentRepositoryEnhancer::class)
        ->private()
        ->args([service('cmf_routing.content_repository')]);

    $services->set('cmf_routing.dynamic_router', \Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter::class)
        ->args([
            service('router.request_context'),
            service('cmf_routing.nested_matcher'),
            '',
            '%cmf_routing.uri_filter_regexp%',
            service('event_dispatcher')->ignoreOnInvalid(),
            service('cmf_routing.route_provider'),
        ])
        ->call('setRequestStack', [service('request_stack')]);

    $services->set('cmf_routing.nested_matcher', \Symfony\Cmf\Component\Routing\NestedMatcher\NestedMatcher::class)
        ->args([
            service('cmf_routing.route_provider'),
            service('cmf_routing.final_matcher'),
        ]);

    $services->set('cmf_routing.matcher.dummy_collection', \Symfony\Component\Routing\RouteCollection::class)
        ->private();

    $services->set('cmf_routing.matcher.dummy_context', \Symfony\Component\Routing\RequestContext::class)
        ->private();

    $services->set('cmf_routing.final_matcher', \Symfony\Cmf\Component\Routing\NestedMatcher\UrlMatcher::class)
        ->args([
            service('cmf_routing.matcher.dummy_collection'),
            service('cmf_routing.matcher.dummy_context'),
        ]);

    $services->set('cmf_routing.generator', \Symfony\Cmf\Component\Routing\ContentAwareGenerator::class)
        ->args([
            service('cmf_routing.route_provider'),
            service('logger')->ignoreOnInvalid(),
        ]);

    $services->set('cmf_routing.redirect_controller', \Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController::class)
        ->public()
        ->args([service('router')]);
};
