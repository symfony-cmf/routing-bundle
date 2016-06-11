<?php

use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();

$treeBrowserVersion = '1.x';
if (class_exists('Symfony\Cmf\Bundle\ResourceRestBundle\CmfResourceRestBundle')) {
    $treeBrowserVersion = '2.x';
}

$collection->addCollection($loader->import(__DIR__.'/tree_browser_'.$treeBrowserVersion.'.yml'));
$collection->addCollection($loader->import(__DIR__.'/test_routing.yml'));

return $collection;
