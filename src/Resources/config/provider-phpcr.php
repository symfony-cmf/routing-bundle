<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('cmf_routing.phpcr_route_provider', \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider::class)
        ->args([
            service('doctrine_phpcr'),
            service('cmf_routing.phpcr_candidates_prefix'),
            null,
            service('logger')->ignoreOnInvalid(),
        ])
        ->call('setManagerName', ['%cmf_routing.dynamic.persistence.phpcr.manager_name%'])
        ->call('setRouteCollectionLimit', ['%cmf_routing.route_collection_limit%']);

    $services->alias('cmf_routing.route_provider', 'cmf_routing.phpcr_route_provider')
        ->public();

    $services->set('cmf_routing.phpcr_candidates_prefix', \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\PrefixCandidates::class)
        ->args([
            '%cmf_routing.dynamic.persistence.phpcr.route_basepaths%',
            '%cmf_routing.dynamic.locales%',
            service('doctrine_phpcr'),
            '%cmf_routing.dynamic.limit_candidates%',
        ])
        ->call('setManagerName', ['%cmf_routing.dynamic.persistence.phpcr.manager_name%']);

    $services->set('cmf_routing.phpcr_content_repository', \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\ContentRepository::class)
        ->args([service('doctrine_phpcr')])
        ->call('setManagerName', ['%cmf_routing.dynamic.persistence.phpcr.manager_name%']);

    $services->alias('cmf_routing.content_repository', 'cmf_routing.phpcr_content_repository');

    $services->set('cmf_routing.phpcrodm_route_idprefix_listener', \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\IdPrefixListener::class)
        ->args([service('cmf_routing.phpcr_candidates_prefix')])
        ->tag('doctrine_phpcr.event_listener', ['event' => 'postLoad'])
        ->tag('doctrine_phpcr.event_listener', ['event' => 'postPersist'])
        ->tag('doctrine_phpcr.event_listener', ['event' => 'postMove']);

    $services->set('cmf_routing.phpcrodm_route_locale_listener', \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\LocaleListener::class)
        ->args([
            service('cmf_routing.phpcr_candidates_prefix'),
            '%cmf_routing.dynamic.locales%',
            '%cmf_routing.dynamic.auto_locale_pattern%',
        ])
        ->tag('doctrine_phpcr.event_listener', ['event' => 'postLoad'])
        ->tag('doctrine_phpcr.event_listener', ['event' => 'postPersist'])
        ->tag('doctrine_phpcr.event_listener', ['event' => 'postMove']);
};
