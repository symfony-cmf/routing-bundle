<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route as PhpcrRoute;

/**
 * @deprecated: use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route instead
 */
class Route extends PhpcrRoute
{
    public function __construct($addFormatPattern = false, $addTrailingSlash = false)
    {
        trigger_error('Use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route instead', E_USER_DEPRECATED);
        parent::__construct($addFormatPattern, $addTrailingSlash);
    }

    /**
     * @deprecated use setContent instead
     */
    public function setRouteContent($object)
    {
        trigger_error('Use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route::setContent', E_USER_DEPRECATED);
        $this->setContent($object);
    }

    /**
     * @deprecated use getContent instead
     */
    public function getRouteContent()
    {
        trigger_error('Use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route::getContent', E_USER_DEPRECATED);
        return $this->getContent();
    }

}
