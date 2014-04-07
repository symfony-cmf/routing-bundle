<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Metadata\Driver;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;

class AnnotationDriverTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->chainDriver = $this->getContainer()->get('cmf_routing.metadata.factory');
        $this->annotDriver = $this->getContainer()->get('cmf_routing.metadata.driver.annotation');
    }

    public function testDriverTemplateAndContent()
    {
        $meta = $this->chainDriver->getMetadataForClass(
            'Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\Document\AnnotatedContent'
        );

        $this->assertNotNull($meta);

        $meta = $meta->getOutsideClassMetadata();

        $this->assertEquals('TestBundle:Content:index.html.twig', $meta->template);
        $this->assertEquals('ThisIsAController', $meta->controller);
    }

    public function testDriverTemplateOnlyWithGenericController()
    {
        $meta = $this->chainDriver->getMetadataForClass(
            'Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\Document\AnnotatedContentTemplateOnly'
        );

        $this->assertNotNull($meta);

        $meta = $meta->getOutsideClassMetadata();

        $this->assertEquals('TestBundle:Content:index.html.twig', $meta->template);
        $this->assertEquals('cmf_content.controller:indexAction', $meta->controller);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage but no controller specified
     */
    public function testDriverTemplateOnlyNoController()
    {
        $this->annotDriver->setGenericController(null);
        $meta = $this->chainDriver->getMetadataForClass(
            'Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\Document\AnnotatedContentTemplateOnly'
        );
    }
}
