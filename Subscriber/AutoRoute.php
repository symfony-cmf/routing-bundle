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
            Event::onFlush,
        );
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

        $scheduledInserts = $uow->getScheduledInserts();
        $scheduledUpdates = $uow->getScheduledUpdates();
        $updates = array_merge($scheduledInserts, $scheduledUpdates);

        foreach ($updates as $document) {
            if ($this->autoRouteManager->isAutoRouteable($document)) {
                $route = $this->autoRouteManager->updateAutoRouteForDocument($document);
                $uow->computeSingleDocumentChangeSet($route);
            }
        }

        $removes = $uow->getScheduledRemovals();

        foreach ($removes as $document) {
            if ($this->autoRouteManager->isAutoRouteable($document)) {
                $routes = $this->autoRouteManager->fetchAutoRoutesForDocument($document);
                foreach ($routes as $route) {
                    $uow->scheduleRemove($route);
                }
            }
        }
    }
}
