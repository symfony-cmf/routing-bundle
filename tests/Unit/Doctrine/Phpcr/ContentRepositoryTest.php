<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\ContentRepository;

class ContentRepositoryTest extends TestCase
{
    private \stdClass $document;
    private \stdClass $document2;
    private DocumentManagerInterface&MockObject $objectManager;
    private DocumentManagerInterface&MockObject $objectManager2;
    private ManagerRegistry&MockObject $managerRegistry;

    protected function setUp(): void
    {
        $this->document = new \stdClass();
        $this->document2 = new \stdClass();
        $this->objectManager = $this->createMock(DocumentManagerInterface::class);
        $this->objectManager2 = $this->createMock(DocumentManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
    }

    public function testFindById(): void
    {
        $this->objectManager
            ->method('find')
            ->with(null, 'id-123')
            ->willReturn($this->document)
        ;

        $this->managerRegistry
            ->method('getManager')
            ->willReturn($this->objectManager)
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $this->assertSame($this->document, $contentRepository->findById('id-123'));
    }

    public function testGetContentId(): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects($this->once())
            ->method('getDocumentId')
            ->with($this->document)
            ->willReturn('id-123')
        ;

        $dm = $this->createMock(DocumentManager::class);
        $dm
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($uow)
        ;
        $this->managerRegistry
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($dm)
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $this->assertEquals('id-123', $contentRepository->getContentId($this->document));
    }

    public function testGetContentIdNoObject(): void
    {
        $contentRepository = new ContentRepository($this->managerRegistry);
        $this->assertNull($contentRepository->getContentId('hello'));
    }

    public function testGetContentIdException(): void
    {
        $this->managerRegistry
            ->expects($this->once())
            ->method('getManager')
            ->willThrowException(new \Exception())
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $this->assertNull($contentRepository->getContentId($this->document));
    }

    /**
     * Use findById() with two different document managers.
     * The two document managers will return different documents when searching for the same id.
     */
    public function testChangingDocumentManager(): void
    {
        $this->objectManager
            ->method('find')
            ->with(null, 'id-123')
            ->willReturn($this->document)
        ;

        $this->objectManager2
            ->method('find')
            ->with(null, 'id-123')
            ->willReturn($this->document2)
        ;

        $objectManagers = [
            'default' => $this->objectManager,
            'new_manager' => $this->objectManager2,
        ];
        $this->managerRegistry
            ->method('getManager')
            ->willReturnCallback(
                function ($name) use ($objectManagers) {
                    return $objectManagers[$name];
                }
            );

        $contentRepository = new ContentRepository($this->managerRegistry);

        $contentRepository->setManagerName('default');
        $this->assertSame($this->document, $contentRepository->findById('id-123'));

        $contentRepository->setManagerName('new_manager');
        $this->assertSame($this->document2, $contentRepository->findById('id-123'));
    }
}
