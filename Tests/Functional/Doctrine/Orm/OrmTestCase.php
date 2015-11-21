<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Orm;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as ComponentBaseTestCase;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;

class OrmTestCase extends ComponentBaseTestCase
{
    protected function getKernelConfiguration()
    {
        return array(
            'environment' => 'orm',
        );
    }

    /**
     * @param Route[] $routes
     */
    protected function removeRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->getEm()->remove($route);
        }

        $this->getEm()->flush();
        $this->getEm()->clear();
    }

    protected function getEm()
    {
        return $this->db('ORM')->getOm();
    }

    protected function createRoute($name, $path)
    {
        // split path in static and variable part
        preg_match('{^(.*?)(/[^/]*\{.*)?$}', $path, $paths);

        $route = new Route();
        $route->setName($name);
        $route->setStaticPrefix($paths[1]);
        if (isset($paths[2])) {
            $route->setVariablePattern($paths[2]);
        }

        $this->getEm()->persist($route);
        $this->getEm()->flush();

        return $route;
    }
}
