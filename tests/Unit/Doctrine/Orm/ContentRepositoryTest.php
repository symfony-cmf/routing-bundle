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
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\ContentRepository;

class ContentRepositoryTest extends TestCase
{
    private $document;

    private $managerRegistry;

    private $objectManager;

    private $objectRepository;

    public function setUp(): void
    {
        $this->document = new \stdClass();
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->objectRepository = $this->createMock(ObjectRepository::class);
    }

    public function testFindById()
    {
        $this->objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('stdClass'))
            ->will($this->returnValue($this->objectRepository))
        ;

        $this->objectRepository
            ->expects($this->any())
            ->method('find')
            ->with(123)
            ->will($this->returnValue($this->document))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $foundDocument = $contentRepository->findById('stdClass:123');

        $this->assertSame($this->document, $foundDocument);
    }

    /**
     * @dataProvider getFindCorrectModelAndIdData
     */
    public function testFindCorrectModelAndId($input, $model, $id)
    {
        $this->objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo($model))
            ->will($this->returnValue($this->objectRepository))
        ;

        $this->objectRepository
            ->expects($this->any())
            ->method('find')
            ->with($id)
            ->will($this->returnValue($this))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $contentRepository = new ContentRepository($this->managerRegistry);
        $contentRepository->setManagerName('default');

        $foundDocument = $contentRepository->findById($input);
        $this->assertSame($this, $foundDocument);
    }

    public function getFindCorrectModelAndIdData()
    {
        return [
            ['Acme\ContentBundle\Entity\Content:12', 'Acme\ContentBundle\Entity\Content', 12],
            ['Id\Contains\Colon:12:1', 'Id\Contains\Colon', '12:1'],
            ['Class\EndsWith\Number12:20', 'Class\EndsWith\Number12', 20],
        ];
    }
}
