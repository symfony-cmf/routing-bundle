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

        $this->driver = $this->getContainer()->get('cmf_routing.metadata.factory');
    }

    public function testDriver()
    {
        $meta = $this->driver->getMetadataForClass(
            'Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\Document\AnnotatedContent'
        );

        $this->assertNotNull($meta);

        $meta = $meta->getOutsideClassMetadata();

        $this->assertEquals('TestBundle:Content:index.html.twig', $meta->template);
        $this->assertEquals('ThisIsAController', $meta->controller);
    }
}
