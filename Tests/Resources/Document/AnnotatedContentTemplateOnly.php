<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\Document;


use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Cmf\Bundle\RoutingBundle\Metadata\Annotations as CmfRouting;

/**
 * @PHPCRODM\Document(referenceable=true)
 *
 * @CmfRouting\Template("TestBundle:Content:index.html.twig")
 */
class AnnotatedContentTemplateOnly
{
    /**
     * @PHPCRODM\Id
     */
    public $path;

    /**
     * PHPCRODM\String()
     */
    public $title;

    public function setId($path)
    {
        $this->path = $path;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
