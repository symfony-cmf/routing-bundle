<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Testdoc;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * @PHPCRODM\Document(referenceable=true)
 */
class Content
{
    /**
     * @PHPCRODM\Id
     */
    public $path;
}
