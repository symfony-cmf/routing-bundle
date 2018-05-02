<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$container->setParameter('cmf_testing.bundle_fqn', 'Symfony\Cmf\Bundle\RoutingBundle');
$loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
$loader->import(CMF_TEST_CONFIG_DIR.'/doctrine_orm.php');
$loader->import(__DIR__.'/config.yml');
$loader->import(__DIR__.'/cmf_routing.yml');
$loader->import(__DIR__.'/cmf_routing.orm.yml');
