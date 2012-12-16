<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Testdoc;

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
