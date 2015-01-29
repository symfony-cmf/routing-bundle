<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Resource;

use Puli\Repository\Api\ResourceRepository;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Puli\Repository\Assert\Assert;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Route provider which uses a CMF resource repository.
 *
 * Routes can potentially be sourced from PHPCR, PHPCR-ODM, ORM, standard
 * routing system, etc.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class ResourceProvider implements RouteProviderInterface
{
    /**
     * @var ResourceRepository
     */
    private $resourceRepository;

    /**
     * @param ResourceRepository $resourceRepository
     */
    public function __construct(ResourceRepository $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $path = $request->getPathInfo();

        // assert absolutness and pathliness of path
        Assert::path($path);

        $collection = new RouteCollection();

        foreach ($this->getPaths($path) as $targetPath) {
            $this->findRoutes($targetPath, $collection);
        }

        return $collection;
    }

    /**
     * Return a list of candidate glob paths in order
     * of longest to shortest urls, example:
     *
     *   array(
     *      '/test/foo/*',
     *      '/test/*',
     *      '/*',
     *      '/',
     *   );
     *
     * @param string
     *
     * @return array
     */
    private function getPaths($path)
    {
        // get the URL parts (excluding the first /)
        $parts = explode('/', substr($path, 1));
        array_pop($parts);

        $paths = array('/', '/*');
        $pathStack = array();

        foreach ($parts as $i => $part) {
            $pathStack[] = $part;
            $paths[] = '/'.implode('/', $pathStack).'/*';
        }

        $paths = array_reverse($paths);

        return $paths;
    }

    /**
     * Find routes for the given glob path and
     * add to the RouteCollection
     *
     * @param string          $path
     * @param RouteCollection $collection
     */
    private function findRoutes($path, $collection)
    {
        $resources = $this->resourceRepository->find($path);

        foreach ($resources as $resource) {
            $route = $resource->getPayload();

            if (!$route instanceof Route) {
                continue;
            }

            $collection->add($resource->getPath(), $route);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name)
    {
        throw new \InvalidArgumentException('Not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names)
    {
        throw new \InvalidArgumentException('Not supported');
    }
}
