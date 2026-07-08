<?php

namespace App\Tests\Helper;

use App\Entity\Card;
use App\Helper\CollectionManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CollectionManagerTest extends TestCase
{
    private CollectionManager $collectionManager;

    protected function setUp(): void
    {
        // resolveTarget() never touches the database, but the constructor requires an EntityManager
        $this->collectionManager = new CollectionManager($this->createMock(EntityManagerInterface::class));
    }

    private function cardWithFinishes(array $finishes): Card
    {
        $card = new Card();
        $card->setFinishes($finishes);

        return $card;
    }

    public function testNonFoilAmountOnNormallyPrintedCardStaysNonfoil(): void
    {
        $card = $this->cardWithFinishes(['nonfoil', 'foil']);

        [$targetCard, $finish] = $this->collectionManager->resolveTarget($card, false);

        self::assertSame($card, $targetCard);
        self::assertSame('nonfoil', $finish);
    }

    public function testNonFoilAmountOnFoilOnlyPromoUsesTheOnlyAvailableFinish(): void
    {
        $card = $this->cardWithFinishes(['surgefoil']);

        [$targetCard, $finish] = $this->collectionManager->resolveTarget($card, false);

        self::assertSame($card, $targetCard);
        self::assertSame('surgefoil', $finish);
    }

    public function testFoilAmountOnNormallyPrintedCardUsesPlainFoil(): void
    {
        $card = $this->cardWithFinishes(['nonfoil', 'foil']);

        [$targetCard, $finish] = $this->collectionManager->resolveTarget($card, true);

        self::assertSame($card, $targetCard);
        self::assertSame('foil', $finish);
    }

    public function testFoilAmountOnCardWithASingleFoilTreatmentUsesThatTreatment(): void
    {
        $card = $this->cardWithFinishes(['nonfoil', 'surgefoil']);

        [$targetCard, $finish] = $this->collectionManager->resolveTarget($card, true);

        self::assertSame($card, $targetCard);
        self::assertSame('surgefoil', $finish);
    }

    public function testFoilAmountOnCardWithMultipleFoilTreatmentsFallsBackToPlainFoil(): void
    {
        $card = $this->cardWithFinishes(['nonfoil', 'galaxyfoil', 'surgefoil']);

        [$targetCard, $finish] = $this->collectionManager->resolveTarget($card, true);

        self::assertSame($card, $targetCard);
        self::assertSame('foil', $finish);
    }

    public function testFoilAmountOnNonFoilOnlyCardMovesToTheStarCard(): void
    {
        $card = $this->cardWithFinishes(['nonfoil']);
        $starCard = $this->cardWithFinishes(['rainbowfoil']);

        [$targetCard, $finish] = $this->collectionManager->resolveTarget($card, true, $starCard);

        self::assertSame($starCard, $targetCard);
        self::assertSame('rainbowfoil', $finish);
    }

    public function testFoilAmountOnNonFoilOnlyCardWithoutStarCardFallsBackToPlainFoilOnTheOriginalCard(): void
    {
        $card = $this->cardWithFinishes(['nonfoil']);

        [$targetCard, $finish] = $this->collectionManager->resolveTarget($card, true);

        self::assertSame($card, $targetCard);
        self::assertSame('foil', $finish);
    }
}
