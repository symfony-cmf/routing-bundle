<?php

namespace Symfony\Cmf\Bundle\RoutingBundle;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;
use Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
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
                    array('cmf_routing.dynamic.persistence.phpcr.manager_name'),
                    'cmf_routing.backend_type_phpcr'
                )
            );
        }

        $doctrineOrmCompiler = $this->findDoctrineOrmCompiler();
        if ($doctrineOrmCompiler) {
            $container->addCompilerPass($this->buildBaseOrmCompilerPass($doctrineOrmCompiler));
            $container->addCompilerPass(
                $doctrineOrmCompiler::createXmlMappingDriver(
                    array(
                        realpath(__DIR__ . '/Resources/config/doctrine-model') => 'Symfony\Cmf\Bundle\RoutingBundle\Model',
                        realpath(__DIR__ . '/Resources/config/doctrine-orm') => 'Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm',
                    ),
                    array('cmf_routing.dynamic.persistence.orm.manager_name'),
                    'cmf_routing.backend_type_orm'
                )
            );
        }
    }

    /**
     * Searches a mapping compiler (doctrine bridge compiler is missing in symfony < 2.3).
     * Use Cmf\CoreBundle in that case.
     */
    private function findDoctrineOrmCompiler()
    {
        if ($this->symfonyVersion()) {
            return 'Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
        }

        if (class_exists('Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            return 'Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass';
        }

        return false;
    }

    private function symfonyVersion()
    {
        $symfonyVersion = class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass');
        $doctrineVersion = class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass');

        return ( $symfonyVersion && $doctrineVersion );
    }

    /**
     * Instantiate compiler now because of SymfonyFileLocator namespace issue (see phpcr method comment).
     */
    private function buildBaseOrmCompilerPass($doctrineOrmCompiler)
    {
        $arguments = array(array(realpath(__DIR__ . '/Resources/config/doctrine-base')), '.orm.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator', $arguments);
        $driver = new Definition('Doctrine\ORM\Mapping\Driver\XmlDriver', array($locator));

        /**
         * if we got here a compiler has been found so this test is enough
         */
        if ($this->symfonyVersion()) {
            return new $doctrineOrmCompiler(
                $driver,
                array('Symfony\Component\Routing'),
                array('cmf_routing.dynamic.persistence.orm.manager_name'),
                'cmf_routing.backend_type_orm'
            );
        } else {
            return new $doctrineOrmCompiler(
                $driver,
                array('Symfony\Component\Routing'),
                array('cmf_routing.dynamic.persistence.orm.manager_name'),
                'doctrine.orm.%s_metadata_driver',
                'cmf_routing.backend_type_orm'
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
            array('cmf_routing.dynamic.persistence.phpcr.manager_name'),
            'cmf_routing.backend_type_phpcr'
        );
    }
}
