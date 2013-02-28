<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Routing;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\PHPCR\DocumentManager;
use Metadata\MetadataFactory;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Util\SlugifierInterface;


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
    protected $dm;
    protected $metadataFactory;
    protected $defaultPath;
    protected $slugifier;

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
        $this->slugifier = $slugifier;
        $this->defaultPath = $defaultPath;
    }

    /**
     * Create or update the automatically generated route for
     * the given document.
     *
     * When this is finished it will support multiple locales.
     *
     * @param object Mapped document for which to generate the AutoRoute
     *
     * @return AutoRoute
     */
    public function updateAutoRouteForDocument($document)
    {
        $metadata = $this->getMetadata($document);
        $autoRoute = $this->getAutoRouteForDocument($document);

        $autoRouteName = $this->getRouteName($document);
        $autoRoute->setName($autoRouteName);

        $autoRouteParent = $this->getParentRoute($document);
        $autoRoute->setParent($autoRouteParent);

        $this->dm->persist($autoRoute);

        return $autoRoute;
    }

    /**
     * Remove all auto routes associated with the given document.
     *
     * @param object $document Mapped document
     *
     * @todo: Test me
     *
     * @return array Array of removed routes
     */
    public function removeAutoRoutesForDocument($document)
    {
        $autoRoutes = $this->fetchAutoRoutesForDocument($document);
        foreach ($autoRoutes as $autoRoute) {
            $this->dm->remove($autoRoute);
        }

        return $autoRoutes;
    }

    /**
     * Return true if the given document is mapped with AutoRoute
     *
     * @param object $document Document
     *
     * @return boolean
     */
    public function isAutoRouteable($document)
    {
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($document));

        return $metadata->autoRouteable == 1 ? true : false;
    }

    /**
     * Generate a route name based on the designated route name method in
     * the given mapped document.
     *
     * Here we use the slugifier service given to this class to normalize
     * the title.
     *
     * @param object Mapped document
     *
     * @return string
     */
    protected function getRouteName($document)
    {
        $metadata = $this->getMetadata($document);

        // @NOTE: I have replaced @sjopets [title]-[category] method by assigning
        //        an annotation to the method which provides the route name, but
        //        I wonder if this is the best way.
        //
        // @TODO: This should not be invalid because we must validate by the factory (hint)
        //
        $routeNameMethod = $metadata->getRouteNameMethod();
        $routeName = $document->$routeNameMethod();

        // @TODO: Make slugifier customizable somehow, e.g. @RouteName(transorms=[slugify])
        $routeName = $this->slugifier->slugify($routeName);

        if ($metadata->resolvePathConflicts) {
            // @TODO: Resolve path conflicts by generating new variations until a non
            //        conclicting path is found.
            throw new \Exception('@TODO: Resolve path conflicts');
        }

        return $routeName;
    }

    /**
     * Return the parent route for the generated AutoRoute.
     *
     * Currently we check to see if a base route path has been specified
     * in the given mapped document, if not we fall back to the global default.
     *
     * @TODO: Enable dynamic parents (e.g. name-of-my-blog/my-post)
     *
     * @param object Get parent route of this mapped document.
     *
     * @return Route
     */
    protected function getParentRoute($document)
    {
        $metadata = $this->getMetadata($document);
        $defaultPath = $metadata->basePath ? : $this->defaultPath;
        $parent = $this->dm->find(null, $defaultPath);

        if (!$parent) {
            throw new \Exception(sprintf(
                'Could not find default route parent at path "%s"',
                $defaultPath
            ));
        }

        return $parent;
    }

    /**
     * Convenience method for retrieving Metadata.
     */
    protected function getMetadata($document)
    {
        $metadata = $this->metadataFactory->getMetadataForClass(get_class($document));

        if (null === $metadata) {
            throw new \Exception(
                'Route does not have associated RoutingExtraBundle mapping, will cannot '.
                'create a route without this metadata!'
            );
        }

        return $metadata;
    }

    /**
     * Return the existing or a new AutoRoute for the given document.
     *
     * @throws \Exception If we have more than one
     *
     * @param object $document Mapped document that needs an AutoRoute
     *
     * @return AutoRoute
     */
    protected function getAutoRouteForDocument($document)
    {
        $autoRoutes = array();

        if ($this->isDocumentPersisted($document)) {
            $autoRoutes = $this->fetchAutoRoutesForDocument($document);
        }

        // @TODO: get locale from ODM
        $locale = null; 

        if ($locale) {
            // filter non-matching locales, note that we could do this with the QueryBuilder
            // but currently searching array values is not supported by jackalope-doctrine-dbal.
            array_filter($res, function ($route) use ($locale) {
                if ($route->getDefault('_locale') != $locale) {
                    return false;
                }

                return true;
            });
        }

        if (count($autoRoutes) > 1) {
            throw new \Exception(sprintf(
                'Found more than one AutoRoute for document "%s"',
                ClassUtils::toString($document)
            ));
        } elseif (count($autoRoutes) == 1) {
            $autoRoute = $autoRoutes->first();
        } else {
            $autoRoute = new AutoRoute;
            $autoRoute->setRouteContent($document);
        }

        return $autoRoute;
    }

    /**
     * Fetch all the automatic routes for the given document
     *
     * @param object $document Mapped document
     *
     * @return array
     */
    public function fetchAutoRoutesForDocument($document)
    {
        $routes = $this->dm->getReferrers($document, null, 'routeContent');
        $routes->filter(function ($route) {
            if ($route instanceof AutoRoute) {
                return true;
            }

            return false;
        });

        return $routes;
    }

    protected function isDocumentPersisted($document)
    {
        $metadata = $this->dm->getClassMetadata(get_class($document));
        $id = $metadata->getIdentifierValue($document);
        $isExisting = $this->dm->getPhpcrSession()->nodeExists($id);
        return $isExisting;
        return $res;
    }
}
