<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class AutoRoute
{
    /** @var string */
    public $basePath;

    /** @var boolean */
    public $resolvePathConflicts;

    /** @var string */
    public $titleMethod;
}

/**
 * @Annotation
 * @Target("METHOD")
 */
final class RouteName
{
}
