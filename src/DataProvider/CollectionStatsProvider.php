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
                ->groupBy('ca.supertypes')
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
                ->groupBy('ca.types')
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
                ->groupBy('ca.subtypes')
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
                ->groupBy('ca.colors')
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
                ->groupBy('ca.rarity')
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
                ->groupBy('c.edition')
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
                ->groupBy('c.language')
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

    public function getCardQuantities(?UserInterface $user = null): array
    {
        $id = 'all';
        if ($user) {
            $id = $user->getId();
        }

        return $this->cache->get('card_quantities_' . $id, function () use ($user) {
            $repo = $this->em->getRepository(CollectedCard::class);
            $qb = $repo->createQueryBuilder('c')
                ->select('SUM(c.nonFoilQuantity) as nonFoil')
                ->addSelect('SUM(c.foilQuantity) as foil')
                ->distinct()
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
                ->select('SUM(c.nonFoilQuantity * ca.cardmarketPrices.priceNormal) as nonFoil')
                ->addSelect('SUM(c.foilQuantity * ca.cardmarketPrices.priceFoil) as foil')
                ->distinct()
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
