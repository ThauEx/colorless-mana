<?php

namespace App\Helper;

use App\Entity\Card;
use App\Entity\CollectedCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CollectionManager
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function addCard(
        UserInterface $user,
        Card $card,
        string $language,
        int $nonFoilQuantity,
        int $foilQuantity,
        bool $updateOnly = false
    ): void {
        $collectedCard = $this->em->getRepository(CollectedCard::class)->findOneBy([
            'user'     => $user,
            'card'     => $card,
            'edition'  => $card->getSetCode(),
            'number'   => $card->getNumber(),
            'language' => $language,
        ]);

        $collectedCard ??= $this->createCollectedCard($user, $card, $language);

        $this->applyQuantities($collectedCard, $nonFoilQuantity, $foilQuantity, $updateOnly);

        $this->em->flush();
    }

    public function createCollectedCard(UserInterface $user, Card $card, string $language): CollectedCard
    {
        $collectedCard = new CollectedCard();
        $collectedCard
            ->setUser($user)
            ->setCard($card)
            ->setEdition($card->getSetCode())
            ->setNumber($card->getNumber())
            ->setLanguage($language)
            ->setNonFoilQuantity(0)
            ->setFoilQuantity(0)
        ;
        $this->em->persist($collectedCard);

        return $collectedCard;
    }

    public function applyQuantities(
        CollectedCard $collectedCard,
        int $nonFoilQuantity,
        int $foilQuantity,
        bool $updateOnly = false
    ): void {
        if ($updateOnly) {
            $collectedCard
                ->setNonFoilQuantity($nonFoilQuantity)
                ->setFoilQuantity($foilQuantity)
            ;

            return;
        }

        $collectedCard
            ->setNonFoilQuantity($collectedCard->getNonFoilQuantity() + $nonFoilQuantity)
            ->setFoilQuantity($collectedCard->getFoilQuantity() + $foilQuantity)
        ;
    }
}
