<?php

namespace App\Twig;

use App\DataProvider\MtgDataProvider;
use App\Helper\LanguageMapper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MtgExtension extends AbstractExtension
{
    private array $sets;
    private array $symbology;

    public function __construct(MtgDataProvider $mtgDataProvider, private readonly LanguageMapper $languageMapper)
    {
        $this->sets = $mtgDataProvider->getSets();
        $this->symbology = $mtgDataProvider->getSymbology();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('set_icon', $this->parseSet(...), ['is_safe' => ['html']]),
            new TwigFilter('symbology_icon', $this->parseSymbology(...), ['is_safe' => ['html']]),
            new TwigFilter('set_name', $this->getSetName(...)),
            new TwigFilter('flag', $this->getFlag(...), ['is_safe' => ['html']]),
        ];
    }

    public function parseSet(string $setCode): string
    {
        return '<img src="' . $this->sets[$setCode]?->getSvgUri() . '" alt="" title="' . $this->sets[$setCode]?->getName() . ' (' . $setCode . ')">';
    }

    public function parseSymbology(string $value): string
    {
        if (!$value) {
            $value = '{0}';
        }

        $matches = [];
        preg_match_all('#{[\w/]+}#', $value, $matches);
        $matches = reset($matches);
        $matches = array_unique($matches);

        foreach ($matches as $match) {
            $htmlTag = '<img src="' . $this->symbology[$match]->getSvgUri() . '" alt="">';
            $value = str_replace($match, $htmlTag, $value);
        }

        return $value;
    }

    public function getSetName(string $setCode): string
    {
        return $this->sets[$setCode]?->getName() ?? '';
    }

    public function getFlag(string $languageCode): string
    {
        $language = $this->languageMapper->codeToLanguage($languageCode);
        $attribute = $this->languageMapper->getFlagAttribute($languageCode);

        if (isset($attribute['data-flag-icon'])) {
            return '<img class="fi-custom" src="' . $attribute['data-flag-icon'] . '" alt="" title="' . $language . '">';
        }

        return '<span class="fi fi-' . $attribute['data-lang'] . '" title="' . $language . '"></span>';
    }
}
