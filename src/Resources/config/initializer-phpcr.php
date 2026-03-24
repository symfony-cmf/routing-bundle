<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('cmf_routing.initializer', \Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer::class)
        ->args([
            'CmfRoutingBundle',
            '%cmf_routing.dynamic.persistence.phpcr.initialized_basepaths%',
        ])
        ->tag('doctrine_phpcr.initializer');
};
