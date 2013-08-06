<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\ContentRepository;

class ContentRepositoryTest extends \PHPUnit_Framework_Testcase
{
    private $document;
    private $document2;
    private $objectManager;
    private $objectManager2;
    private $managerRegistry;

    public function setUp()
    {
        $this->document = new \stdClass;
        $this->document2 = new \stdClass;
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->objectManager2 = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    }

    public function testFindById()
    {
        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, 'id-123')
            ->will($this->returnValue($this->document))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $this->assertSame($this->document, $contentRepository->findById('id-123'));
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
        $this->assertSame($this->document, $contentRepository->findById('id-123'));

        $contentRepository->setManagerName('new_manager');
        $this->assertSame($this->document2, $contentRepository->findById('id-123'));
    }
}
