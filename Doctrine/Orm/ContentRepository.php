<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm;

use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\DoctrineProvider;

/**
 * Abstract content repository for ORM
 *
 * @author teito
 */
class ContentRepository extends DoctrineProvider implements ContentRepositoryInterface
{
    /**
     * Determine target class and id for this content
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
     * {@inheritDoc}
     */
    public function findById($id)
    {
        list($model, $modelId) = $this->getModelAndId($id);

        return $this->getObjectManager()->getRepository($model)->find($modelId);
    }

    /**
     * {@inheritDoc}
     */
    public function getContentId($content)
    {
        if (! is_object($content)) {
            return null;
        }

        try {
            $meta = $this->getObjectManager()->getClassMetadata(get_class($content));
            $ids = $meta->getIdentifierValues($content);
            if (0 !== count($ids)) {
                throw new \Exception('Multi identifier values not supported in ' . get_class($content));
            }

            return implode(':', array(
                get_class($content),
                reset($ids)
            ));
        } catch (\Exception $e) {
            return null;
        }
    }
}
