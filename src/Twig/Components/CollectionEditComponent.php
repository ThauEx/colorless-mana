<?php

namespace App\Twig\Components;

use App\DataProvider\CollectionStatsProvider;
use App\Entity\Card;
use App\Entity\CollectedCard;
use App\Form\CollectedCardType;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('collection_edit')]
class CollectionEditComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    private CollectionStatsProvider $collectionStatsProvider;

    #[LiveProp(fieldName: '')]
    public ?CollectedCard $collectedCard = null;
    #[LiveProp(writable: true)]
    public ?array $printings = null;

    public bool $isSaved = false;
    public bool $error = false;

    public function __construct(CollectionStatsProvider $collectionStatsProvider)
    {
        $this->collectionStatsProvider = $collectionStatsProvider;
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager): void
    {
        $originalCollectedCard = clone $this->collectedCard;

        $this->submitForm();

        $form = $this->getFormInstance();

        [$edition, $number] = explode('-', $form->get('editionAndSetCode')->getData());

        if ($originalCollectedCard->getEdition() !== $edition || $originalCollectedCard->getNumber() !== $number) {
            $collectedCard = $form->getData();
            $cardRepo = $entityManager->getRepository(Card::class);

            $criteria = new Criteria();
            $criteria
                ->where(Criteria::expr()->eq('scryfallOracleId', $collectedCard->getCard()->getScryfallOracleId()))
                ->where(Criteria::expr()->eq('setCode', $edition))
                ->andWhere(Criteria::expr()->eq('number', $number))
                ->andWhere(Criteria::expr()->orX(
                    Criteria::expr()->isNull('side'),
                    Criteria::expr()->neq('side', 'b')
                ))
            ;

            $card = $cardRepo->matching($criteria)->first();

            $collectedCard
                ->setEdition($edition)
                ->setNumber($number)
                ->setCard($card)
            ;
        }

        $this->isSaved = true;
        $entityManager->flush();
        $this->collectionStatsProvider->reset($this->getUser());
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createFormFromCollectedCard($this->collectedCard, $this->printings);
    }

    private function createFormFromCollectedCard(CollectedCard $collectedCard, array $printings): FormInterface
    {
        $form = $this->createForm(CollectedCardType::class, $collectedCard, [
            'collected_card' => $collectedCard,
            'printings'      => $printings,
        ]);
        $form->get('editionAndSetCode')->setData($collectedCard->getEdition() . '-' . $collectedCard->getNumber());

        return $form;
    }
}
