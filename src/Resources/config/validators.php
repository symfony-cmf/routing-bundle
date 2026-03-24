<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('cmf_routing.validator.route_defaults', \Symfony\Cmf\Bundle\RoutingBundle\Validator\Constraints\RouteDefaultsTwigValidator::class)
        ->args([
            service('controller_resolver'),
            service('twig.loader')->nullOnInvalid(),
        ])
        ->tag('validator.constraint_validator', ['alias' => 'cmf_routing.validator.route_defaults']);
};
