<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Migrator;

use Doctrine\Bundle\PHPCRBundle\Migrator\MigratorInterface;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;

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

    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected $dm;

    protected $basepath;

    //protected $dataDir;

    public function __construct(ManagerRegistry $registry, $basepath)
    {
        $this->dm = $registry->getManager();
        $this->session = $registry->getConnection();
        $this->basepath = $basepath;
    }

    public function init(SessionInterface $session, OutputInterface $output)
    {
        $this->session = $session;
        $this->output = $output;
    }

    protected function migrateTree($root)
    {
        foreach ($root as $child) {
            if( $child->getPropertyValue('phpcr:class') == 'Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route')
            {
               $child->setProperty('phpcr:class', 'Symfony\Cmf\Bundle\RoutingBundle\Document\Route');
            }
            if( $child->hasNodes() )
            {
                $this->migrateTree($child);
            }
	}
        return true;
    }

    public function migrate($path = '/', $depth = -1)
    {

        NodeHelper::createPath($this->session, preg_replace('#/[^/]*$#', '', $this->basepath));


        $root = $this->session->getNode($this->basepath);
        $migrated = $this->migrateTree($root);
        if($migrated)
        {
            $this->session->save();
        }

        return 0;
    }

}