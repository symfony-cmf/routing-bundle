<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Symfony\Component\HttpFoundation\Request;

/**
 * Loads routes from Doctrine PHPCR-ODM, supporting the locale as first bit
 * after the prefix or as variable pattern.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class LocaleRouteProvider extends RouteProvider
{
    /**
     * The allowed locales.
     *
     * @var array
     */
    protected $locales = array();

    /**
     * The detected locale in this request.
     *
     * @var string
     */
    private $locale;

    /**
     * Set the allowed locales.
     *
     * @param array $locales
     */
    public function setLocales(array $locales = array())
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritDoc}
     *
     * Additionally checks if $url starts with a locale, and if so proposes
     * additional candidates without the locale.
     */
    protected function getCandidates($url)
    {
        $directCandidates = parent::getCandidates($url);

        $dirs = explode('/', ltrim($url, '/'));
        if (isset($dirs[0]) && in_array($dirs[0], $this->locales)) {
            $this->locale = $dirs[0];
            array_shift($dirs);
            $url = '/'.implode('/', $dirs);

            // the normal listener "waits" until the routing completes
            // as the locale could be defined inside the route
            $this->getObjectManager()->getLocaleChooserStrategy()->setLocale($this->locale);

            return array_merge($directCandidates, parent::getCandidates($url));
        }

        return $directCandidates;
    }

    protected function configureLocale($route)
    {
        if ($this->getObjectManager()->isDocumentTranslatable($route)) {
            // add locale requirement
            if (!$route->getRequirement('_locale')) {
                $locales = $this->getObjectManager()->getLocalesFor($route, true);
                $route->setRequirement('_locale', implode('|', $locales));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteCollectionForRequest(Request $request)
    {
        $collection = parent::getRouteCollectionForRequest($request);
        foreach ($collection as $route) {
            $this->configureLocale($route);
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name, $parameters = array())
    {
        $route = parent::getRouteByName($name, $parameters);
        $this->configureLocale($route);

        return $route;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names = null)
    {
        $collection = parent::getRoutesByNames($names);
        foreach ($collection as $route) {
            $this->configureLocale($route);
        }

        return $collection;
    }
}
