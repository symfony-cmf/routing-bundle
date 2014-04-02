<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Cmf\Component\Routing\ContentAwareGenerator as BaseGenerator;

/**
 * **Deprecated**
 *
 * @author David Buchmann
 *
 * @deprecated This class is obsolete and the ContentAwareGenerator from the
 *  CMF routing component should be used directly instead. This class will be
 *  removed in 1.2.
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
