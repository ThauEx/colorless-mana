<?php

namespace App\Repository;

use App\Doctrine\UuidEncoder;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private UuidEncoder $uuidEncoder;

    public function __construct(ManagerRegistry $registry, UuidEncoder $uuidEncoder)
    {
        parent::__construct($registry, User::class);

        $this->uuidEncoder = $uuidEncoder;
    }

    public function findFollowing(UserInterface $user)
    {
        $qb = $this->createQueryBuilder('u');

        // The follower system is not ready yet, so return all other users instead
        return $qb
            ->where('u.id != :user')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult()
        ;
//        return $qb
//            ->where('u.follower = :user')
//            ->setParameter('user', $user->getId())
//            ->getQuery()
//            ->getResult()
//        ;
    }

    public function getFollowingAndMyselfQueryBuilder(UserInterface $user)
    {
        return $this->createQueryBuilder('u');
    }

    public function findByHashes(array $userHashes): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id')
        ;

        $conditions = [];
        foreach ($userHashes as $index => $user) {
            $uuid = $this->uuidEncoder->decode($user);
            $conditions[] = $qb->expr()->eq('u.uuid', ':user' . $index);
            $qb->setParameter('user' . $index, $uuid);
        }
        $qb->where($qb->expr()->orX(...$conditions));

        $userIds = $qb->getQuery()->getResult();

        return array_map(static function ($user) {
            return $user['id'];
        }, $userIds);
    }
}
