<?php

namespace App\Helper;

use App\Entity\Card;
use App\Entity\CollectedCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CollectionManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addCard(
        UserInterface $user,
        Card $card,
        string $language,
        int $nonFoilQuantity,
        int $foilQuantity,
        bool $updateOnly = false
    ): void {
        $repo = $this->em->getRepository(CollectedCard::class);
        $collectedCard = $repo->findOneBy([
            'user'     => $user,
            'card'     => $card,
            'edition'  => $card->getSetCode(),
            'number'   => $card->getNumber(),
            'language' => $language,
        ]);

        if (!$collectedCard) {
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
        }

        if ($updateOnly) {
            if ($collectedCard->getNonFoilQuantity() !== $nonFoilQuantity) {
                $collectedCard->setNonFoilQuantity($nonFoilQuantity);
            }

            if ($collectedCard->getFoilQuantity() !== $foilQuantity) {
                $collectedCard->setFoilQuantity($foilQuantity);
            }
        } else {
            $collectedCard
                ->setNonFoilQuantity($collectedCard->getNonFoilQuantity() + $nonFoilQuantity)
                ->setFoilQuantity($collectedCard->getFoilQuantity() + $foilQuantity)
            ;
        }

        $this->em->flush();
    }
}
