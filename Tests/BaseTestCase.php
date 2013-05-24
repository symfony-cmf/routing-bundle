<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests;

/**
 * Base class for any unit tests that might need it.
 * (Not to be confused with Tests\Functional\BaseTestCase)
 */
class BaseTestCase extends \PHPUnit_Framework_Testcase
{
    /**
     * Returns an object manager mock that is capable of returning
     * one or more documents for find().
     *
     * $documents should be an associative array of
     *      searchString => document (mock object)
     *
     * @param array $documents
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManager(array $documents)
    {
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->returnCallback(
                    function ($class, $searchString) use ($documents) {
                        return $documents[$searchString];
                    }
                )
            );

        return $objectManager;
    }

    /**
     * Returns a manager registry mock that is capable of returning
     * one or more object managers for getManager().
     *
     * $objectManagers should be an associative array of
     *      managerName => manager (mock object)
     *
     * @param array $objectManagers
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected function getManagerRegistry(array $objectManagers)
    {
        $managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will(
                $this->returnCallback(
                    function ($name) use ($objectManagers) {
                        return $objectManagers[$name];
                    }
                )
            );

        return $managerRegistry;
    }
}
