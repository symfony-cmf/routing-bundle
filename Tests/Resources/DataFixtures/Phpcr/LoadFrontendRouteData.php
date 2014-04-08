<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\DataFixtures\PHPCR;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use PHPCR\Util\NodeHelper;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Doctrine\ODM\PHPCR\Document\Generic;

class LoadFrontendRouteData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        NodeHelper::createPath($manager->getPhpcrSession(), '/test');
        $root = $manager->find(null, '/test');
        $parent = new Generic;
        $parent->setParent($root);
        $parent->setNodename('routing');
        $manager->persist($parent);

        //create two prefixes as new candidates
        $candidateOne = new Generic;
        $candidateOne->setParent($parent);
        $candidateOne->setNodename('candidate-1');
        $manager->persist($candidateOne);

        $candidateTwo = new Generic;
        $candidateTwo->setParent($parent);
        $candidateTwo->setNodename('candidate-2');
        $manager->persist($candidateTwo);

        $route = new Route;
        $route->setParentDocument($candidateOne);
        $route->setName('route-in-candidate-1');
        $route->setStaticPrefix('/test/routing/candidate-1');
        $manager->persist($route);

        $route = new Route;
        $route->setParentDocument($candidateTwo);
        $route->setStaticPrefix('/test/routing/candidate-2');
        $route->setName('route-in-candidate-2');
        $manager->persist($route);

        $redirectRoute = new RedirectRoute;
        $redirectRoute->setParentDocument($candidateOne);
        $redirectRoute->setName('redirect-route-1');
        $manager->persist($redirectRoute);

        $manager->flush();
    }
}
