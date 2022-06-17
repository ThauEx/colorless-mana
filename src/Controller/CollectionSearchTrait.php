<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CollectedCard;
use App\Entity\User;
use App\Form\CollectionSearchType;
use ArrayIterator;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

trait CollectionSearchTrait
{
    /** @param UserInterface[] $users */
    private function getCollection(Request $request, array $users, bool $includePrintings = false, ?int $limit = 15): array
    {
        $em = $this->doctrine->getManager();
        $repo = $em->getRepository(CollectedCard::class);
        $cardRepo = $em->getRepository(Card::class);
        $userRepo = $em->getRepository(User::class);
        $selectedUsers = [];
        $searchParams = [];

        /** @var FormInterface $form */
        $form = $this->container->get('form.factory')->createNamed('', CollectionSearchType::class, [], [
            'method' => 'GET',
            'users'  => $users,
        ]);

        if (count($users) === 1) {
            $selectedUsers[] = $users[0]->getId();
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchParams = $form->getData();

            if (isset($searchParams['users'])) {
                $selectedUsers = $userRepo->findByHashes($searchParams['users']);
            }
        }

        $qb = $repo->findWithSearchParams($searchParams, $selectedUsers);

        $pagerfanta = new Pagerfanta(new QueryAdapter($qb));
        $pagerfanta
            ->setCurrentPage($request->query->get('page', 1))
            ->setMaxPerPage($limit)
        ;

        $results = $pagerfanta->getCurrentPageResults();
        $cleanedResults = new ArrayIterator();

        foreach ($results as $result) {
            if ($result instanceof CollectedCard) {
                $cleanedResults->append($result);
            }
        }

        $oracleIds = array_map(static function (CollectedCard $collectedCard) {
            return $collectedCard->getCard()->getScryfallOracleId();
        }, iterator_to_array($cleanedResults));

        $returnData = [$form, $cleanedResults, $pagerfanta];

        if ($includePrintings) {
            $returnData[] = $cardRepo->findSetCodeAndNumberByScryfallOracleIds($oracleIds);
        }

        return $returnData;
    }
}
