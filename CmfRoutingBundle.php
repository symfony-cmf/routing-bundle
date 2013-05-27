<?php

namespace Symfony\Cmf\Bundle\RoutingBundle;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\ChainRouterPass;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\RouteEnhancerPass;
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
        $container->addCompilerPass(new ChainRouterPass());
        $container->addCompilerPass(new RouteEnhancerPass());
        $container->addCompilerPass(new SetRouterPass());
        $container->addCompilerPass($this->buildBasePhpcrCompilerPass());
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
        $arguments = array(array(realpath(__DIR__ . '/Resources/config/doctrine')), '.phpcr.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver', array($locator));

        return new DoctrinePhpcrMappingsPass(
            $driver,
            array('Symfony\Component\Routing'),
            array('cmf_routing.manager_name'),
            false // TODO: once we have config for orm or phpcr-odm, configure the enabled parameter
        );
    }
}
