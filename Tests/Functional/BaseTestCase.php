<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as ComponentBaseTestCase;
use PHPCR\Util\PathHelper;

class BaseTestCase extends ComponentBaseTestCase
{
    public function getDm()
    {
        $dm = $this->db('PHPCR')->getOm();
        return $dm;
    }

    public function createRoute($path)
    {
        $parentPath = PathHelper::getParentPath($path);
        $parent = $this->getDm()->find(null, $parentPath);
        $name = PathHelper::getNodeName($path);
        $route = new Route;
        $route->setPosition($parent, $name);
        $this->getDm()->persist($route);
        $this->getDm()->flush();

        return $route;
    }
}
