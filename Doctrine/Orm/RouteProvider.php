<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\DoctrineProvider;

/**
 * Provider loading routes from Doctrine
 *
 * This is <strong>NOT</strong> not a doctrine repository but just the route
 * provider for the NestedMatcher. (you could of course implement this
 * interface in a repository class, if you need that)
 *
 * @author david.buchmann@liip.ch
 */
class RouteProvider extends DoctrineProvider implements RouteProviderInterface
{
    protected function getCandidates($url)
    {
        $candidates = array();
        if ('/' !== $url) {
            if (preg_match('/(.+)\.[a-z]+$/i', $url, $matches)) {
                $candidates[] = $url;
                $url = $matches[1];
            }

            $part = $url;
            while (false !== ($pos = strrpos($part, '/'))) {
                $candidates[] = $part;
                $part = substr($url, 0, $pos);
            }
        }

        $candidates[] = '/';

        return $candidates;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name, $parameters = array())
    {
        $route = $this->getRoutesRepository()->findOneByName($name);
        if (!$route) {
            throw new RouteNotFoundException("No route found for name '$name'");
        }

        return $route;
    }

    public function getRoutesByNames($names, $parameters = array())
    {
    }

    public function getRouteCollectionForRequest(Request $request)
    {
        $url = $request->getPathInfo();

        $candidates = $this->getCandidates($url);

        $collection = new RouteCollection();

        if (empty($candidates)) {
            return $collection;
        }

        try {
            $routes = $this->getRoutesRepository()->findByStaticPrefix($candidates, array('position' => 'ASC'));

            foreach ($routes as $key => $route) {
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
        } catch (RepositoryException $e) {
            // TODO: how to determine whether this is a relevant exception or not?
            // for example, getting /my//test (note the double /) is just an invalid path
            // and means another router might handle this.
            // but if the PHPCR backend is down for example, we want to alert the user
        }

        return $collection;
    }

    protected function getRoutesRepository()
    {
        return $this->getObjectManager()->getRepository($this->className);
    }
}
