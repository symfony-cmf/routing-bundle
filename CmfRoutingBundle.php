<?php

namespace Symfony\Cmf\Bundle\RoutingBundle;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRoutersPass;
use Symfony\Cmf\Component\Routing\DependencyInjection\Compiler\RegisterRouteEnhancersPass;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\SetRouterPass;

/**
 * Bundle class
 */
class CmfRoutingBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterRoutersPass());
        $container->addCompilerPass(new RegisterRouteEnhancersPass());
        $container->addCompilerPass(new SetRouterPass());

        if (class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')) {
            $container->addCompilerPass($this->buildBasePhpcrCompilerPass());
            $container->addCompilerPass(
                DoctrinePhpcrMappingsPass::createXmlMappingDriver(
                    array(
                        realpath(__DIR__ . '/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\RoutingBundle\Model',
                        realpath(__DIR__ . '/Resources/config/doctrine-phpcr') => 'Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr',
                    ),
                    array('cmf_routing.manager_name')
                )
            );
        }
    }

    /**
     * Build the compiler pass for the symfony core routing component. The
     * factory method uses the SymfonyFileLocator which will look at the
     * namespace and thus not work here.
     *
     * @return DoctrinePhpcrMappingsPass
     */
    private function buildBasePhpcrCompilerPass()
    {
        $arguments = array(array(realpath(__DIR__ . '/Resources/config/doctrine-base')), '.phpcr.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver', array($locator));

        return new DoctrinePhpcrMappingsPass(
            $driver,
            array('Symfony\Component\Routing'),
            array('cmf_routing.manager_name'),
            'cmf_routing.backend_type_phpcr'
        );
    }
}
