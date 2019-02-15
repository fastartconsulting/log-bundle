<?php

namespace LogBundle\Repository;

use Doctrine\ODM\MongoDB\MongoDBException;
use LogBundle\Document\LogMonitor;
use Schema\SchemaDocumentRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LogMonitorRepository extends SchemaDocumentRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        parent::__construct(LogMonitor::class, $container);
    }

    public function findErrors($max_count) {
        $qb = $this->dm->createQueryBuilder('LogBundle:LogMonitor');
        $qb->field('count')->gte($max_count);
        $qb->field('level')->lte(500);
        $list = $qb->getQuery()->execute();

        return $list;
    }

    public function findErrorsUrgency($max_count) {
        $qb = $this->dm->createQueryBuilder('LogBundle:LogMonitor');
        $qb->field('count')->gte($max_count);
        $qb->field('level')->equals(500);
        $list = $qb->getQuery()->execute();

        return $list;
    }
}