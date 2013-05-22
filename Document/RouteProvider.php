<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Document;

use Doctrine\Common\Persistence\ObjectManager;

use LogicException;

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
     * @var ObjectManager
     */
    protected $dm;

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

    public function __construct($className = null)
    {
        $this->className = $className;
    }

    /**
     * Set the document manager to use for this loader. Must be called before
     * using any of the get methods.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $dm
     */
    public function setDocumentManager(ObjectManager $dm)
    {
        $this->dm = $dm;
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
        $this->checkDocumentManager();

        $url = $request->getPathInfo();

        $candidates = $this->getCandidates($url);

        $collection = new RouteCollection();
        if (empty($candidates)) {
            return $collection;
        }

        try {
            $routes = $this->dm->findMany($this->className, $candidates);
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
        $this->checkDocumentManager();

        // $name is the route document path
        $route = $this->dm->find($this->className, $name);
        if (!$route) {
            throw new RouteNotFoundException("No route found for path '$name'");
        }

        return $route;
    }

    public function getRoutesByNames($names, $parameters = array())
    {
        $this->checkDocumentManager();
        return $this->dm->findMany($this->className, $names);
    }

    /**
     * @throws \LogicException
     */
    private function checkDocumentManager()
    {
        if (!$this->dm instanceof ObjectManager) {
            throw new LogicException('A document manager must be set before using this provider');
        }
    }
}
