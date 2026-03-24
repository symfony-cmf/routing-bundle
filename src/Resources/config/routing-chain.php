<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('cmf_routing.router', \Symfony\Cmf\Component\Routing\ChainRouter::class)
        ->args([service('logger')->ignoreOnInvalid()])
        ->call('setContext', [service('router.request_context')]);

    $services->alias(\Symfony\Cmf\Component\Routing\ChainRouterInterface::class, 'cmf_routing.router');
};
