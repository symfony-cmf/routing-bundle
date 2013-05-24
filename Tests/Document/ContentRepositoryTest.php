<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Document\ContentRepository;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\BaseTestCase;

class ContentRepositoryTest extends BaseTestCase
{
    public function testFindById()
    {
        $managerRegistry = $this->getManagerRegistry(
            array(
                'default' => $this->getObjectManager(
                    array('id-123' => $this->getDocument('/cms/my-document'))
                )
            )
        );

        $contentRepository = new ContentRepository($managerRegistry);
        $contentRepository->setManagerName('default');

        $foundDocument = $contentRepository->findById('id-123');

        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument', $foundDocument);
        $this->assertEquals('/cms/my-document', $foundDocument->getPath());
    }

    public function testGetContentId()
    {
        $this->markTestIncomplete();
    }

    /**
     * Use findById() with two different document managers.
     * The two document managers will return different documents when searching for the same id.
     */
    public function testChangingDocumentManager()
    {
        $managerRegistry = $this->getManagerRegistry(
            array(
                'default' => $this->getObjectManager(
                    array('id-123' => $this->getDocument('/cms/my-document'))
                ),
                'new_manager' => $this->getObjectManager(
                    array('id-123' => $this->getDocument('/cms/new-document'))
                )
            )
        );
        $contentRepository = new ContentRepository($managerRegistry);

        $contentRepository->setManagerName('default');
        $foundDocument = $contentRepository->findById('id-123');
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument', $foundDocument);
        $this->assertEquals('/cms/my-document', $foundDocument->getPath());

        $contentRepository->setManagerName('new_manager');
        $newFoundDocument = $contentRepository->findById('id-123');
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument', $newFoundDocument);
        $this->assertEquals('/cms/new-document', $newFoundDocument->getPath());
    }

    /**
     * Get a mock document that returns the supplied $path for getPath().
     *
     * @param $path
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDocument($path)
    {
        $document = $this->getMock('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument');
        $document->expects($this->any())->method('getPath')->will($this->returnValue($path));

        return $document;
    }
}
