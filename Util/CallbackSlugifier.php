<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Util;

/**
 * Slugifier service which uses a callback
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class CallbackSlugifier implements SlugifierInterface
{
    protected $callback;

    /**
     * See: http://php.net/manual/en/language.types.callable.php
     *
     * @param mixed Php callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * {inheritDoc}
     */
    public function slugify($string)
    {
        return call_user_func($this->callback, $string);
    }
}
