<?php

namespace App\Tests\Entity;

use App\Entity\Card;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    public function testGetTextsFallsBackToEnglishTypeWhenLocalizedTypeIsMissing(): void
    {
        $card = new Card();
        $card->getEnTexts()->setName('Plains')->setType('Basic Land — Plains');
        // MTGJSON's foreignData omits the type line on some prints of reprinted cards
        $card->getDeTexts()->setName('Ebene')->setType('');

        self::assertSame('Basic Land — Plains', $card->getTexts('de')->getType());
    }

    public function testGetTextsKeepsLocalizedTypeWhenPresent(): void
    {
        $card = new Card();
        $card->getEnTexts()->setName('Plains')->setType('Basic Land — Plains');
        $card->getDeTexts()->setName('Ebene')->setType('Standardland — Ebene');

        self::assertSame('Standardland — Ebene', $card->getTexts('de')->getType());
    }

    public function testGetTextsFallbackDoesNotMutateStoredGermanType(): void
    {
        $card = new Card();
        $card->getEnTexts()->setName('Plains')->setType('Basic Land — Plains');
        $card->getDeTexts()->setName('Ebene')->setType('');

        $card->getTexts('de');

        self::assertSame('', $card->getDeTexts()->getType());
    }
}
