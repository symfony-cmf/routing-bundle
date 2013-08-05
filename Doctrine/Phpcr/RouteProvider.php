<?php

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
                    if (preg_match('/.+\.([a-z]+)$/i', $url, $matches)) {
                        if ($route->getDefault('_format') === $matches[1]) {
                            continue;
                        }

                        $route->setDefault('_format', $matches[1]);
                    }
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

    protected function getCandidates($url)
    {
        $candidates = array();
        if ('/' !== $url) {
            if (preg_match('/(.+)\.[a-z]+$/i', $url, $matches)) {
                $candidates[] = $this->idPrefix.$url;
                $url = $matches[1];
            }

            $part = $url;
            while (false !== ($pos = strrpos($part, '/'))) {
                $candidates[] = $this->idPrefix.$part;
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
        $route = $this->getObjectManager()->find($this->className, $name);
        if (!$route) {
            throw new RouteNotFoundException("No route found for path '$name'");
        }

        return $route;
    }

    public function getRoutesByNames($names, $parameters = array())
    {
        return $this->getObjectManager()->findMany($this->className, $names);
    }

}
