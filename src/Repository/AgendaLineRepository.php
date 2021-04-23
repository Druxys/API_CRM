<?php

namespace App\Repository;

use App\Entity\AgendaLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaLine[]    findAll()
 * @method AgendaLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaLine::class);
    }

    // /**
    //  * @return AgendaLine[] Returns an array of AgendaLine objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AgendaLine
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
