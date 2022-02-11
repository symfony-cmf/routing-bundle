<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\DoctrineProvider;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provider loading routes from Doctrine.
 *
 * This is <strong>NOT</strong> not a doctrine repository but just the route
 * provider for the NestedMatcher. (you could of course implement this
 * interface in a repository class, if you need that)
 *
 * @author david.buchmann@liip.ch
 */
class RouteProvider extends DoctrineProvider implements RouteProviderInterface
{
    private CandidatesInterface $candidatesStrategy;

    public function __construct(ManagerRegistry $managerRegistry, CandidatesInterface $candidatesStrategy, $className)
    {
        parent::__construct($managerRegistry, $className);
        $this->candidatesStrategy = $candidatesStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request): RouteCollection
    {
        $collection = new RouteCollection();

        $candidates = $this->candidatesStrategy->getCandidates($request);
        if (0 === \count($candidates)) {
            return $collection;
        }
        $routes = $this->getRouteRepository()->findByStaticPrefix($candidates, ['position' => 'ASC']);
        /** @var $route Route */
        foreach ($routes as $route) {
            $collection->add($route->getName(), $route);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name): SymfonyRoute
    {
        if (!$this->candidatesStrategy->isCandidate($name)) {
            throw new RouteNotFoundException(sprintf('Route "%s" is not handled by this route provider', $name));
        }

        $route = $this->getRouteRepository()->findOneBy(['name' => $name]);
        if (!$route) {
            throw new RouteNotFoundException("No route found for name '$name'");
        }

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names = null): array
    {
        if (null === $names) {
            if (0 === $this->routeCollectionLimit) {
                return [];
            }

            try {
                return $this->getRouteRepository()->findBy([], null, $this->routeCollectionLimit ?: null);
            } catch (TableNotFoundException $e) {
                return [];
            }
        }

        $routes = [];
        foreach ($names as $name) {
            // TODO: if we do findByName with multivalue, we need to filter with isCandidate afterwards
            try {
                $routes[] = $this->getRouteByName($name);
            } catch (RouteNotFoundException $e) {
                // not found
            }
        }

        return $routes;
    }

    protected function getRouteRepository(): ObjectRepository
    {
        return $this->getObjectManager()->getRepository($this->className);
    }
}
