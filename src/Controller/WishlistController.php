<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CollectedCard;
use App\Entity\User;
use App\Entity\Wishlist;
use App\Form\WishlistSearchType;
use App\Form\WishlistType;
use App\Repository\WishlistRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wishlist', name: 'wishlist_')]
class WishlistController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('', name: 'index')]
    public function list(WishlistRepository $repository): Response
    {
        return $this->render('wishlist/list.html.twig', [
            'wishlist' => $repository->findAllEntries($this->getUser()),
        ]);
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('/search', name: 'search')]
    public function search(Request $request): Response
    {
        $form = $this->createForm(WishlistSearchType::class, $request->request->all());
        $form->handleRequest($request);
        $results = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $em = $this->doctrine->getManager();
            $repo = $em->getRepository(Card::class);
            $qb = $repo->createQueryBuilder('c');
            $qb->select('c');

            $conditions = [];
            foreach (Card::CARD_LANGUAGES as $index => $lang) {
                $conditions[] = $qb->expr()->like("c.{$lang}Texts.name", ':term' . $index);
                $qb->setParameter('term' . $index, '%' . $data['name'] . '%');
            }
            $qb->where($qb->expr()->orX(...$conditions));

            $qb
                ->groupBy('c.scryfallOracleId')
                ->orderBy('c.frameVersion', 'desc')
            ;

            $results = $qb->getQuery()->getResult();
        }

        return $this->render('wishlist/search.html.twig', [
            'form'    => $form->createView(),
            'results' => $results,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/add/{scryfallOracleId}', name: 'add')]
    public function add(ManagerRegistry $doctrine, Request $request, string $scryfallOracleId): Response
    {
        $em = $doctrine->getManager();
        $repo = $em->getRepository(Card::class);
        $card = $repo->findOneBy(['scryfallOracleId' => $scryfallOracleId]);

        $wishlist = new Wishlist();

        if ($language = $request->query->get('language')) {
            $wishlist->setLanguages([$language]);
        }

        if ($cardId = $request->query->get('card')) {
            $card = $repo->findOneBy(['id' => $cardId]);
            $wishlist->addCard($card);
        }

        $form = $this->createForm(WishlistType::class, $wishlist, [
            'scryfall_oracle_id' => $scryfallOracleId,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wishlist
                ->setScryfallOracleId(Uuid::fromString($scryfallOracleId))
                ->setUser($this->getUser())
            ;
            $this->addFlash('info', 'Karte erfolgreich hinzugefügt.');

            $em->persist($wishlist);
            $em->flush();

            $this->redirectToRoute('wishlist_index');
        }

        return $this->render('wishlist/add.html.twig', [
            'card' => $card,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/match/{id}', name: 'match')]
    public function match(string $id): Response
    {
        $em = $this->doctrine->getManager();
        $wishlistRepo = $em->getRepository(Wishlist::class);
        $collectedCardRepo = $em->getRepository(CollectedCard::class);
        $cardRepo = $em->getRepository(Card::class);
        $userRepo = $em->getRepository(User::class);

        $wishlistItem = $wishlistRepo->findOneBy([
            'id'   => $id,
            'user' => $this->getUser(),
        ]);

        $followingUsers = $userRepo->findFollowing($this->getUser());

        $card = $cardRepo->findOneBy(['scryfallOracleId' => $wishlistItem->getScryfallOracleId()]);
        $cards = $collectedCardRepo->findMatchesForWishlist($wishlistItem, $followingUsers);

        return $this->render('wishlist/match.html.twig', [
            'card'     => $card,
            'wishlist' => $wishlistItem,
            'cards'    => $cards,
        ]);
    }
}
