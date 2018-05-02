<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ODM\PHPCR\DocumentManager;
use PHPCR\RepositoryException;
use PHPCR\Util\UUIDHelper;
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\DoctrineProvider;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * Loads routes from Doctrine PHPCR-ODM.
 *
 * This is <strong>NOT</strong> a doctrine repository but just the route
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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ManagerRegistry $managerRegistry,
        CandidatesInterface $candidatesStrategy,
        $className = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($managerRegistry, $className);
        $this->candidatesStrategy = $candidatesStrategy;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     *
     * @return array a list of PHPCR-ODM ids
     */
    public function getCandidates(Request $request)
    {
        if (false !== strpos($request->getPathInfo(), ':')) {
            return [];
        }

        return $this->candidatesStrategy->getCandidates($request);
    }

    /**
     * {@inheritdoc}
     *
     * This will return any document found at the url or up the path to the
     * prefix. If any of the documents does not extend the symfony Route
     * object, it is filtered out. In the extreme case this can also lead to an
     * empty list being returned.
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $candidates = $this->getCandidates($request);

        $collection = new RouteCollection();

        if (0 === count($candidates)) {
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
            if ($this->logger) {
                $this->logger->critical($e);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name The absolute path or uuid of the Route document
     */
    public function getRouteByName($name)
    {
        if (UUIDHelper::isUUID($name)) {
            $route = $this->getObjectManager()->find($this->className, $name);
            if ($route
                && !$this->candidatesStrategy->isCandidate($this->getObjectManager()->getUnitOfWork()->getDocumentId($route))
            ) {
                throw new RouteNotFoundException(
                    sprintf(
                        'Route with uuid "%s" and id "%s" is not handled by this route provider',
                        $name,
                        $this->getObjectManager()->getUnitOfWork()->getDocumentId($route)
                    )
                );
            }
        } elseif (!$this->candidatesStrategy->isCandidate($name)) {
            throw new RouteNotFoundException(sprintf('Route name "%s" is not handled by this route provider', $name));
        } else {
            $route = $this->getObjectManager()->find($this->className, $name);
        }

        if (empty($route)) {
            throw new RouteNotFoundException(sprintf('No route found at "%s"', $name));
        }

        if (!$route instanceof SymfonyRoute) {
            throw new RouteNotFoundException(sprintf('Document at "%s" is no route', $name));
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
            return [];
        }

        try {
            /** @var $dm DocumentManager */
            $dm = $this->getObjectManager();
        } catch (RepositoryException $e) {
            // special case: there is not even a database existing. this means there are no routes.
            if ($e->getPrevious() instanceof TableNotFoundException) {
                return [];
            }

            throw $e;
        }
        $qb = $dm->createQueryBuilder();

        $qb->from('d')->document(SymfonyRoute::class, 'd');

        $this->candidatesStrategy->restrictQuery($qb);

        $query = $qb->getQuery();
        if ($this->routeCollectionLimit) {
            $query->setMaxResults($this->routeCollectionLimit);
        }

        return $query->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names = null)
    {
        if (null === $names) {
            return $this->getAllRoutes();
        }

        $candidates = [];
        foreach ($names as $key => $name) {
            if (UUIDHelper::isUUID($name) || $this->candidatesStrategy->isCandidate($name)) {
                $candidates[$key] = $name;
            }
        }

        if (!$candidates) {
            return [];
        }

        /** @var $dm DocumentManager */
        $dm = $this->getObjectManager();
        $documents = $dm->findMany($this->className, $candidates);
        foreach ($documents as $key => $document) {
            if (UUIDHelper::isUUID($key)
                && !$this->candidatesStrategy->isCandidate($this->getObjectManager()->getUnitOfWork()->getDocumentId($document))
            ) {
                // this uuid pointed out of our path. can only determine after fetching the document
                unset($documents[$key]);
            }
            if (!$document instanceof SymfonyRoute) {
                // we follow the logic of DocumentManager::findMany and do not throw an exception
                unset($documents[$key]);
            }
        }

        return $documents;
    }
}
