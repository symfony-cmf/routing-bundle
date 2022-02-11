<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\DoctrineProvider;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;

/**
 * Abstract content repository for ORM.
 *
 * This repository follows the pattern of FQN:id. That is, the full model class
 * name, then a colon, then the id. For example "Acme\Content:12".
 *
 * This will only work with single column ids.
 *
 * @author teito
 */
class ContentRepository extends DoctrineProvider implements ContentRepositoryInterface
{
    /**
     * Determine target class and id for this content.
     *
     * @return string[] with model first element, id second
     */
    protected function getModelAndId(string $identifier): array
    {
        return explode(':', $identifier, 2);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id The ID contains both model name and id, separated by a colon
     */
    public function findById(mixed $id): ?object
    {
        [$model, $modelId] = $this->getModelAndId($id);

        return $this->getObjectManager()->getRepository($model)->find($modelId);
    }

    public function getContentId(mixed $content): ?string
    {
        if (!\is_object($content)) {
            return null;
        }

        try {
            $class = \get_class($content);
            $meta = $this->getObjectManager()->getClassMetadata($class);
            $ids = $meta->getIdentifierValues($content);
            if (1 !== \count($ids)) {
                throw new \Exception(sprintf('Class "%s" must use only one identifier', $class));
            }

            return implode(':', [$class, reset($ids)]);
        } catch (\Exception $e) {
            return null;
        }
    }
}
