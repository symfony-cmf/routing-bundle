<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('cmf_routing.orm_content_repository', \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\ContentRepository::class)
        ->args([service('doctrine')])
        ->call('setManagerName', ['%cmf_routing.dynamic.persistence.orm.manager_name%']);

    $services->alias('cmf_routing.content_repository', 'cmf_routing.orm_content_repository');

    $services->set('cmf_routing.orm_candidates', \Symfony\Cmf\Component\Routing\Candidates\Candidates::class)
        ->args([
            '%cmf_routing.dynamic.locales%',
            '%cmf_routing.dynamic.limit_candidates%',
        ]);

    $services->set('cmf_routing.route_provider', \Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\RouteProvider::class)
        ->public()
        ->args([
            service('doctrine'),
            service('cmf_routing.orm_candidates'),
            '%cmf_routing.dynamic.persistence.orm.route_class%',
        ])
        ->call('setManagerName', ['%cmf_routing.dynamic.persistence.orm.manager_name%'])
        ->call('setRouteCollectionLimit', ['%cmf_routing.route_collection_limit%']);
};
