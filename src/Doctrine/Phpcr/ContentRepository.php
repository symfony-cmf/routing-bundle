<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\DoctrineProvider;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;

/**
 * Implement ContentRepositoryInterface for PHPCR-ODM.
 *
 * This is <strong>NOT</strong> not a doctrine repository but just the content
 * provider for the NestedMatcher. (you could of course implement this
 * interface in a repository class, if you need that)
 *
 * @author Uwe Jäger
 */
class ContentRepository extends DoctrineProvider implements ContentRepositoryInterface
{
    public function findById($id): ?object
    {
        return $this->getObjectManager()->find(null, $id);
    }

    public function getContentId($content): ?string
    {
        if (!\is_object($content)) {
            return null;
        }

        try {
            return $this->getObjectManager()->getUnitOfWork()->getDocumentId($content);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Make sure the manager is a PHPCR-ODM manager.
     */
    protected function getObjectManager(): DocumentManagerInterface
    {
        $dm = parent::getObjectManager();
        if (!$dm instanceof DocumentManagerInterface) {
            throw new \LogicException(sprintf('Expected %s, got %s', DocumentManagerInterface::class, get_class($dm)));
        }

        return $dm;
    }
}
