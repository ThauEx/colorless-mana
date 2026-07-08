<?php

namespace App\Tests\Doctrine\Query;

use App\Doctrine\Query\MatchAgainstFunction;
use PHPUnit\Framework\TestCase;

class MatchAgainstFunctionTest extends TestCase
{
    public function testSingleWordBecomesRequiredPrefixSearch(): void
    {
        self::assertSame('+bolt*', MatchAgainstFunction::toBooleanSearchTerm('bolt'));
    }

    public function testMultipleWordsAreAllRequired(): void
    {
        self::assertSame('+lightning* +bolt*', MatchAgainstFunction::toBooleanSearchTerm('lightning bolt'));
    }

    public function testExtraWhitespaceIsCollapsed(): void
    {
        self::assertSame('+sol* +ring*', MatchAgainstFunction::toBooleanSearchTerm('  sol   ring  '));
    }

    public function testBooleanModeOperatorsAreStrippedFromWords(): void
    {
        self::assertSame('+bolt*', MatchAgainstFunction::toBooleanSearchTerm('+bolt*'));
        self::assertSame('+bolt*', MatchAgainstFunction::toBooleanSearchTerm('"bolt"'));
    }

    public function testEmptyInputProducesEmptyTerm(): void
    {
        self::assertSame('', MatchAgainstFunction::toBooleanSearchTerm(''));
        self::assertSame('', MatchAgainstFunction::toBooleanSearchTerm('   '));
    }

    public function testInputThatIsOnlyOperatorsProducesEmptyTerm(): void
    {
        self::assertSame('', MatchAgainstFunction::toBooleanSearchTerm('+++'));
    }
}
