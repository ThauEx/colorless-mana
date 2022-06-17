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
    private LanguageMapper $languageMapper;

    public function __construct(MtgDataProvider $mtgDataProvider, LanguageMapper $languageMapper)
    {
        $this->sets = $mtgDataProvider->getSets();
        $this->symbology = $mtgDataProvider->getSymbology();
        $this->languageMapper = $languageMapper;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('set_icon', [$this, 'parseSet'], ['is_safe' => ['html']]),
            new TwigFilter('symbology_icon', [$this, 'parseSymbology'], ['is_safe' => ['html']]),
            new TwigFilter('set_name', [$this, 'getSetName']),
            new TwigFilter('flag', [$this, 'getFlag'], ['is_safe' => ['html']]),
        ];
    }

    public function parseSet(string $setCode): string
    {
        return '<img src="' . $this->sets[$setCode]->getSvgUri() . '" alt="" title="' . $this->sets[$setCode]->getName() . ' (' . $setCode . ')">';
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
        return $this->sets[$setCode]->getName();
    }

    public function getFlag(string $languageCode): string
    {
        $flag = $this->languageMapper->languageToCountry($languageCode);
        $language = $this->languageMapper->codeToLanguage($languageCode);

        return '<span class="fi fi-' . $flag . '" title="' . $language . '"></span>';
    }
}
