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

    public function __construct($prefix)
    {
        $this->idPrefix = $prefix;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $this->updateId($args);
    }
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateId($args);
    }
    protected function updateId(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();
        // only update route objects and only if the prefix can match, to allow
        // for more than one listener and more than one route root
        if ($doc instanceof Route
            && ! strncmp($this->idPrefix, $doc->getPath(), strlen($this->idPrefix))) {
            $doc->setPrefix($this->idPrefix);
        }
    }
}
