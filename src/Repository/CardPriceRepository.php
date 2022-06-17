<?php

namespace App\Repository;

use App\Entity\CardPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CardPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardPrice[]    findAll()
 * @method CardPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardPrice::class);
    }

    // /**
    //  * @return CardPrice[] Returns an array of CardPrice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CardPrice
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
