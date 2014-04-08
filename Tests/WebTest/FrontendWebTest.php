<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\WebTest;


use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;

class FrontendWebTest extends BaseTestCase
{
    public function setUp()
    {
        $this->db('PHPCR')->loadFixtures(array(
            'Symfony\Cmf\Bundle\SeoBundle\Tests\Resources\DataFixtures\Phpcr\LoadContentData',
        ));
        $this->client = $this->createClient();
    }

    public function testCandidateRoutes()
    {
        $this->client->request('GET', '/route-in-candidate-1');
        $res = $this->client->getResponse();

        $this->assertEquals(200, $res->getStatusCode());

        $this->client->request('GET', '/route-in-candidate-2');
        $res = $this->client->getResponse();

        $this->assertEquals(200, $res->getStatusCode());
    }
}
 