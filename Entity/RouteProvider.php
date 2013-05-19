<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Entity;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;

use Symfony\Component\Routing\Exception\RouteNotFoundException;


use Doctrine\ORM\EntityRepository;

/**
 * Repository to load routes from Doctrine ORM
 *
 * This is a doctrine repository
 *
 * @author matteo caberlotto mcaber@gmail.com
 */
class RouteProvider extends EntityRepository implements RouteProviderInterface
{
    /**
     * Symfony routes always need a name in the collection. We generate routes
     * based on the route entity "name" property.
     */
    protected $routeNamePrefix = 'dynamic_route';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Class name of the route class, null for phpcr-odm as it can determine
     * the class on its own.
     *
     * @var string
     */
    protected $className;

    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
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

        if (!empty($this->idPrefix)) {
            $candidates[] = $this->idPrefix;
        }

        return $candidates;
    }

    /**
     * {@inheritDoc}
     *
     * This will return any document found at the url or up the path to the
     * prefix. If any of the documents does not extend the symfony Route
     * object, it is filtered out. In the extreme case this can also lead to an
     * empty list being returned.
     */
    public function findRouteByUrl($url)
    {
        if (! is_string($url) || strlen($url) < 1 || '/' != $url[0]) {
            throw new RouteNotFoundException("$url is not a valid route");
        }

        $candidates = $this->getCandidates($url);

        $collection = new RouteCollection();

        try {
            $routes = $this->findByPath($candidates, array('position' => 'ASC'));

            foreach ($routes as $route) {
                if ($route instanceof SymfonyRoute) {
                    $collection->add($route->getName(), $route);
                }
            }
        } catch (RepositoryException $e) {
            // TODO: how to determine whether this is a relevant exception or not?
            // for example, getting /my//test (note the double /) is just an invalid path
            // and means another router might handle this.
            // but if the PHPCR backend is down for example, we want to alert the user
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name, $parameters = array())
    {
        $route = $this->findOneByName($name);
        if (!$route) {
            throw new RouteNotFoundException("No route found for name '$name'");
        }

        return $route;
    }

    public function getRoutesByNames($names, $parameters = array()) {
        // @TODO: must implement this
    }

    public function getRouteCollectionForRequest(\Symfony\Component\HttpFoundation\Request $request) {

        $url = $request->getPathInfo();

        $candidates = $this->getCandidates($url);

        $collection = new RouteCollection();
        if (empty($candidates)) {
            return $collection;
        }

        try {
            $routes = $this->findByPath($candidates, array('position' => 'ASC'));

            // filter for valid route objects
            // we can not search for a specific class as PHPCR does not know class inheritance
            // but optionally we could define a node type
            foreach ($routes as $key => $route) {
                if ($route instanceof SymfonyRoute) {
                    if (preg_match('/.+\.([a-z]+)$/i', $url, $matches)) {
                        if ($route->getDefault('_format') === $matches[1]) {
                            continue;
                        }

                        $route->setDefault('_format', $matches[1]);
                    }
                    // SYMFONY 2.1 COMPATIBILITY: tweak route name
                    $key = trim(preg_replace('/[^a-z0-9A-Z_.]/', '_', $key), '_');
                    $collection->add($key, $route);
                }
            }
        } catch (RepositoryException $e) {
            // TODO: how to determine whether this is a relevant exception or not?
            // for example, getting /my//test (note the double /) is just an invalid path
            // and means another router might handle this.
            // but if the PHPCR backend is down for example, we want to alert the user
        }

        return $collection;
    }
}
