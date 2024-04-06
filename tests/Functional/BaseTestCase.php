<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use PHPCR\Util\PathHelper;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Fixtures\App\Document\Content;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase as ComponentBaseTestCase;

class BaseTestCase extends ComponentBaseTestCase
{
    protected function getDm(): DocumentManagerInterface
    {
        return $this->db('PHPCR')->getOm();
    }

    protected function createRoute(string $path): Route
    {
        $parentPath = PathHelper::getParentPath($path);
        $parent = $this->getDm()->find(null, $parentPath);
        $name = PathHelper::getNodeName($path);
        $route = new Route();
        $route->setPosition($parent, $name);
        $this->getDm()->persist($route);
        $this->getDm()->flush();

        return $route;
    }

    protected function createContent(string $path = '/test/content'): Content
    {
        $content = new Content();
        $content->setId($path);
        $content->setTitle('Foo Content');
        $this->getDm()->persist($content);
        $this->getDm()->flush();

        return $content;
    }
}
