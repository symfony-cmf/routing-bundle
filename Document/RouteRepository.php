<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Document;

use Doctrine\Common\Persistence\ObjectManager;

use PHPCR\RepositoryException;

use Symfony\Component\Routing\Route as SymfonyRoute;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Cmf\Component\Routing\RouteRepositoryInterface;

/**
 * Repository to load routes from PHPCR-ODM
 *
 * This is <strong>NOT</strong> not a doctrine repository but just the proxy
 * for the DynamicRouter implementing RouteRepositoryInterface
 *
 * @author david.buchmann@liip.ch
 */
class RouteRepository implements RouteRepositoryInterface
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

    public function __construct(ObjectManager $dm, $className = null)
    {
        $this->dm = $dm;
        $this->className = $className;
    }

    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
    }

    protected function getCandidates($url)
    {
        $part = $url;
        $candidates = array();
        if ('/' !== $url) {
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
    public function findManyByUrl($url)
    {
        if (! is_string($url) || strlen($url) < 1 || '/' != $url[0]) {
            throw new RouteNotFoundException("$url is not a valid route");
        }

        $candidates = $this->getCandidates($url);

        try {
            $routes = $this->dm->findMany($this->className, $candidates);
            // filter for valid route objects
            // we can not search for a specific class as PHPCR does not know class inheritance
            // TODO: but optionally we could define a node type
            foreach ($routes as $key => $route) {
                if (! $route instanceof SymfonyRoute) {
                    unset($routes[$key]);
                }
            }
        } catch (RepositoryException $e) {
            // TODO: how to determine whether this is a relevant exception or not?
            // for example, getting /my//test (note the double /) is just an invalid path
            // and means another router might handle this.
            // but if the PHPCR backend is down for example, we want to alert the user
            $routes = array();
        }

        return $routes;
    }
}
