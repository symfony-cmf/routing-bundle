<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\Exception;

class MoreThanOneAutoRoute extends \Exception
{
    public function __construct($document)
    {
        $message = sprintf(
            'Found more than one AutoRoute for document of class "%s"',
            get_class($document)
        );
        parent::__construct($message);
    }

}
