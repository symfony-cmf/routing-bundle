<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Document\ContentRepository;

class ContentRepositoryTest extends \PHPUnit_Framework_Testcase
{
    private $document;
    private $document2;
    private $objectManager;
    private $objectManager2;
    private $managerRegistry;

    public function setUp()
    {
        $this->document = $this->getMock('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument');
        $this->document2 = $this->getMock('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->objectManager2 = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    }

    public function testFindById()
    {
        $this->document
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/my-document'))
        ;

        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($this->document))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
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
        $this->document
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/my-document'))
        ;

        $this->document2
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/new-document'))
        ;

        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, 'id-123')
            ->will($this->returnValue($this->document))
        ;

        $this->objectManager2
            ->expects($this->any())
            ->method('find')
            ->with(null, 'id-123')
            ->will($this->returnValue($this->document2))
        ;

        $objectManagers = array(
            'default' => $this->objectManager,
            'new_manager' => $this->objectManager2
        );
        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will(
                $this->returnCallback(
                    function ($name) use ($objectManagers) {
                        return $objectManagers[$name];
                    }
                )
            );

        $contentRepository = new ContentRepository($this->managerRegistry);

        $contentRepository->setManagerName('default');
        $foundDocument = $contentRepository->findById('id-123');
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument', $foundDocument);
        $this->assertEquals('/cms/my-document', $foundDocument->getPath());

        $contentRepository->setManagerName('new_manager');
        $newFoundDocument = $contentRepository->findById('id-123');
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingBundle\Tests\Document\TestDocument', $newFoundDocument);
        $this->assertEquals('/cms/new-document', $newFoundDocument->getPath());
    }
}
