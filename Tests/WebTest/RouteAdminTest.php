<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\WebTest\Admin\MenuNodeAdminTest;

use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class RouteAdminTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array(
            'Symfony\Cmf\Bundle\RoutingBundle\Tests\Resources\DataFixtures\Phpcr\LoadRouteData',
        ));
        $this->client = $this->createClient();
    }

    public function testMenuList()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/route/list');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertCount(1, $crawler->filter('html:contains("route-1")'));
    }

    public function testMenuEdit()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/route/test/routing/route-1/edit');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertCount(1, $crawler->filter('input[value="route-1"]'));
    }

    public function testMenuShow()
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function testMenuCreate()
    {
        $crawler = $this->client->request('GET', '/admin/cmf/routing/route/create');
        $res = $this->client->getResponse();
        $this->assertEquals(200, $res->getStatusCode());

        $button = $crawler->selectButton('Create');
        $form = $button->form();
        $node = $form->getFormNode();
        $actionUrl = $node->getAttribute('action');
        $uniqId = substr(strchr($actionUrl, '='), 1);

        $form[$uniqId.'[parent]'] = '/test/routing';
        $form[$uniqId.'[name]'] = 'foo-test';

        $this->client->submit($form);
        $res = $this->client->getResponse();

        // If we have a 302 redirect, then all is well
        $this->assertEquals(302, $res->getStatusCode());
    }
}
