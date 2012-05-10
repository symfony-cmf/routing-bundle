<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Document;

use Doctrine\ODM\PHPCR\DocumentRepository;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;

use PHPCR\RepositoryException;

use Symfony\Component\Routing\Route as SymfonyRoute;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Cmf\Component\Routing\RouteRepositoryInterface;

/**
 * Repository to load routes from phpcr-odm
 *
 * @author david.buchmann@liip.ch
 */
class RouteRepository extends DocumentRepository implements RouteRepositoryInterface
{
    /**
     * The prefix to add to the url to create the repository path
     *
     * @var string
     */
    protected $idPrefix = '';

    public function __construct($dm, ClassMetadata $class)
    {
        parent::__construct($dm, $class);
        // let dm determine class so this repository works for all types of routes
        $this->className = null;
    }

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
    public function findManyByUrl($url)
    {
        if (! is_string($url) || strlen($url) < 1 || '/' != $url[0]) {
            throw new RouteNotFoundException("$url is not a valid route");
        }

        $part = $url;
        $candidates = array();
        while (false !== ($pos = strrpos($part, '/'))) {
            $candidates[] = $this->idPrefix . $part;
            $part = substr($url, 0, $pos);
        }
        $candidates[] = $this->idPrefix;

        try {
            $routes = $this->findMany($candidates);
            // filter for valid route objects
            // we can not search for a specific class as PHPCR does not know class inheritance
            // TOD: but optionally we could define a node type
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
