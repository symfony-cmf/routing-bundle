<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Testdoc;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCR;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\Annotations as CMFRouting;

/**
 * @PHPCR\Document(
 *      referenceable=true
 * )
 *
 * @CMFRouting\AutoRoute(
 *     basePath="/functional/routing",
 *     resolvePathConflicts=false
 * )
 */
class Post
{
    /**
     * @PHPCR\Id()
     */
    public $path;

    /**
     * @PHPCR\Referrers(filter="routeContent")
     */
    public $routes;

    /**
     * @PHPCR\String()
     */
    public $title;

    /**
     * @CMFRouting\RouteName()
     */
    public function getTitle()
    {
        return $this->title;
    }
}
