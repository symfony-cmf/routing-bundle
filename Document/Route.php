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
}
