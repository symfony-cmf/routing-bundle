<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\PHPCR\Event;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\AutoRouteManager;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;
use Doctrine\ODM\PHPCR\Event\PostFlushEventArgs;

/**
 * Doctrine PHPCR ODM Subscriber for maintaining automatic routes.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 * @date 13/02/22
 */
class AutoRoute implements EventSubscriber
{
    protected $autoRouteManager;

    protected $persistQueue = array();
    protected $removeQueue = array();

    public function __construct(AutoRouteManager $autoRouteManager)
    {
        $this->autoRouteManager = $autoRouteManager;
    }

    public function getSubscribedEvents()
    {
        return array(
            Event::postUpdate,
            Event::postPersist,
            Event::preRemove,
            Event::postFlush,
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->doUpdate($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->doUpdate($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($this->autoRouteManager->isAutoRouteable($document)) {
            $routes = $this->autoRouteManager->fetchAutoRoutesForDocument($document);
            foreach ($routes as $route) {
                $this->removeQueue[] = $route;
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();

        $doFlush = (!empty($this->removeQueue) || !empty($this->persistQueue));

        if (count($this->persistQueue) > 0) {
            foreach ($this->persistQueue as $document) {
                $this->autoRouteManager->updateAutoRouteForDocument($document);
            }

            $this->persistQueue = array();
        }

        if (count($this->removeQueue) > 0) {
            foreach ($this->removeQueue as $route) {
                $dm->remove($route);
            }
            $this->removeQueue = array();
        }

        if ($doFlush) {
        }
    }

    protected function doUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        if ($this->autoRouteManager->isAutoRouteable($document)) {
            $this->persistQueue[spl_object_hash($document)] = $document;
        }
    }
}
