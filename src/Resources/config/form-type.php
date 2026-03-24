<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('cmf_routing.route_type_form_type', \Symfony\Cmf\Bundle\RoutingBundle\Form\Type\RouteTypeType::class)
        ->tag('form.type', ['alias' => 'cmf_routing_route_type']);
};
