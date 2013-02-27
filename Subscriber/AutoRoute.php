<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\PHPCR\Event;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\AutoRouteManager;
use Doctrine\ODM\PHPCR\Event\LifecycleEventArgs;
use Doctrine\ODM\PHPCR\Event\PostFlushEventArgs;
use Doctrine\ODM\PHPCR\Event\OnFlushEventArgs;

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
            Event::preUpdate,
            Event::prePersist,
            Event::onFlush,
            Event::preRemove,
        );
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->doUpdate($args);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->doUpdate($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        $dm = $args->getDocumentManager();
        if ($this->autoRouteManager->isAutoRouteable($document)) {
            $routes = $this->autoRouteManager->fetchAutoRoutesForDocument($document);
            foreach ($routes as $route) {
                $this->removeQueue[] = $route;
            }
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $dm = $args->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        foreach ($this->persistQueue as $document) {
            $route = $this->autoRouteManager->updateAutoRouteForDocument($document);
            $uow->computeSingleDocumentChangeSet($route);
        }

        foreach ($this->removeQueue as $document) {
            $uow->scheduleRemove($document);
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
