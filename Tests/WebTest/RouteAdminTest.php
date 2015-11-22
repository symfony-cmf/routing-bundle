<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\WebTest;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class RouteAdminTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing-web';

    public function setUp()
    {
        $this->client = $this->createClient();
    }

    public function testRouteList()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/route/list');
        $res = $this->client->getResponse();
        $this->assertResponseSuccess($res);
        $this->assertCount(1, $crawler->filter('html:contains("route-1")'));
    }

    public function testRouteEdit()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/route'.self::ROUTE_ROOT.'/route-1/edit');
        $res = $this->client->getResponse();
        $this->assertResponseSuccess($res);
        $this->assertCount(1, $crawler->filter('input[value="route-1"]'));

        $this->assertFrontendLinkPresent($crawler);
    }

    public function testRouteShow()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/route'.self::ROUTE_ROOT.'/route-1/show');
        $res = $this->client->getResponse();
        $this->assertResponseSuccess($res);
    }

    public function testRouteCreate()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/route/create');
        $res = $this->client->getResponse();
        $this->assertResponseSuccess($res);

        $this->assertFrontendLinkNotPresent($crawler);

        $button = $crawler->selectButton('Create');
        $form = $button->form();
        $node = $form->getFormNode();
        $actionUrl = $node->getAttribute('action');
        $uniqId = substr(strchr($actionUrl, '='), 1);

        $form[$uniqId . '[parent]'] = self::ROUTE_ROOT;
        $form[$uniqId . '[name]'] = 'foo-test';

        $this->client->submit($form);
        $res = $this->client->getResponse();

        // If we have a 302 redirect, then all is well
        $this->assertEquals(302, $res->getStatusCode());

        // clean up
        $dm = $this->db('PHPCR')->getOm();
        $dm->remove($dm->find(null, self::ROUTE_ROOT.'/foo-test'));
        $dm->flush();
        $dm->clear();
    }

    /**
     * @param Crawler $crawler
     */
    private function assertFrontendLinkPresent(Crawler $crawler)
    {
        $this->assertCount(1, $link = $crawler->filter('a[class="sonata-admin-frontend-link"]'));
        $this->assertEquals('/route-1', $link->attr('href'));
    }

    /**
     * @param Crawler $crawler
     */
    private function assertFrontendLinkNotPresent(Crawler $crawler)
    {
        $this->assertCount(0, $crawler->filter('a[class="sonata-admin-frontend-link"]'));
    }
}
