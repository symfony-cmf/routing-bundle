<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\RedirectRoute;
use Symfony\Cmf\Component\Routing\RouteAwareInterface;


/**
 * NOTE: I have tried to decouple this from the Subscriber, so you have the
 *       option of manually calling $service->updateRoutesForDocument($obj)
 *       if you are worried about batch updates etc.
 *
 * This class is concerned with the automatic creation of route objects.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @author Sjoerd Peters <speters@netvlies.net>
 */
class AutoRouteManager
{
    protected $metadataFactory;
    protected $defaultPath;
    protected $slugifier;
    protected $phpcrSession;

    /**
     * @TODO: Should defaultPath be contained in a service to
     *        enable this property to be modified at runtime?
     * @TODO: Replace Slugifier with TransformerFactory or similar.
     *
     * @param DocumentManager    $dm          PHPCR-ODM Document Manager
     * @param MetadataFactory    $metadata    Metadata factory
     * @param SlugifierInterface $slugifier   Slugifier
     * @param string             $defaultPath Default base path for new routes
     */
    public function __construct(
        DocumentManager $dm,
        MetadataFactory $metadataFactory, 
        SlugifierInterface $slugifier,
        $defaultPath
    )
    {
        $this->dm = $dm;
        $this->metadataFactory = $metadataFactory;
        $this->defaultPath = $defaultPath;
        $this->phpcrSession = $dm->getPhpcrSession();
    }

    /**
     * Automatically create routes for the given mapped document.
     *
     * @NOTE: If parent routes are mapped, we here create a new sub route
     *        for each (e.g. one for each locale). Not 100% sure about that.
     *
     * @param object $document Should be an object with the AutoRoute class annotation
     *
     * @return array
     */
    public function createAutoRoutesForDocument($document)
    {
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($document));

        if (null === $metadata) {
            throw new \Exception(
                'Route does not have associated RoutingExtraBundle mapping, will cannot '.
                'create a route without this metadata!'
            );
        }

        $routeName = $this->getRouteName($document);
        $routePath = $metadata->basePath ? : $this->defaultPath;
        $parentsProperty = $metadata->routeParentsMethod;

        // If the user has not defined a Parents annotation, we use the default
        if (null === $parentsProperty) {
            $defaultParent = $this->dm->find(null, $defaultPath);
            $parents = array($defaultParent);
        } else {
            // @TODO: Route parents: Property or Method? (or even CLASS attribute)
            $parents = $document->$parentsProperty;
        }

        $ret = array();
        foreach ($parents as $parent) {
            $route = new AutoRoute();
            $route->setParent($parent);
            $route->setName($routeName);
            $route->setRouteContent($document);
        }

        return $ret;
    }

    public function updateAutoRoutesForDocument(RouteAwareInterface $document)
    {
        $ret = array();

        $metadata = $this->metadataFactory->getMetadataForClass(get_class($document));

        // TODO: Create a class for this exception
        if (null === $metadata) {
            throw new \Exception(
                'Route does not have associated RoutingExtraBundle mapping, will cannot '.
                'update a route without this metadata!'
            );
        }

        foreach ($document->getRoutes() as $route) {

            // NOTE: I wonder if it isn't better to do it as netvlies have done it
            //       by associating the Permalink, AutoRoute and DefaultRoute with a
            //       property.
            if ($route instanceOf AutoRoute) {

                if($metadata->updateRouteName){
                    $routeName = $this->getRouteName($metadata, $document);
                    $route->setName($routeName);
                    $ret[]= $route;
                }


            }
        }

        $autoRoute = $document->getAutoRoute();
        $basePath = dirname($autoRoute->getPath());
        $name = basename($autoRoute->getPath());

        if($document->getDefaultRoute() === $document->getAutoRoute()){
            $routeRoot = $this->getRoutingRoot();
        }
        else{
            $routeRoot = $this->getRedirectRoot();
        }

        if($metadata->updateBasePath){
            $basePath = $this->parsePath($routeRoot.'/'.$metadata->basePath, $document);
        }

        return $basePath.'/'.$name;
    }

    /**
     * Creates a path  to guarantee an unique entry point (permalink)
     * used to avoid conflicts when inserting a new PHPCR node.
     * @todo move this into oms bundle
     *
     * @param string path
     * @return string
     */
    public function getUniquePath($path)
    {
        $number = 1;
        $newPath = $path;

        while ($this->phpcrSession->nodeExists($newPath)) {
            $newPath = $path . '-' . $number++;
        }

        return $newPath;
    }

    protected function getRouteName(ClassMetadata $metadata, $document)
    {
        // @NOTE: I have replaced @sjopets [title]-[category] method by assigning
        //        an annotation to the method which provides the route name, but
        //        I wonder if this is the best way.
        // @TODO: This should not be invalid because we must validate by the factory (hint)
        $routeNameMethod = $metadata->routeNameMethod;
        $routeName = $document->$routeNameMethod();
        // @TODO: Make this customizable somehow, e.g. @RouteName(transorms=[slugify])
        $routeName = $this->slugifier->slugify($routeName);

        return $routeName;
    }
}
