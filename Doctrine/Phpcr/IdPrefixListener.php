<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Doctrine PHPCR-ODM listener to set the idPrefix on routes
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class IdPrefixListener
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

    /**
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->idPrefix = $prefix;
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
        $doc = $args->getObject();

        // only update route objects and only if the prefix can match, to allow
        // for more than one listener and more than one route root
        if (($doc instanceof Route)
            && ! strncmp($this->idPrefix, $doc->getId(), strlen($this->idPrefix))
        ) {
            $prefix = $this->idPrefix;
            $parent = $doc;

            do {
                if ($parent instanceof Host) {
                    $prefix = $parent->getId();
                    break;
                }
            } while ($parent = $parent->getParent());

            $doc->setPrefix($prefix);
        }
    }
}
