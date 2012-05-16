<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Cmf\Component\Routing\DoctrineRouter as BaseDoctrineRouter;

/**
 * A router that reads route entries from an Object-Document Mapper store.
 *
 * This is basically using the symfony routing matcher and generator. Different
 * to the default router, the route collection is loaded from the injected
 * route repository custom per request to not load a potentially large number
 * of routes that are known to not match anyways.
 *
 * If the route provides a content, that content is placed in the request
 * object with the CONTENT_KEY for the controller to use.
 *
 * @author Philippo de Santis
 * @author David Buchmann
 */
class DoctrineRouter extends BaseDoctrineRouter implements ContainerAwareInterface
{
    /**
     * key for the request attribute that contains the content document if this
     * route has one associated
     */
    const CONTENT_KEY = 'contentDocument';

    /**
     * key for the request attribute that contains the template this document
     * wants to use
     */
    const CONTENT_TEMPLATE = 'contentTemplate';

    /**
     * To get the request from, as its not available immediatly
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     *
     * Put content into the request attributes instead of the defaults
     */
    public function match($url)
    {
        $defaults = parent::match($url);

        if (isset($defaults['_content'])) {
            if (! $request = $this->container->get('request')) {
                throw new \Exception('Request object not available from container');
            }

            $request->attributes->set(self::CONTENT_KEY, $defaults['_content']);
            unset($defaults['_content']);
        }

        return $defaults;
    }

    protected function getLocale($parameters)
    {
        $locale = parent::getLocale($parameters);
        if ($locale) {
            return $locale;
        }

        if (is_null($this->container) || ! $request = $this->container->get('request')) {
            throw new \RuntimeException('Request object not available from container');
        }

        return $request->getLocale();
    }
}
