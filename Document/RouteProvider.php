<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Document;

use Doctrine\Common\Persistence\ManagerRegistry;

use PHPCR\RepositoryException;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Component\Routing\RouteProviderInterface;

/**
 * Provider loading routes from PHPCR-ODM
 *
 * This is <strong>NOT</strong> not a doctrine repository but just the route
 * provider for the NestedMatcher. (you could of course implement this
 * interface in a repository class, if you need that)
 *
 * @author david.buchmann@liip.ch
 */
class RouteProvider implements RouteProviderInterface
{
    /**
     * @var string Name of object manager to use
     */
    protected $managerName;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * Class name of the route class, null for phpcr-odm as it can determine
     * the class on its own.
     *
     * @var string
     */
    protected $className;

    /**
     * The prefix to add to the url to create the repository path
     *
     * @var string
     */
    protected $idPrefix = '';

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string|null $className
     */
    public function __construct(ManagerRegistry $managerRegistry, $className = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->className = $className;
    }

    /**
     * Set the object manager name to use for this loader;
     * if not called, the default manager will be used.
     *
     * @param string $managerName
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;
    }

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

        $candidates[] = $this->idPrefix;

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

    /**
     * Get the object manager from the registry, based on the current managerName
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->managerRegistry->getManager($this->managerName);
    }
}
