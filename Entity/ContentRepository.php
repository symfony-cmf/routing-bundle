<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Entity;

use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;

/**
 * Description of ContentRepository
 *
 * @author teito
 */
class ContentRepository implements ContentRepositoryInterface
{
    public function __construct($orm)
    {
        $this->orm = $orm;
    }

    /**
     * {@inheritDoc}
     */
    public function findById($id)
    {
        $identifier = $id;

        list($model, $id) = $this->getModelAndId($identifier);

        return $this->orm->getRepository($model)->find($id);
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
            return implode(':', array(
                get_class($content),
                $content->getId()
            ));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getModelAndId($identifier)
    {
        $model = substr($identifier, 0, strpos($identifier, ':'));
        $id = substr($identifier, strpos($identifier, ':'));

        return array($model, $id);
    }
}
