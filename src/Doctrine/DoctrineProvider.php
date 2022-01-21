<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * Abstract class for doctrine based content repository and route provider
 * implementations.
 *
 * @author Uwe Jäger
 * @author David Buchmann <mail@davidbu.ch>
 */
abstract class DoctrineProvider
{
    /**
     * If this is null, the manager registry will return the default manager.
     */
    protected ?string $managerName = null;

    protected ManagerRegistry $managerRegistry;

    /**
     * Class name of the object class to find, null for PHPCR-ODM as it can
     * determine the class on its own.
     */
    protected ?string $className;

    /**
     * Limit to apply when calling getRoutesByNames() with null.
     */
    protected ?int $routeCollectionLimit;

    public function __construct(ManagerRegistry $managerRegistry, ?string $className = null)
    {
        $this->managerRegistry = $managerRegistry;
        $this->className = $className;
    }

    /**
     * Set the object manager name to use for this loader. If not set, the
     * default manager as decided by the manager registry will be used.
     */
    public function setManagerName(?string $managerName): void
    {
        $this->managerName = $managerName;
    }

    /**
     * Set the limit to apply when calling getAllRoutes().
     *
     * Setting the limit to null means no limit is applied.
     */
    public function setRouteCollectionLimit(?int $routeCollectionLimit = null): void
    {
        $this->routeCollectionLimit = $routeCollectionLimit;
    }

    /**
     * Get the object manager named $managerName from the registry.
     */
    protected function getObjectManager(): ObjectManager
    {
        return $this->managerRegistry->getManager($this->managerName);
    }
}
