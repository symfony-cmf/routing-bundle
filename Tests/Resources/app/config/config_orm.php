<?php

$loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
$loader->import(CMF_TEST_CONFIG_DIR.'/phpcr_odm.php'); // todo find some way to don't have this in the ORM config
$loader->import(CMF_TEST_CONFIG_DIR.'/doctrine_orm.php');
$loader->import(CMF_TEST_CONFIG_DIR.'/sonata_admin.php');
$loader->import(__DIR__.'/cmf_routing.yml');
$loader->import(__DIR__.'/cmf_routing.orm.yml');
