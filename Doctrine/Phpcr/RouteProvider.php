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

use PHPCR\RepositoryException;

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
            $routes = $this->getObjectManager()->findMany($this->className, $candidates);
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
     */
    public function getRouteByName($name, $parameters = array())
    {
        // $name is the route document path
        if ('' === $this->idPrefix || 0 === strpos($name, $this->idPrefix)) {
            $route = $this->getObjectManager()->find($this->className, $name);
        }

        if (empty($route)) {
            throw new RouteNotFoundException(sprintf('No route found for path "%s"', $name));
        }

        if (!$route instanceof SymfonyRoute) {
            throw new RouteNotFoundException(sprintf('Document at path "%s" is no route', $name));
        }

        return $route;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names, $parameters = array())
    {
        if ('' !== $this->idPrefix) {
            foreach ($names as $key => $name) {
                if (0 !== strpos($name, $this->idPrefix)) {
                    unset($names[$key]);
                }
            }
        }

        $collection = $this->getObjectManager()->findMany($this->className, $names);
        foreach ($collection as $key => $document) {
            if (!$document instanceof SymfonyRoute) {
                // we follow the logic of DocumentManager::findMany and do not throw an exception
                unset($collection[$key]);
            }
        }

        return $collection;
    }

}
