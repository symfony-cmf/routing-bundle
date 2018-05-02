<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->addCollection($loader->import(__DIR__.'/test_routing.yml'));

return $collection;
