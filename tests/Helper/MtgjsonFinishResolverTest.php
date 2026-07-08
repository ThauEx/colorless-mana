<?php

namespace App\Tests\Helper;

use App\Helper\MtgjsonFinishResolver;
use PHPUnit\Framework\TestCase;

class MtgjsonFinishResolverTest extends TestCase
{
    private MtgjsonFinishResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new MtgjsonFinishResolver();
    }

    public function testNoPromoTypesKeepsFinishesUnchanged(): void
    {
        $result = $this->resolver->effectiveFinishes([
            'finishes'   => ['nonfoil', 'foil'],
            'promoTypes' => ['prerelease'],
        ]);

        self::assertSame(['nonfoil', 'foil'], $result);
    }

    public function testMissingFinishesAndPromoTypesReturnEmpty(): void
    {
        self::assertSame([], $this->resolver->effectiveFinishes([]));
    }

    public function testSingleFoilTreatmentReplacesFoil(): void
    {
        $result = $this->resolver->effectiveFinishes([
            'finishes'   => ['nonfoil', 'foil'],
            'promoTypes' => ['surgefoil'],
        ]);

        self::assertSame(['nonfoil', 'surgefoil'], $result);
    }

    public function testExtraFoilTreatmentWithoutFoilSuffixIsRecognized(): void
    {
        $result = $this->resolver->effectiveFinishes([
            'finishes'   => ['nonfoil', 'foil'],
            'promoTypes' => ['embossed'],
        ]);

        self::assertSame(['nonfoil', 'embossed'], $result);
    }

    public function testNeonInkColorVariantDropsThePlainNeonInkTag(): void
    {
        $result = $this->resolver->effectiveFinishes([
            'finishes'   => ['nonfoil', 'foil'],
            'promoTypes' => ['neonink', 'neoninkyellow'],
        ]);

        self::assertSame(['nonfoil', 'neoninkyellow'], $result);
    }

    public function testRaisedFoilOnlyDescribesTheTextureOfAnotherTreatment(): void
    {
        // Real-world case: Phyrexia: All Will Be One cards report both
        // "oilslick" and "raisedfoil" for what is physically a single finish.
        $result = $this->resolver->effectiveFinishes([
            'finishes'   => ['nonfoil', 'foil'],
            'promoTypes' => ['oilslick', 'raisedfoil'],
        ]);

        self::assertSame(['nonfoil', 'oilslick'], $result);
    }

    public function testUnresolvableMultiTreatmentCombinationIsJoined(): void
    {
        $result = $this->resolver->effectiveFinishes([
            'finishes'   => ['nonfoil', 'foil'],
            'promoTypes' => ['galaxyfoil', 'surgefoil'],
        ]);

        self::assertSame(['nonfoil', 'galaxyfoil+surgefoil'], $result);
    }

    public function testNonFoilTreatmentPromoTypesAreIgnored(): void
    {
        $result = $this->resolver->effectiveFinishes([
            'finishes'   => ['nonfoil'],
            'promoTypes' => ['prerelease', 'datestamped'],
        ]);

        self::assertSame(['nonfoil'], $result);
    }
}
