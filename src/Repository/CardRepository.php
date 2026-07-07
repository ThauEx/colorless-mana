<?php

namespace App\Repository;

use App\Doctrine\Query\MatchAgainstFunction;
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

            $results[$key][] = ['setCode' => $result['setCode'], 'number' => $result['number']];
        }

        return $results;
    }

    public function findByScryfallId(string $scryfallId)
    {
        return $this->findBy([
            'id' => $scryfallId,
        ]);
    }

    public function findByName(string $name)
    {
        $term = MatchAgainstFunction::toBooleanSearchTerm($name);

        if ($term === '') {
            return [];
        }

        // The FULLTEXT index also covers scryfallOracleId; MATCH() must name
        // every indexed column to be able to use it
        $nameColumns = implode(', ', array_map(static fn (string $lang) => "c.{$lang}Texts.name", Card::CARD_LANGUAGES));

        return $this
            ->createQueryBuilder('c')
            ->where("MATCH_AGAINST(c.scryfallOracleId, {$nameColumns}, :term) > 0")
            ->setParameter('term', $term)
            ->getQuery()
            ->getResult()
        ;
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
            // Foil-only variants use a "★" suffix; match both so users do not
            // have to type the star
            $number = rtrim(ltrim(trim($number), '0'), '★');

            $qb
                ->andWhere($qb->expr()->in('c.number', ':numbers'))
                ->setParameter('numbers', [$number, $number . '★'])
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
