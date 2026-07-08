<?php

namespace App\DataProvider;

use App\Entity\CollectedCard;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CollectionStatsProvider
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire(service: 'collection_stats.cache')]
        private readonly CacheInterface $cache,
    ) {
    }

    public function getCardSupertypes(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_supertypes_' . $id, function () use ($user) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('ca.supertypes')
                ->distinct()
                ->join('c.card', 'ca')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $rows = array_column($qb->getQuery()->getArrayResult(), 'supertypes');
            $types = empty($rows) ? [] : array_unique(array_merge(...$rows));
            natsort($types);

            return array_values($types);
        });
    }

    public function getCardTypes(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_types_' . $id, function () use ($user) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('ca.types')
                ->distinct()
                ->join('c.card', 'ca')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $rows = array_column($qb->getQuery()->getArrayResult(), 'types');
            $types = empty($rows) ? [] : array_unique(array_merge(...$rows));
            natsort($types);

            return array_values($types);
        });
    }

    public function getCardSubtypes(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_subtypes_' . $id, function () use ($user) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('ca.subtypes')
                ->distinct()
                ->join('c.card', 'ca')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $rows = array_column($qb->getQuery()->getArrayResult(), 'subtypes');
            $types = empty($rows) ? [] : array_unique(array_merge(...$rows));
            natsort($types);

            return array_values($types);
        });
    }

    public function getCardColors(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_colors_' . $id, function () use ($user) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('ca.colors')
                ->distinct()
                ->join('c.card', 'ca')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $rows = array_column($qb->getQuery()->getArrayResult(), 'colors');

            return empty($rows) ? [] : array_unique(array_merge(...$rows));
        });
    }

    public function getCardRarities(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_rarities_' . $id, function () use ($user) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('ca.rarity')
                ->distinct()
                ->join('c.card', 'ca')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            return array_column($qb->getQuery()->getScalarResult(), 'rarity');
        });
    }

    public function getCardSetCodes(?UserInterface $user = null, array $sets = []): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_set_codes_' . $id, function () use ($user, $sets) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('ca.setCode')
                ->distinct()
                ->join('c.card', 'ca')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $setCodes = [];
            foreach (array_column($qb->getQuery()->getScalarResult(), 'setCode') as $setCode) {
                if (!isset($sets[$setCode])) {
                    continue;
                }
                $setCodes[$sets[$setCode]->getName() . ' (' . $setCode . ')'] = $setCode;
            }

            ksort($setCodes);

            return $setCodes;
        });
    }

    public function getCardLanguages(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_languages_' . $id, function () use ($user) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('c.language')
                ->distinct()
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $languages = [];
            foreach (array_column($qb->getQuery()->getScalarResult(), 'language') as $language) {
                $languages['language.' . $language] = $language;
            }

            ksort($languages);

            return $languages;
        });
    }

    /** @return string[] distinct finishes in the collection, nonfoil first */
    public function getCardFinishes(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_finishes_' . $id, function () use ($user) {
            $qb = $this->em
                ->getRepository(CollectedCard::class)
                ->createQueryBuilder('c')
                ->select('c.finish')
                ->distinct()
                ->orderBy('c.finish')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $finishes = array_column($qb->getQuery()->getScalarResult(), 'finish');

            usort($finishes, static fn(string $a, string $b) => ($a === CollectedCard::FINISH_NONFOIL ? 0 : 1) <=> ($b === CollectedCard::FINISH_NONFOIL ? 0 : 1)
                ?: strcmp($a, $b));

            return $finishes;
        });
    }

    public function getCardQuantities(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_quantities_' . $id, function () use ($user) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select("SUM(CASE WHEN c.finish = 'nonfoil' THEN c.quantity ELSE 0 END) as nonFoil")
                ->addSelect("SUM(CASE WHEN c.finish != 'nonfoil' THEN c.quantity ELSE 0 END) as foil")
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            return $qb->getQuery()->getOneOrNullResult();
        });
    }

    public function getCardTotalPrices(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_total_prices_' . $id, function () use ($user) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->join('c.card', 'ca')
                ->select("SUM(CASE WHEN c.finish = 'nonfoil' THEN c.quantity * ca.cardmarketPrices.priceNormal ELSE 0 END) as nonFoil")
                ->addSelect("SUM(CASE WHEN c.finish != 'nonfoil' THEN c.quantity * ca.cardmarketPrices.priceFoil ELSE 0 END) as foil")
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            return $qb->getQuery()->getOneOrNullResult();
        });
    }

    public function reset(UserInterface $user): void
    {
        $keys = [
            'card_types_',
            'card_subtypes_',
            'card_colors_',
            'card_rarities_',
            'card_set_codes_',
            'card_languages_',
            'card_finishes_',
            'card_quantities_',
            'card_total_prices_',
        ];

        foreach ($keys as $key) {
            try {
                $this->cache->delete($key . $user->getId());
            } catch (InvalidArgumentException) {
            }
        }

    }
}
