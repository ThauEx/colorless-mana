<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\CollectedCard;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CollectedCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method CollectedCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method CollectedCard[]    findAll()
 * @method CollectedCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollectedCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollectedCard::class);
    }

    public function findMatchesForWishlist(Wishlist $wishlist, array $users)
    {
        $qb = $this->createQueryBuilder('cc');

        $qb
            ->select('c, cc')
            ->leftJoin('cc.card', 'c')
            ->where($qb->expr()->in('cc.user', ':users'))
            ->andWhere($qb->expr()->eq('c.scryfallOracleId', ':scryfallOracleId'))
            ->setParameter('users', $users)
            ->setParameter('scryfallOracleId', $wishlist->getScryfallOracleId())
        ;

        if (!empty($wishlist->getLanguages())) {
            $conditions = [];
            foreach ($wishlist->getLanguages() as $language) {
                $conditions[] = $qb->expr()->eq('cc.language', ':lang' . $language);
                $qb->setParameter('lang' . $language, $language);
            }
            $qb->andWhere($qb->expr()->orx(...$conditions));
        }

        return $qb->getQuery()->getResult();
    }

    public function findWithSearchParams(array $searchParams, array $users = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, ca')
            ->leftJoin('c.card', 'ca')
            ->where('c.user IN (:users)')
            ->orderBy('ca.setCode', 'asc')
            ->addOrderBy('ca.number', 'asc')
        ;

        if (!empty($searchParams['term'])) {
            $conditions = [];

            foreach (Card::CARD_LANGUAGES as $index => $lang) {
                $conditions[] = $qb->expr()->like("ca.{$lang}Texts.name", ':term' . $index);
                $qb->setParameter('term' . $index, '%' . $searchParams['term'] . '%');
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($searchParams['supertypes'])) {
            $conditions = [];

            foreach ($searchParams['supertypes'] as $index => $supertype) {
                $conditions[] = $qb->expr()->like('ca.supertypes', ':supertype' . $index);
                $qb->setParameter('supertype' . $index, '%"' . $supertype . '"%');
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($searchParams['types'])) {
            $conditions = [];

            foreach ($searchParams['types'] as $index => $type) {
                $conditions[] = $qb->expr()->like('ca.types', ':type' . $index);
                $qb->setParameter('type' . $index, '%"' . $type . '"%');
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($searchParams['subtypes'])) {
            $conditions = [];

            foreach ($searchParams['subtypes'] as $index => $subtype) {
                $conditions[] = $qb->expr()->like('ca.subtypes', ':subtype' . $index);
                $qb->setParameter('subtype' . $index, '%"' . $subtype . '"%');
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($searchParams['colors'])) {
            if ($searchParams['colorMatch'] === 'exact') {
                $parameter = 'a:' . count($searchParams['colors']) . ':{';
                $matchCount = 0;
                // To match the colors, the exact order is required
                // TODO: Check if there is a better way to search
                foreach (['B', 'G', 'R', 'U', 'W'] as $color) {
                    if (in_array($color, $searchParams['colors'], true)) {
                        $parameter .= 'i:' . $matchCount . ';s:1:"' . $color . '";';
                        $matchCount++;
                    }
                }
                $parameter .= '}';

                $qb->andWhere($qb->expr()->eq('ca.colors', ':color'));
                $qb->setParameter('color', $parameter);
            }

            if ($searchParams['colorMatch'] === 'include') {
                $parameter = '%';
                // To match the colors, the exact order is required
                foreach (['B', 'G', 'R', 'U', 'W'] as $color) {
                    if (in_array($color, $searchParams['colors'], true)) {
                        $parameter .= '"' . $color . '"%';
                    }
                }

                $qb->andWhere($qb->expr()->like('ca.colors', ':color'));
                $qb->setParameter('color', $parameter);
            }

            if ($searchParams['colorMatch'] === 'atMost') {
                $conditions = [];
                foreach ($searchParams['colors'] as $index => $color) {
                    $conditions[] = $qb->expr()->like('ca.colors', ':color' . $index);
                    $qb->setParameter('color' . $index, '%"' . $color . '"%');
                }

                $qb->andWhere($qb->expr()->orX(...$conditions));
            }
        }

        if (!empty($searchParams['rarities'])) {
            $conditions = [];

            foreach ($searchParams['rarities'] as $index => $rarity) {
                $conditions[] = $qb->expr()->eq('ca.rarity', ':rarity' . $index);
                $qb->setParameter('rarity' . $index, $rarity);
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($searchParams['setCodes'])) {
            $conditions = [];

            foreach ($searchParams['setCodes'] as $index => $setCode) {
                $conditions[] = $qb->expr()->eq('ca.setCode', ':setCode' . $index);
                $qb->setParameter('setCode' . $index, $setCode);
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($searchParams['languages'])) {
            $conditions = [];

            foreach ($searchParams['languages'] as $index => $language) {
                $conditions[] = $qb->expr()->eq('c.language', ':language' . $index);
                $qb->setParameter('language' . $index, $language);
            }
            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($searchParams['isNormal'])) {
            if ($searchParams['isNormal'] === 'y') {
                $qb->andWhere($qb->expr()->gt('c.nonFoilQuantity', 0));
            } elseif ($searchParams['isNormal'] === 'n') {
                $qb->andWhere($qb->expr()->eq('c.nonFoilQuantity', 0));
            }
        }

        if (!empty($searchParams['isFoil'])) {
            if ($searchParams['isFoil'] === 'y') {
                $qb->andWhere($qb->expr()->gt('c.foilQuantity', 0));
            } elseif ($searchParams['isFoil'] === 'n') {
                $qb->andWhere($qb->expr()->eq('c.foilQuantity', 0));
            }
        }

        if (!empty($searchParams['order'])) {
            $direction = $searchParams['dir'] === 'desc' ? 'desc' : 'asc';
            $sort = match ($searchParams['order']) {
                'id'           => 'c.id',
                'name'         => 't.name',
                'quantity'     => 'c.nonFoilQuantity',
                'foilQuantity' => 'c.foilQuantity',
                'rarity'       => 'ca.rarity',
                'setCode'      => 'ca.setCode',
                'language'     => 'c.language',
                'manaCost'     => 'ca.manaCost',
                'type'         => 't.type',
                'price'        => 'ca.cardmarketPrices.priceNormal',
                'foilPrice'    => 'ca.cardmarketPrices.priceFoil',
                default        => '',
            };

            if ($sort === '') {
                $qb
                    ->orderBy('ca.setCode', $direction)
                    ->addOrderBy('ca.number', $direction)
                ;
            } else {
                $qb->orderBy($sort, $direction);
            }
        }

        $qb->setParameter('users', $users);

        return $qb;
    }

    public function findByNamesAndUsers(array $names, array $users = [])
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, ca')
            ->leftJoin('c.card', 'ca')
        ;

        foreach ($names as $index => $name) {
            $conditions = [];

            foreach (Card::CARD_LANGUAGES as $index2 => $lang) {
                $conditions[] = $qb->expr()->eq("ca.{$lang}Texts.name", ':name' . $index . $index2);
                $qb->setParameter('name' . $index . $index2, $name);
            }
            $qb->orWhere($qb->expr()->orX(...$conditions));
        }

        if (!empty($users)) {
            $qb
                ->andWhere('c.user IN (:users)')
                ->setParameter('users', $users)
            ;
        }

        return $qb->getQuery()->getResult();
        return $qb;
    }
}
