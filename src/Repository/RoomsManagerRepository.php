<?php

namespace App\Repository;

use App\Entity\RoomsManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RoomsManager|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoomsManager|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoomsManager[]    findAll()
 * @method RoomsManager[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomsManagerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomsManager::class);
    }

    // /**
    //  * @return RoomsManager[] Returns an array of RoomsManager objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RoomsManager
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
