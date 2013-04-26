<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Cmf\Component\Routing\ContentAwareGenerator as BaseGenerator;

/**
 * Symfony framework integration of the CMF routing component ContentAwareGenerator class
 *
 * @author David Buchmann
 */
class ContentAwareGenerator extends BaseGenerator implements ContainerAwareInterface
{
    /**
     * To get the request from, as its not available immediately
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $defaultLocale = null;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Overwrite the locale to be used by default if there is neither one in
     * the parameters when building the route nor a request available (i.e. CLI).
     *
     * @param string $locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * Get the locale, either from the parent or from the container if available
     * or the default locale if one is set.
     *
     * @param array $parameters the request parameters
     *
     * @return string
     */
    protected function getLocale($parameters)
    {
        $locale = parent::getLocale($parameters);
        if ($locale) {
            return $locale;
        }

        if (is_null($this->container)
            || ! $this->container->isScopeActive('request')
            || ! $request = $this->container->get('request')
        ) {
            return $this->defaultLocale;
        }

        return $request->getLocale();
    }
}
