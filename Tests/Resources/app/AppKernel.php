<?php

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSet('default');

        if (in_array($this->environment, array('phpcr', 'resource'))) {
            $this->requireBundleSets(array(
                'phpcr_odm',
                'sonata_admin_phpcr',
            ));
        } elseif ('orm' === $this->environment) {
            $this->requireBundleSet('doctrine_orm');
        }

        $this->addBundles(array(
            new \Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new \Symfony\Cmf\Bundle\ResourceBundle\CmfResourceBundle(),
        ));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->environment.'.php');
    }
}
