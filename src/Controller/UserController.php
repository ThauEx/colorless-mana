<?php

namespace App\Controller;

use App\Doctrine\UuidEncoder;
use App\Entity\User;
use App\Entity\UserSettings;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/u/{identifier}', name: 'user_')]
class UserController extends AbstractController
{
    use CollectionSearchTrait;

    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly UuidEncoder $uuidEncoder
    ) {}

    #[Route('/collection', name: 'public_collection')]
    public function publicCollection(Request $request, string $identifier): Response
    {
        $uuid = $this->uuidEncoder->decode($identifier);
        $em = $this->doctrine->getManager();
        $repo = $em->getRepository(User::class);

        /** @var User $user */
        $user = $repo->createQueryBuilder('u')
            ->select('u, cc, ca')
            ->leftJoin('u.collectedCards', 'cc')
            ->leftJoin('cc.card', 'ca')
            ->where('u.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if (in_array($user->getSettings()->get(UserSettings::CAN_SEE_COLLECTION), [UserSettings::NOBODY, UserSettings::FOLLOWERS, UserSettings::FOLLOWING_FOLLOWERS], true)) {
            throw $this->createAccessDeniedException('Private collection');
        }

        [$form, $collection, $pager] = $this->getCollection($request, [$user], false, 16);

        return $this->render('cards/public.html.twig', [
            'user'       => $user,
            'collection' => $collection,
            'pager'      => $pager,
            'form'       => $form->createView(),
        ]);
    }
}
