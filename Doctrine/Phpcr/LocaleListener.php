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
     * The prefix to add to the url to create the repository path
     *
     * @var string
     */
    protected $idPrefix = '';

    /**
     * List of possible locales to detect on URL after idPrefix
     *
     * @var array
     */
    protected $locales;

    public function __construct($prefix, array $locales)
    {
        $this->idPrefix = $prefix;
        $this->locales = $locales;
    }

    /**
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
    }

    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();
        if (! $doc instanceof Route) {
            return;
        }
        $this->updateLocale($doc, $doc->getId());
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $doc = $args->getObject();
        if (! $doc instanceof Route) {
            return;
        }
        $this->updateLocale($doc, $doc->getId());
    }

    public function postMove(MoveEventArgs $args)
    {
        $doc = $args->getObject();
        if (! $doc instanceof Route) {
            return;
        }
        $this->updateLocale($doc, $args->getTargetPath());
    }

    protected function updateLocale(Route $doc, $id)
    {
        $matches = array();

        // only update route objects and only if the prefix can match, to allow
        // for more than one listener and more than one route root
        if (! preg_match('#' . $this->idPrefix . '/([^/]+)(/|$)#', $id, $matches)) {
            return;
        }

        if (in_array($matches[1], $this->locales)
            && ! $doc->getDefault('_locale')
            && ! $doc->getRequirement('_locale')
        ) {
            $doc->setDefault('_locale', $matches[1]);
            $doc->setRequirement('_locale', $matches[1]);
        }
    }
}
