<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Migrator;

use Doctrine\Bundle\PHPCRBundle\Migrator\MigratorInterface;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\Util\NodeHelper;
use PHPCR\SessionInterface;

class Route implements MigratorInterface
{
    /**
     * @var PHPCR\SessionInterface
     */
    protected $session;

    /*
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    protected $basepath;

    public function __construct(ManagerRegistry $registry, $basepath)
    {
        $this->session = $registry->getConnection();
        $this->basepath = $basepath;
    }

    public function init(SessionInterface $session, OutputInterface $output)
    {
        $this->session = $session;
        $this->output = $output;
    }

    public function migrate($path = '/', $depth = -1)
    {
        $workspace = $this->session->getWorkspace();
        $queryManager = $workspace->getQueryManager();

        $sql = "SELECT * FROM [nt:unstructured]
            WHERE [nt:unstructured].[phpcr:class] = 'Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route'
            AND ISDESCENDANTNODE('$this->basepath')";

        $query = $queryManager->createQuery($sql, 'JCR-SQL2');
        $queryResult = $query->execute();

        foreach ($queryResult->getNodes() as $path => $node) {
            $node->setProperty('phpcr:class', 'Symfony\Cmf\Bundle\RoutingBundle\Document\Route');
        }

        $this->session->save();
        return 0;
    }
}