<?php

namespace App\DataProvider;

use App\Entity\CollectedCard;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CollectionStatsProvider
{
    private CacheInterface $cache;

    public function __construct(private readonly EntityManagerInterface $em, CacheInterface $collectionStatsCache) {
        $this->cache = $collectionStatsCache;
    }

    public function getCardSupertypes(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_supertypes_' . $id, function () use ($user) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select('c')
                ->distinct()
                ->join('c.card', 'ca')
                ->addSelect('ca')
                ->groupBy('ca.supertypes', 'c.id', 'ca.id')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $results = $qb->getQuery()->getResult();

            $types = [];
            /** @var CollectedCard $result */
            foreach ($results as $result) {
                $types[] = $result->getCard()->getSupertypes();
            }

            $types = array_merge(...$types);
            $types = array_unique($types);
            natsort($types);

            return array_values($types);
        });
    }
//$cache->invalidateTags(['tag_1', 'tag_3']);
    public function getCardTypes(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_types_' . $id, function (ItemInterface $item) use ($user) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select('c')
                ->distinct()
                ->join('c.card', 'ca')
                ->addSelect('ca')
                ->groupBy('ca.types', 'c.id', 'ca.id')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $results = $qb->getQuery()->getResult();

            $types = [];
            /** @var CollectedCard $result */
            foreach ($results as $result) {
                $types[] = $result->getCard()->getTypes();
            }

            $types = array_merge(...$types);
            $types = array_unique($types);
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
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select('c')
                ->distinct()
                ->join('c.card', 'ca')
                ->addSelect('ca')
                ->groupBy('ca.subtypes', 'c.id', 'ca.id')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $results = $qb->getQuery()->getResult();

            $types = [];
            /** @var CollectedCard $result */
            foreach ($results as $result) {
                $types[] = $result->getCard()->getSubtypes();
            }

            $types = array_merge(...$types);
            $types = array_unique($types);
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
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select('c')
                ->distinct()
                ->join('c.card', 'ca')
                ->addSelect('ca')
                ->groupBy('ca.colors', 'c.id', 'ca.id')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $results = $qb->getQuery()->getResult();

            $types = [];
            /** @var CollectedCard $result */
            foreach ($results as $result) {
                $types[] = $result->getCard()->getColors();
            }

            $types = array_merge(...$types);

            return array_unique($types);
        });
    }

    public function getCardRarities(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_rarities_' . $id, function () use ($user) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c');
            $qb
                ->select('c')
                ->distinct()
                ->join('c.card', 'ca')
                ->addSelect('ca')
                ->groupBy('ca.rarity', 'c.id', 'ca.id')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $results = $qb->getQuery()->getResult();

            return array_map(static function (CollectedCard $collectedCard) {
                return $collectedCard->getCard()->getRarity();
            }, $results);
        });
    }

    public function getCardRarities2(UserInterface $user): array
    {
//        return $this->cache->get('card_rarities_' . $user->getId(), function () use ($user) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c');
            $qb
                ->select('c')
                ->addSelect($qb->expr()->count('ca.rarity'))
                ->addSelect('ca.rarity')
                ->distinct()
                ->where('c.user = :user')
                ->join('c.card', 'ca')
                ->addSelect('ca')
                ->groupBy('ca.rarity')
                ->setParameter('user', $user)
            ;

            $results = $qb->getQuery()->getResult();
dd($results);

            return array_map(static function (CollectedCard $collectedCard) {
                return $collectedCard->getCard()->getRarity();
            }, $results);
//        });
    }

    public function getCardSetCodes(?UserInterface $user = null, array $sets = []): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_set_codes_' . $id, function () use ($user, $sets) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select('c')
                ->distinct()
                ->join('c.card', 'ca')
                ->addSelect('ca')
                ->groupBy('c.edition', 'c.id', 'ca.id')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $results = $qb->getQuery()->getResult();

            $setCodes = [];
            /** @var CollectedCard $result */
            foreach ($results as $result) {
                $setCode = $result->getCard()->getSetCode();
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
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select('c')
                ->distinct()
                ->groupBy('c.language', 'c.id')
            ;

            if ($user) {
                $qb
                    ->where('c.user = :user')
                    ->setParameter('user', $user)
                ;
            }

            $results = $qb->getQuery()->getResult();

            $languages = [];
            /** @var CollectedCard $result */
            foreach ($results as $result) {
                $language = $result->getLanguage();
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

            usort($finishes, static function (string $a, string $b) {
                return ($a === CollectedCard::FINISH_NONFOIL ? 0 : 1) <=> ($b === CollectedCard::FINISH_NONFOIL ? 0 : 1)
                    ?: strcmp($a, $b);
            });

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
            } catch (InvalidArgumentException $e) {
            }
        }

    }
}
