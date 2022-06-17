<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Wishlist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wishlist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wishlist[]    findAll()
 * @method Wishlist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function findAllEntries(UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('w');
        $qb
            ->select('w, ca')
            ->join(Card::class, 'ca', 'with', 'ca.scryfallOracleId = w.scryfallOracleId')
            ->where('w.user = :user')
            ->setParameter('user', $user)
        ;

        $results = $qb->getQuery()->getResult();
        $wishlist = [];
        $prevWishlist = null;
        foreach ($results as $result) {
            if ($result instanceof Wishlist) {
                $prevWishlist = $result;
                $wishlist[] = $prevWishlist;
            } else {
                $prevWishlist->addScryfallOracleIdCards($result);
            }
        }

        return $wishlist;
    }
}
