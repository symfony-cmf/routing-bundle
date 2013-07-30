<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as ComponentBaseTestCase;
use PHPCR\Util\PathHelper;
use Symfony\Cmf\Component\Testing\Document\Content;

class BaseTestCase extends ComponentBaseTestCase
{
    protected function getDm()
    {
        $dm = $this->db('PHPCR')->getOm();
        return $dm;
    }

    protected function createRoute($path)
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

    protected function createContent($path)
    {
        $content = new Content;
        $content->setId('/test/content');
        $content->setTitle('Foo Content');
        $this->getDm()->persist($content);
        $this->getDm()->flush();

        return $content;
    }
}
