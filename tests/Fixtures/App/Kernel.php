<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Fixtures\App;

use Symfony\Cmf\Bundle\ResourceRestBundle\CmfResourceRestBundle;
use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSet('default');

        if ('phpcr' === $this->environment) {
            $this->requireBundleSets([
                'phpcr_odm',
            ]);
        } elseif ('orm' === $this->environment) {
            $this->requireBundleSet('doctrine_orm');
        }

        $this->registerConfiguredBundles();

        if (class_exists(CmfResourceRestBundle::class)) {
            $this->addBundles([
                new \Symfony\Cmf\Bundle\ResourceBundle\CmfResourceBundle(),
                new \Symfony\Cmf\Bundle\ResourceRestBundle\CmfResourceRestBundle(),
            ]);
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->environment.'.php');
    }
}
