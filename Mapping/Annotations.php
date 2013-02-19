<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Mapping\Annotations;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class AutoRoute
{
    public $basePath;
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class DefaultRoute
{
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class ParentRoutes
{
}

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Permalink
{
}

/**
 * @Annotation
 * @Target("METHOD")
 */
final class RouteName
{
    public $transformation;
}
