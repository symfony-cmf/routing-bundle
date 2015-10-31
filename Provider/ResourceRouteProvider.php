<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Provider;

use Puli\Discovery\Api\Discovery;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Cmf\Component\Resource\Repository\Resource\CmfResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ResourceRouteProvider implements RouteProviderInterface
{
    /**
     * @var SymfonyRoute[]
     */
    private $routesByUri;

    /**
     * @var SymfonyRoute[]
     */
    private $routesByPath = array();

    /**
     * @var Discovery
     */
    private $discovery;

    /**
     * @var CandidatesInterface
     */
    private $candidatesStrategy;

    public function __construct(Discovery $discovery, array $routesByUri, CandidatesInterface $candidatesStrategy)
    {
        $this->discovery = $discovery;
        $this->routesByUri = $routesByUri;
        $this->candidatesStrategy = $candidatesStrategy;
    }

    public function getRouteCollectionForRequest(Request $request)
    {
        $this->discoverRoutes();

        $candidates = $this->getCandidates($request);
        $collection = new RouteCollection();

        if (empty($candidates)) {
            return $collection;
        }

        foreach (array_intersect(array_keys($this->routesByUri), $candidates) as $key => $uri) {
            $collection->add($key, $this->routesByUri[$uri]);
        }

        return $collection;
    }

    public function getRouteByName($name)
    {
        $this->discoverRoutes();

        if (isset($this->routesByPath[$name])) {
            return $this->routesByPath[$name];
        }
    }

    public function getRoutesByNames($names)
    {
        // todo implement this method
    }

    /**
     * @param Request $request
     *
     * @return array a list of PHPCR-ODM ids
     */
    private function getCandidates(Request $request)
    {
        if (false !== strpos($request->getPathInfo(), ':')) {
            return array();
        }

        return $this->candidatesStrategy->getCandidates($request);
    }

    private function discoverRoutes()
    {
        if (0 !== count($this->routesByUri)) {
            return;
        }

        foreach ($this->discovery->findBindings('cmf/routes') as $binding) {
            foreach ($binding->getResources() as $resource) {
                if (!$resource instanceof CmfResource) {
                    continue;
                }

                $route = $resource->getPayload();
                if (!$route instanceof SymfonyRoute) {
                    continue;
                }

                $this->routesByUri[$route->getStaticPrefix()] = $route;
                $this->routesByPath[$resource->getPath()] = $route;
            }
        }
    }
}
