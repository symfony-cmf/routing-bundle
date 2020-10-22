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
     * @param mixed $identifier as produced by getContentId
     *
     * @return array with model first element, id second
     */
    protected function getModelAndId($identifier)
    {
        return explode(':', $identifier, 2);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id The ID contains both model name and id, separated by a colon
     */
    public function findById($id)
    {
        list($model, $modelId) = $this->getModelAndId($id);

        return $this->getObjectManager()->getRepository($model)->find($modelId);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentId($content)
    {
        if (!\is_object($content)) {
            return;
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
            return;
        }
    }
}
