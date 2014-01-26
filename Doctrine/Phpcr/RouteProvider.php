<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\DocumentManager;

use PHPCR\Query\QueryInterface;
use PHPCR\RepositoryException;

use PHPCR\Query\RowInterface;

use PHPCR\Util\UUIDHelper;
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
     * Places in the PHPCR tree where routes are located.
     *
     * @var array
     */
    protected $idPrefixes = array();

    /**
     * @param $prefix
     */
    public function setPrefixes($prefixes)
    {
        $this->idPrefixes = $prefixes;
    }

    /**
     * Append a repository prefix to the possible prefixes.
     *
     * @param $prefix
     */
    public function addPrefix($prefix)
    {
        $this->idPrefixes[] = $prefix;
    }

    /**
     * Get all currently configured prefixes where to look for routes.
     *
     * @return array
     */
    public function getPrefixes()
    {
        return $this->idPrefixes;
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
        $url = $request->getPathInfo();
        $candidates = $this->getCandidates($url);

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
     * Get id candidates for all configured prefixes.
     *
     * @param string $url
     *
     * @return array PHPCR ids that could load routes that match $url.
     */
    protected function getCandidates($url)
    {
        $candidates = array();
        foreach ($this->getPrefixes() as $prefix) {
            $candidates = array_merge($candidates, $this->getCandidatesFor($prefix, $url));
        }

        return $candidates;
    }

    /**
     * Get the id candidates for one prefix.
     *
     * @param string $url
     *
     * @return array PHPCR ids that could load routes that match $url and are
     *      child of $prefix.
     */
    protected function getCandidatesFor($prefix, $url)
    {
        $candidates = array();
        if ('/' !== $url) {
            if (preg_match('/(.+)\.[a-z]+$/i', $url, $matches)) {
                $candidates[] = $prefix . $url;
                $url = $matches[1];
            }

            $part = $url;
            while (false !== ($pos = strrpos($part, '/'))) {
                $candidates[] = $prefix . $part;
                $part = substr($url, 0, $pos);
            }
        }

        $candidates[] = $prefix;

        return $candidates;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name The absolute path or uuid of the Route document.
     */
    public function getRouteByName($name)
    {
        if (UUIDHelper::isUUID($name)) {
            $route = $this->getObjectManager()->find($this->className, $name);
            if ($route
                && '' !== $this->idPrefix
                && 0 !== strpos($this->getObjectManager()->getUnitOfWork()->getDocumentId($route), $this->idPrefix)
            ) {
                $route = null;
            }
        } else {
            $route = $this->getObjectManager()->find($this->className, $name);

            foreach ($this->getPrefixes() as $prefix) {
                // $name is the route document path
                if ('' === $prefix || 0 === strpos($name, $prefix)) {
                    $route = $this->getObjectManager()->find($this->className, $name);
                    if ($route) {
                        break;
                    }
                }
            }
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
            return array();
        }

        /** @var $dm DocumentManager */
        $dm = $this->getObjectManager();
        $sql2 = 'SELECT * FROM [nt:unstructured] WHERE [phpcr:classparents] = '.$dm->quote('Symfony\Component\Routing\Route');

        $prefixConstraints = array();
        foreach ($this->getPrefixes() as $prefix) {
            if ('' == $prefix) {
                $prefixConstraints = array();
                break;
            }
            $prefixConstraints[] = 'ISDESCENDANTNODE('.$dm->quote($prefix).')';
        }
        if ($prefixConstraints) {
            $sql2 .= ' AND (' . implode(' OR ', $prefixConstraints) . ')';
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
            if (UUIDHelper::isUUID($name)) {
                continue;
            }
            foreach ($this->getPrefixes() as $prefix) {
                if ('' == $prefix) {
                    $candidates = $names;
                    break 2;
                }
                foreach ($names as $key => $name) {
                    if (0 === strpos($name, $prefix)) {
                        $candidates[$key] = $name;
                    }
                }
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
