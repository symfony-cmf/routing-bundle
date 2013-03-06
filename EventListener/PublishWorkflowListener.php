<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowCheckerInterface;

/**
 * Makes sure only published routes can be accessed
 */
class PublishWorkflowListener implements EventSubscriberInterface
{

    /**
     * @var PublishWorkflowCheckerInterface
     */
    protected $publishWorkflowChecker;

    /**
     * @param PublishWorkflowCheckerInterface $publishWorkflowChecker
     */
    public function __construct(PublishWorkflowCheckerInterface $publishWorkflowChecker)
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;
    }

    /**
     * Handling the request event
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('routeDocument');

        if (!$this->publishWorkflowChecker->checkIsPublished($route, false, $request)) {
            throw new NotFoundHttpException('Route not found: ' . $request->getPathInfo());
        }
    }

    /**
     * We are only interested in request events.
     *
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1)),
        );
    }

}
