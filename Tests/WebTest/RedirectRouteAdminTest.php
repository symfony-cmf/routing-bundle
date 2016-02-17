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

class RedirectRouteAdminTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array(
            'Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\DataFixtures\Phpcr\LoadRouteData',
        ));
        $this->client = $this->createClient();
    }

    public function testRedirectRouteList()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/redirectroute/list');
        $res = $this->client->getResponse();
        $this->assertResponseOk($res);
        $this->assertCount(1, $crawler->filter('html:contains("redirect-route-1")'));
    }

    public function testRedirectRouteEdit()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/redirectroute/test/routing/redirect-route-1/edit');
        $res = $this->client->getResponse();
        $this->assertResponseOk($res);
        $this->assertCount(1, $crawler->filter('input[value="redirect-route-1"]'));

        $this->assertFrontendLinkPresent($crawler);
    }

    public function testRedirectRouteShow()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/redirectroute/test/routing/redirect-route-1/show');
        $res = $this->client->getResponse();
        $this->assertResponseOk($res);
    }

    public function testRedirectRouteCreate()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/redirectroute/create');
        $res = $this->client->getResponse();
        $this->assertResponseOk($res);

        $this->assertFrontendLinkNotPresent($crawler);

        $button = $crawler->selectButton('Create');
        $form = $button->form();
        $node = $form->getFormNode();
        $actionUrl = $node->getAttribute('action');
        $uniqId = substr(strstr($actionUrl, '='), 1);

        $form[$uniqId.'[parentDocument]'] = '/test/routing';
        $form[$uniqId.'[name]'] = 'foo-test';

        $this->client->submit($form);
        $res = $this->client->getResponse();

        // If we have a 302 redirect, then all is well
        $this->assertEquals(302, $res->getStatusCode());
    }

    /**
     * @param Crawler $crawler
     */
    private function assertFrontendLinkPresent(Crawler $crawler)
    {
        $this->assertCount(1, $link = $crawler->filter('a[class="sonata-admin-frontend-link"]'), 'The page contains a frontend link');
        $this->assertEquals('/redirect-route-1', $link->attr('href'));
    }

    /**
     * @param Crawler $crawler
     */
    private function assertFrontendLinkNotPresent(Crawler $crawler)
    {
        $this->assertCount(0, $crawler->filter('a[class="sonata-admin-frontend-link"]'));
    }

    private function assertResponseOk(Response $response)
    {
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    }
}
