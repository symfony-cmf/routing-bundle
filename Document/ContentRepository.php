<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Document;

use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Implement ContentRepositoryInterface for phpcr-odm
 *
 * @author Uwe Jäger
 */
class ContentRepository implements ContentRepositoryInterface
{
    /** @var string Name of object manager to use */
    protected $managerName;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Set the object manager name to use for this loader;
     * if not called, the default manager will be used.
     *
     * @param string $managerName
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;
    }

    /**
     * Return a content object by it's id or null if there is none.
     *
     * @param $id mixed id of the content object
     * @return mixed
     */
    public function findById($id)
    {
        return $this->getObjectManager()->find(null, $id);
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
            return $this->getObjectManager()->getUnitOfWork()->getDocumentId($content);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the object manager from the registry, based on the current managerName
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->managerRegistry->getManager($this->managerName);
    }
}
