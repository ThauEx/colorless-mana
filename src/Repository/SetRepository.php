<?php

namespace App\Repository;

use App\Entity\Set;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Set|null find($id, $lockMode = null, $lockVersion = null)
 * @method Set|null findOneBy(array $criteria, array $orderBy = null)
 * @method Set[]    findAll()
 * @method Set[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Set::class);
    }

    /** @return array<string, string> release date keyed by lowercased set code */
    public function getReleaseDatesByCode(): array
    {
        $rows = $this->createQueryBuilder('s')
            ->select('s.code, s.releaseDate')
            ->getQuery()
            ->getArrayResult()
        ;

        $dates = [];
        foreach ($rows as $row) {
            $dates[$row['code']] = $row['releaseDate'];
        }

        return $dates;
    }
}
