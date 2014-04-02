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
 * Provide routes loaded from PHPCR-ODM
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
     * The prefix to add to the url to create the repository path
     *
     * @var string
     */
    protected $idPrefix = '';

    /**
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
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
     * @param $url
     *
     * @return array
     */
    protected function getCandidates($url)
    {
        $candidates = array();
        if ('/' !== $url) {
            if (preg_match('/(.+)\.[a-z]+$/i', $url, $matches)) {
                $candidates[] = $this->idPrefix . $url;
                $url = $matches[1];
            }

            $part = $url;
            while (false !== ($pos = strrpos($part, '/'))) {
                $candidates[] = $this->idPrefix . $part;
                $part = substr($url, 0, $pos);
            }
        }

        $candidates[] = $this->idPrefix;

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
        } elseif ('' === $this->idPrefix || 0 === strpos($name, $this->idPrefix)) {
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
     * Get list of route names
     *
     * @return array
     */
    private function getRouteNames()
    {
        if (0 === $this->routeCollectionLimit) {
            return array();
        }

        /** @var $dm DocumentManager */
        $dm = $this->getObjectManager();
        $sql2 = 'SELECT * FROM [nt:unstructured] WHERE [phpcr:classparents] = '.$dm->quote('Symfony\Component\Routing\Route');

        if ('' !== $this->idPrefix) {
            $sql2.= ' AND ISDESCENDANTNODE('.$dm->quote($this->idPrefix).')';
        }

        $query = $dm->createPhpcrQuery($sql2, QueryInterface::JCR_SQL2);
        if (null !== $this->routeCollectionLimit) {
            $query->setLimit($this->routeCollectionLimit);
        }

        $result = $query->execute();

        $names = array();
        foreach ($result as $row) {
            /** @var $row RowInterface */
            $names[] = $row->getPath();
        }

        return $names;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names = null)
    {
        if (null === $names) {
            $names = $this->getRouteNames();
        }

        if ('' !== $this->idPrefix) {
            foreach ($names as $key => $name) {
                if (!UUIDHelper::isUUID($name) && 0 !== strpos($name, $this->idPrefix)) {
                    unset($names[$key]);
                }
            }
        }

        /** @var $dm DocumentManager */
        $dm = $this->getObjectManager();
        $collection = $dm->findMany($this->className, $names);
        foreach ($collection as $key => $document) {
            if (!$document instanceof SymfonyRoute) {
                // we follow the logic of DocumentManager::findMany and do not throw an exception
                unset($collection[$key]);
            }
        }

        return $collection;
    }
}
