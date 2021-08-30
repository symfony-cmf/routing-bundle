<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as ComponentBaseTestCase;

class OrmTestCase extends ComponentBaseTestCase
{
    protected static function getKernelConfiguration(): array
    {
        return [
            'environment' => 'orm',
        ];
    }

    protected function clearDb($model)
    {
        if (\is_array($model)) {
            foreach ($model as $singleModel) {
                $this->clearDb($singleModel);
            }
        }

        $items = $this->getDm()->getRepository($model)->findAll();

        foreach ($items as $item) {
            $this->getDm()->remove($item);
        }

        $this->getDm()->flush();
    }

    protected function getDm()
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

        $this->getDm()->persist($route);
        $this->getDm()->flush();

        return $route;
    }
}
