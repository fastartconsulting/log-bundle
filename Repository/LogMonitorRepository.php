<?php

namespace FAC\LogBundle\Repository;

use Doctrine\ODM\MongoDB\MongoDBException;
use FAC\LogBundle\Document\LogMonitor;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LogMonitorRepository extends DocumentRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        parent::__construct(LogMonitor::class, $container);
    }

    public function findErrors($max_count) {
        $qb = $this->dm->createQueryBuilder('FAC\LogBundle:LogMonitor');
        $qb->field('count')->gte($max_count);
        $qb->field('level')->lte(500);
        $list = $qb->getQuery()->execute();

        return $list;
    }

    public function findErrorsUrgency($max_count) {
        $qb = $this->dm->createQueryBuilder('FAC\LogBundle:LogMonitor');
        $qb->field('count')->gte($max_count);
        $qb->field('level')->equals(500);
        $list = $qb->getQuery()->execute();

        return $list;
    }
}