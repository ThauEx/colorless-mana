<?php

namespace App\Controller;

use App\DataProvider\CollectionStatsProvider;
use App\DataProvider\MtgDataProvider;
use App\Entity\Card;
use App\Entity\CollectedCard;
use App\Entity\User;
use App\Form\CardSearchType;
use App\Form\CollectionSearchType;
use App\Form\MultiSearchType;
use App\Helper\CollectionManager;
use ArrayIterator;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/collection', name: 'collection_')]
class CollectionController extends AbstractController
{
    use CollectionSearchTrait;

    public function __construct(
        private readonly MtgDataProvider $mtgDataProvider,
        private readonly CollectionStatsProvider $collectionStatsProvider,
        private readonly ManagerRegistry $doctrine
    ) {}

    #[Security("is_granted('ROLE_USER')")]
    #[Route('', name: 'index')]
    public function list(Request $request): Response
    {
        [$form, $collection, $pager, $printings] = $this->getCollection(request: $request, users: [$this->getUser()], includePrintings: true);

        return $this->render('cards/list.html.twig', [
            'collection' => $collection,
            'pager'      => $pager,
            'form'       => $form->createView(),
            'printings'  => $printings,
            'meta'       => [
                'types'      => $this->collectionStatsProvider->getCardTypes($this->getUser()),
                'subTypes'   => $this->collectionStatsProvider->getCardSubtypes($this->getUser()),
                'colors'     => $this->collectionStatsProvider->getCardColors($this->getUser()),
                'rarities'   => $this->collectionStatsProvider->getCardRarities($this->getUser()),
//                'sets'       => $this->collectionStatsProvider->getCardSetCodes($this->getUser()),
                'languages'  => $this->collectionStatsProvider->getCardLanguages($this->getUser()),
                'quantities' => $this->collectionStatsProvider->getCardQuantities($this->getUser()),
                'prices'     => $this->collectionStatsProvider->getCardTotalPrices($this->getUser()),
            ],
        ]);
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('/import', name: 'import')]
    public function import(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add(
                'updateOnly',
                CheckboxType::class,
                [
                    'label'    => 'form.import.update_only',
                    'required' => false,
                ]
            )
            ->add(
                'csv',
                FileType::class,
                [
                    'label' => false,
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.import.submit',
                    'attr'  => [
                        'class' => 'btn btn-primary',
                    ],
                ]
            )
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $data['csv'];

            // TODO: Validation
            $notFound = $this->mtgDataProvider->importCsv($uploadedFile->getPathname(), $data['updateOnly']);
            $this->collectionStatsProvider->reset($this->getUser());

            if (empty($notFound)) {
                $this->addFlash('info', 'cards.import.success');

                return $this->redirectToRoute('collection_index');
            }

            return $this->render('cards/import_not_found.html.twig', [
                'not_found' => $notFound,
            ]);
        }

        return $this->render('cards/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('/delete/{id}', name: 'delete')]
    public function removeCard(string $id): RedirectResponse
    {
        $em = $this->doctrine->getManager();
        $repo = $em->getRepository(CollectedCard::class);
        $collectedCard = $repo->findOneBy([
            'id'   => $id,
            'user' => $this->getUser(),
        ]);

        if (!$collectedCard) {
            throw $this->createNotFoundException();
        }

        $em->remove($collectedCard);
        $em->flush();

        $this->collectionStatsProvider->reset($this->getUser());

        $this->addFlash('info', 'cards.remove_card.success');

        return $this->redirectToRoute('collection_index');
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('/search', name: 'search')]
    public function search(Request $request): Response
    {
        $form = $this->createForm(CardSearchType::class, $request->query->all());
        $form->handleRequest($request);
        $cards = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->doctrine->getManager();
            $repo = $em->getRepository(Card::class);

            if ($form->get('submit_scryfall')->isClicked()) {
                $cards = $repo->findByScryfallId($data['scryfall_id']);
            } elseif ($form->get('submit_set_number')->isClicked()) {
                // Validate
                $cards = $repo->findBySetCodeAndNumber($data['setCode'], $data['number']);
            } elseif ($form->get('submit_name')->isClicked()) {
                $cards = $repo->findByName($data['name']);
            }
        }

        return $this->render('cards/search.html.twig', [
            'form'       => $form->createView(),
            'collection' => $cards,
        ]);
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('/multi-search', name: 'multi_search')]
    public function multiSearch(Request $request): Response
    {
        $form = $this->createForm(MultiSearchType::class);
        $form->handleRequest($request);

        $sorted = [];
        $cardNames = [];
        $oracleIdToNameMapping = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $cardNames = explode("\r\n", $data['cards']);
            $cardNamesToMap = $cardNames;

            $em = $this->doctrine->getManager();
            $repo = $em->getRepository(CollectedCard::class);
            $cards = $repo->findByNamesAndUsers($cardNames, $data['users']->toArray());

            /** @var CollectedCard $card */
            foreach ($cards as $card) {
                $index = $card->getCard()->getScryfallOracleId();
                if (!isset($sorted[$index])) {
                    $sorted[$index] = [];
                }

                $sorted[$index][] = $card;
                if (!isset($oracleIdToNameMapping[$index])) {
                    foreach ($cardNamesToMap as $cardIndex => $cardName) {
                        foreach (Card::CARD_LANGUAGES as $lang) {
                            $texts = $card->getCard()->getTexts($lang);
                            if (strtolower($texts->getName()) === strtolower($cardName)) {
                                $oracleIdToNameMapping[$index] = $cardName;

                                unset($cardNamesToMap[$cardIndex]);
                                $cardNamesToMap = array_values($cardNamesToMap);
                                continue 3;
                            }
                        }
                    }
                }
            }
        }

        return $this->render('cards/multi_search.html.twig', [
            'form'                      => $form->createView(),
            'cards'                     => $sorted,
            'searched_cards'            => $cardNames,
            'cards_not_found'           => $cardNamesToMap ?? null,
            'oracle_id_to_name_mapping' => $oracleIdToNameMapping,
        ]);
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('/add', name: 'add')]
    public function addCard(Request $request, CollectionManager $collectionManager): Response
    {
        $data = $request->query->all();

        $em = $this->doctrine->getManager();
        $repo = $em->getRepository(Card::class);
        $card = $repo->findOneBy([
            'setCode' => $data['setCode'],
            'number'  => $data['number'],
        ]);

        $data['nonFoilAmount'] = (int) $data['nonFoilAmount'];
        $data['foilAmount'] = (int) $data['foilAmount'];

        if (!$card || ($data['nonFoilAmount'] === 0 && $data['foilAmount'] === 0)) {
            throw $this->createNotFoundException();
        }

        $collectionManager->addCard(
            $this->getUser(),
            $card,
            $data['lang'],
            $data['nonFoilAmount'],
            $data['foilAmount']
        );

        $this->collectionStatsProvider->reset($this->getUser());

        $this->addFlash('info', 'cards.add_card.success');

        return $this->redirectToRoute('collection_index');
    }

    #[Security("is_granted('ROLE_USER')")]
    #[Route('/following', name: 'following')]
    public function following(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $repo = $em->getRepository(User::class);

        $followingUsers = $repo->findFollowing($this->getUser());

        [$form, $collection, $pager] = $this->getCollection($request, $followingUsers, 16);

        return $this->render('cards/following.html.twig', [
            'collection' => $collection,
            'pager'      => $pager,
            'form'       => $form->createView(),
        ]);
    }
}
