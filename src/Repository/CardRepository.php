<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function findOneByNameAndSetCode(string $name, string $setCode): ?Card
    {
        $qb = $this->createQueryBuilder('c');

        try {
            $qb->andWhere('c.setCode = :setCode');

            $conditions = [];

            foreach (Card::CARD_LANGUAGES as $index => $lang) {
                $conditions[] = $qb->expr()->eq("c.{$lang}Texts.name", ':name' . $index);
                $qb->setParameter('name' . $index, $name);
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));

            return $qb
                ->setParameter('setCode', $setCode)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $exception) {
            return null;
        }
    }

    public function findSetCodeAndNumberByScryfallOracleId(string $scryfallOracleId)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('c.setCode', 'c.number')
            ->where($qb->expr()->eq('c.scryfallOracleId', ':id'))
            ->setParameter('id', $scryfallOracleId)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findSetCodeAndNumberByScryfallOracleIds(array $scryfallOracleIds): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('c.scryfallOracleId', 'c.setCode', 'c.number')
            ->where($qb->expr()->in('c.scryfallOracleId', ':ids'))
            ->setParameter('ids', $scryfallOracleIds)
        ;

        $results = [];

        foreach ($qb->getQuery()->getResult() as $result) {
            $key = (string) $result['scryfallOracleId'];
            if (!array_key_exists($key, $results)) {
                $results[$key] = [];
            }

            $results[$key][] = $result;
        }

        return $results;
    }

    public function findByScryfallId(string $scryfallId)
    {
        return $this->findBy([
            'scryfallId' => $scryfallId,
        ]);
    }

    public function findByName(string $name)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('c');

        foreach (Card::CARD_LANGUAGES as $lang) {
            $qb
                ->orWhere($qb->expr()->like("c.{$lang}Texts.name", ':term' . $lang))
                ->setParameter('term' . $lang, '%' . $name . '%')
            ;
        }


        return $qb->getQuery()->getResult();
    }

    public function findBySetCodeAndNumber(string $setCode = '', string $number = '')
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c')
            ->where('c.side is NULL or c.side = :side')
            ->setParameter('side', 'a')
            ->orderBy('c.number')
            ->setMaxResults(20)
        ;

        if (!empty($setCode)) {
            $qb
                ->andWhere('c.setCode = :setCode')
                ->setParameter('setCode', $setCode)
            ;
        }

        if (!empty($number)) {
            $qb
                ->andWhere('c.number = :number')
                ->setParameter('number', ltrim($number, '0'))
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
