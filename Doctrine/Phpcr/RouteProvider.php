<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;

use PHPCR\Query\QueryInterface;
use PHPCR\RepositoryException;

use PHPCR\Query\RowInterface;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Component\Routing\RouteProviderInterface;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\DoctrineProvider;

/**
 * Loads routes from Doctrine PHPCR-ODM.
 *
 * This is <strong>NOT</strong> not a doctrine repository but just the route
 * provider for the NestedMatcher. (you could of course implement this
 * interface in a repository class, if you need that)
 *
 * @author david.buchmann@liip.ch
 */
class RouteProvider extends DoctrineProvider implements RouteProviderInterface
{
    /**
     * @var CandidatesInterface
     */
    private $candidatesStrategy;

    public function __construct(ManagerRegistry $managerRegistry, CandidatesInterface $candidatesStrategy, $className = null)
    {
        parent::__construct($managerRegistry, $className);
        $this->candidatesStrategy = $candidatesStrategy;
    }

    /**
     * {@inheritDoc}
     *
     * This will return any document found at the url or up the path to the
     * prefix. If any of the documents does not extend the symfony Route
     * object, it is filtered out. In the extreme case this can also lead to an
     * empty list being returned.
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $candidates = $this->candidatesStrategy->getCandidates($request);

        $collection = new RouteCollection();

        if (empty($candidates)) {
            return $collection;
        }

        try {
            /** @var $dm DocumentManager */
            $dm = $this->getObjectManager();
            $routes = $dm->findMany($this->className, $candidates);
            // filter for valid route objects
            foreach ($routes as $key => $route) {
                if ($route instanceof SymfonyRoute) {
                    $collection->add($key, $route);
                }
            }
        } catch (RepositoryException $e) {
            // TODO: how to determine whether this is a relevant exception or not?
            // https://github.com/symfony-cmf/RoutingBundle/issues/143
            // for example, getting /my//test (note the double /) is just an invalid path
            // and means another router might handle this.
            // but if the PHPCR backend is down for example, we want to alert the user
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name)
    {
        if (!$this->candidatesStrategy->isCandidate($name)) {
            throw new RouteNotFoundException(sprintf('Path "%s" is not handled by this route provider', $name));
        }

        $route = $this->getObjectManager()->find($this->className, $name);

        if (empty($route)) {
            throw new RouteNotFoundException(sprintf('No route found for path "%s"', $name));
        }

        if (!$route instanceof SymfonyRoute) {
            throw new RouteNotFoundException(sprintf('Document at path "%s" is no route', $name));
        }

        return $route;
    }

    /**
     * Get all the routes in the repository that are under one of the
     * configured prefixes. This respects the limit.
     *
     * @return array
     */
    private function getAllRoutes()
    {
        if (0 === $this->routeCollectionLimit) {
            return array();
        }

        /** @var $dm DocumentManager */
        $dm = $this->getObjectManager();
        $sql2 = 'SELECT * FROM [nt:unstructured] WHERE [phpcr:classparents] = '.$dm->quote('Symfony\Component\Routing\Route');

        $candidateConstraint = $this->candidatesStrategy->getQueryRestriction();
        if ($candidateConstraint) {
            $sql2 .= ' AND (' . $candidateConstraint . ')';
        }

        $query = $dm->createPhpcrQuery($sql2, QueryInterface::JCR_SQL2);
        if (null !== $this->routeCollectionLimit) {
            $query->setLimit($this->routeCollectionLimit);
        }

        return $dm->getDocumentsByPhpcrQuery($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names = null)
    {
        if (null === $names) {
            return $this->getAllRoutes();
        }

        $candidates = array();
        foreach ($names as $key => $name) {
            if ($this->candidatesStrategy->isCandidate($name)) {
                $candidates[$key] = $name;
            }
        }

        if (!$candidates) {
            return array();
        }

        /** @var $dm DocumentManager */
        $dm = $this->getObjectManager();
        $collection = $dm->findMany($this->className, $candidates);
        foreach ($collection as $key => $document) {
            if (!$document instanceof SymfonyRoute) {
                // we follow the logic of DocumentManager::findMany and do not throw an exception
                unset($collection[$key]);
            }
        }

        return $collection;
    }
}
