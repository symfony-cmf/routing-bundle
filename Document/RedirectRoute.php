<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute as PhpcrRedirectRoute;

/**
 * @deprecated: use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute instead
 */
class RedirectRoute extends PhpcrRedirectRoute
{
    public function __construct($addFormatPattern = false, $addTrailingSlash = false)
    {
        trigger_error('Use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute instead', E_USER_DEPRECATED);
        parent::__construct($addFormatPattern, $addTrailingSlash);
    }
}
