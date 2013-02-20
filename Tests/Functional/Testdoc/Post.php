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
 *     basePath="/test/routing",
 *     resolvePathConflicts=false
 * )
 */
class Post
{
    /**
     * @PHPCR\Id
     */
    public $path;

    /**
     * @CMFRouting\RouteName()
     */
    public function getTitle()
    {
        return "Unit testing a blog post";
    }
}
