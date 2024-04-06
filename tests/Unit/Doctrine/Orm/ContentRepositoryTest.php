<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Orm;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\ContentRepository;

class ContentRepositoryTest extends TestCase
{
    private \stdClass $document;
    private ManagerRegistry&MockObject $managerRegistry;
    private ObjectManager&MockObject $objectManager;
    private ObjectRepository&MockObject $objectRepository;

    protected function setUp(): void
    {
        $this->document = new \stdClass();
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->objectRepository = $this->createMock(ObjectRepository::class);
    }

    public function testFindById(): void
    {
        $this->objectManager
            ->method('getRepository')
            ->with($this->equalTo('stdClass'))
            ->willReturn($this->objectRepository)
        ;

        $this->objectRepository
            ->method('find')
            ->with(123)
            ->willReturn($this->document)
        ;

        $this->managerRegistry
            ->method('getManager')
            ->willReturn($this->objectManager)
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $foundDocument = $contentRepository->findById('stdClass:123');

        $this->assertSame($this->document, $foundDocument);
    }

    /**
     * @dataProvider getFindCorrectModelAndIdData
     */
    public function testFindCorrectModelAndId($input, $model, $id): void
    {
        $this->objectManager
            ->method('getRepository')
            ->with($this->equalTo($model))
            ->willReturn($this->objectRepository)
        ;

        $this->objectRepository
            ->method('find')
            ->with($id)
            ->willReturn($this)
        ;

        $this->managerRegistry
            ->method('getManager')
            ->willReturn($this->objectManager)
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $foundDocument = $contentRepository->findById($input);
        $this->assertSame($this, $foundDocument);
    }

    public function getFindCorrectModelAndIdData(): array
    {
        return [
            ['Acme\ContentBundle\Entity\Content:12', 'Acme\ContentBundle\Entity\Content', 12],
            ['Id\Contains\Colon:12:1', 'Id\Contains\Colon', '12:1'],
            ['Class\EndsWith\Number12:20', 'Class\EndsWith\Number12', 20],
        ];
    }
}
