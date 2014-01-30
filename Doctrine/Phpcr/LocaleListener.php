<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\Event\MoveEventArgs;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

/**
 * Doctrine PHPCR-ODM listener to update the locale on routes based on the URL.
 *
 * It uses the idPrefix and looks at the path segment right after the prefix to
 * determine if that segment matches one of the configured locales and if so
 * sets a requirement and default named _locale for that locale.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class LocaleListener
{
    /**
     * Used to ask for the possible prefixes to determine the possible locale
     * segment of the id.
     *
     * @var RouteProvider
     */
    protected $provider;

    /**
     * List of possible locales to detect on URL after idPrefix
     *
     * @var array
     */
    protected $locales;

    /**
     * If set, call Route::setAddLocalePattern.
     * @var boolean
     */
    protected $addLocalePattern;

    /**
     * @param RouteProvider $provider         To get prefixes from.
     * @param array         $locales          Locales that should be detected.
     * @param bool          $addLocalePattern Whether to make route prepend the
     *                                        locale pattern if it does not have
     *                                        one of the allowed locals in its id.
     */
    public function __construct(RouteProvider $provider, array $locales, $addLocalePattern = false)
    {
        $this->provider = $provider;
        $this->locales = $locales;
        $this->addLocalePattern = $addLocalePattern;
    }

    /**
     * Specify the list of allowed locales.
     *
     * @param array $locales
     */
    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    /**
     * Whether to make the route prepend the locale pattern if it does not
     * have one of the allowed locals in its id.
     *
     * @param boolean $addLocalePattern
     */
    public function setAddLocalePattern($addLocalePattern)
    {
        $this->addLocalePattern = $addLocalePattern;
    }

    /**
     * Update locale after loading a route.
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();
        if (! $doc instanceof Route) {
            return;
        }
        $this->updateLocale($doc, $doc->getId());
    }

    /**
     * Update locale after persisting a route.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();
        if (! $doc instanceof Route) {
            return;
        }
        $this->updateLocale($doc, $doc->getId());
    }

    /**
     * Update a locale after the route has been moved.
     *
     * @param MoveEventArgs $args
     */
    public function postMove(MoveEventArgs $args)
    {
        $doc = $args->getObject();
        if (! $doc instanceof Route) {
            return;
        }
        $this->updateLocale($doc, $args->getTargetPath(), true);
    }

    /**
     * @return array
     */
    protected function getPrefixes()
    {
        return $this->provider->getPrefixes();
    }

    /**
     * Update the locale of a route if $id starts with the prefix and has a
     * valid locale right after.
     *
     * @param Route   $doc   The route object
     * @param string  $id    The id (in move case, this is not the current id
     *                       of $route)
     * @param boolean $force Whether to update the locale even if the route
     *                       already has a locale.
     */
    protected function updateLocale(Route $doc, $id, $force = false)
    {
        $matches = array();

        // only update route objects and only if the prefix can match, to allow
        // for more than one listener and more than one route root
        if (! preg_match('#(' . implode('|', $this->getPrefixes()) . ')/([^/]+)(/|$)#', $id, $matches)) {
            return;
        }

        if (in_array($locale = $matches[2], $this->locales)) {
            if ($force || ! $doc->getDefault('_locale')) {
                $doc->setDefault('_locale', $locale);
            }
            if ($force || ! $doc->getRequirement('_locale')) {
                $doc->setRequirement('_locale', $locale);
            }
        } elseif ($this->addLocalePattern) {
            $doc->setOption('add_locale_pattern', true);
        }
    }
}
