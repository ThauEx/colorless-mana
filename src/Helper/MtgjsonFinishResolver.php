<?php

namespace App\Helper;

class MtgjsonFinishResolver
{
    // Foil treatments whose promo type name does not follow the "*foil" /
    // "neonink*" naming; see isFoilTreatment()
    private const EXTRA_FOIL_TREATMENTS = [
        'doublerainbow',
        'embossed',
        'gilded',
        'glossy',
        'invisibleink',
        'metal',
        'oilslick',
        'stepandcompleat',
        'textured',
    ];

    /** @return string[] */
    public function effectiveFinishes(array $cardData): array
    {
        $finishes = $cardData['finishes'] ?? [];
        $treatments = array_values(array_filter($cardData['promoTypes'] ?? [], $this->isFoilTreatment(...)));

        if (empty($treatments)) {
            return $finishes;
        }

        // "raisedfoil" only describes the texture of another treatment (e.g.
        // oil slick raised foil) and the plain "neonink" tag always accompanies
        // its color variant; neither is a finish of its own then
        if (count($treatments) > 1) {
            $treatments = array_values(array_diff($treatments, ['raisedfoil', 'neonink']));
        }

        // The remaining treatments describe a single physical finish
        if (count($treatments) > 1) {
            sort($treatments);
            $treatments = [implode('+', $treatments)];
        }

        // A foil treatment describes what the card's foil actually is, so it
        // replaces the generic "foil" entry.
        $finishes = array_values(array_diff($finishes, ['foil']));

        return array_merge($finishes, $treatments);
    }

    private function isFoilTreatment(string $promoType): bool
    {
        return str_ends_with($promoType, 'foil')
            || str_starts_with($promoType, 'neonink')
            || in_array($promoType, self::EXTRA_FOIL_TREATMENTS, true);
    }
}
