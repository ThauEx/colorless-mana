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
        string $finish,
        int $quantity,
        bool $updateOnly = false
    ): void {
        $collectedCard = $this->em->getRepository(CollectedCard::class)->findOneBy([
            'user'     => $user,
            'card'     => $card,
            'language' => $language,
            'finish'   => $finish,
        ]);

        $collectedCard ??= $this->createCollectedCard($user, $card, $language, $finish);

        $this->applyQuantity($collectedCard, $quantity, $updateOnly);

        $this->em->flush();
    }

    public function createCollectedCard(UserInterface $user, Card $card, string $language, string $finish): CollectedCard
    {
        $collectedCard = new CollectedCard();
        $collectedCard
            ->setUser($user)
            ->setCard($card)
            ->setEdition($card->getSetCode())
            ->setNumber($card->getNumber())
            ->setLanguage($language)
            ->setFinish($finish)
            ->setQuantity(0)
        ;
        $this->em->persist($collectedCard);

        return $collectedCard;
    }

    public function applyQuantity(CollectedCard $collectedCard, int $quantity, bool $updateOnly = false): void
    {
        if ($updateOnly) {
            $collectedCard->setQuantity($quantity);

            return;
        }

        $collectedCard->setQuantity($collectedCard->getQuantity() + $quantity);
    }

    /**
     * Resolves which card row and finish a plain non-foil/foil amount (as used
     * by CSV exports and scanning tools) refers to.
     *
     * Foil amounts map to the card's only foil-like finish when unambiguous.
     * When the card was never printed in foil, the foil version usually lives
     * as a separate "★" card (Secret Lair convention), so the amount moves
     * there when such a sibling is given.
     *
     * @return array{0: Card, 1: string} target card and finish
     */
    public function resolveTarget(Card $card, bool $foil, ?Card $starCard = null): array
    {
        $finishes = $card->getFinishes();

        if (!$foil) {
            if (in_array(CollectedCard::FINISH_NONFOIL, $finishes, true) || count($finishes) !== 1) {
                return [$card, CollectedCard::FINISH_NONFOIL];
            }

            // Single-finish card, e.g. foil-only promo: the owned card can only be that finish
            return [$card, $finishes[0]];
        }

        $candidates = array_values(array_diff($finishes, [CollectedCard::FINISH_NONFOIL]));

        if ($candidates !== []) {
            if (in_array('foil', $candidates, true) || count($candidates) !== 1) {
                return [$card, 'foil'];
            }

            return [$card, $candidates[0]];
        }

        if ($starCard !== null) {
            $starCandidates = array_values(array_diff($starCard->getFinishes(), [CollectedCard::FINISH_NONFOIL]));

            return [$starCard, count($starCandidates) === 1 ? $starCandidates[0] : 'foil'];
        }

        return [$card, 'foil'];
    }
}
