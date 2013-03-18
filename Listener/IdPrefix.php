<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Listener;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;

/**
 * Doctrine PHPCR-ODM listener to set the idPrefix on new routes
 *
 * @author david.buchmann@liip.ch
 */
class IdPrefix
{
    /**
     * The prefix to add to the url to create the repository path
     *
     * @var string
     */
    protected $idPrefix = '';

    /**
     * A regular expression to match potential locales routes with this prefix
     * are allowed to have.
     *
     * Something along '#^/(de|fr|en)(/|$)#'
     *
     * @var string
     */
    protected $locales = false;

    public function __construct($prefix, array $locales = null)
    {
        $this->idPrefix = $prefix;
        if ($locales) {
            $this->locales = '#^/(' . implode('|', $locales) . ')(/|$)#';
        }
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
        $this->updateId($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateId($args);
    }

    protected function updateId(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        // only update route objects and only if the prefix can match, to allow
        // for more than one listener and more than one route root
        if ($doc instanceof Route
            && ! strncmp($this->idPrefix, $doc->getId(), strlen($this->idPrefix))
        ) {
            $doc->setPrefix($this->idPrefix);
            $doc->setLocales($this->locales);
        }
    }
}
