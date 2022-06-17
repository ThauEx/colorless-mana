<?php

namespace App\Helper;

class LanguageMapper
{
    private array $languages = [
        'English'             => 'en',
        'Spanish'             => 'es',
        'French'              => 'fr',
        'German'              => 'de',
        'Italian'             => 'it',
        'Portuguese'          => 'pt',
        'Portuguese (Brazil)' => 'pt',
        'Japanese'            => 'ja',
        'Korean'              => 'ko',
        'Russian'             => 'ru',
        'Simplified Chinese'  => 'zhs',
        'Chinese Simplified'  => 'zhs',
        'Traditional Chinese' => 'zht',
        'Chinese Traditional' => 'zht',
        'Hebrew'              => 'he',
        'Latin'               => 'la',
        'Ancient Greek'       => 'grc',
        'Arabic'              => 'ar',
        'Sanskrit'            => 'sa',
        'Phyrexian'           => 'ph',
    ];

    private array $languageCountry = [
        'en'  => 'gb',
        'es'  => 'es',
        'fr'  => 'fr',
        'de'  => 'de',
        'it'  => 'it',
        'pt'  => 'pt',
        'ja'  => 'jp',
        'ko'  => 'kr',
        'ru'  => 'ru',
        'zhs' => 'cn',
        'zht' => 'tw',
        'he'  => 'il',
        'la'  => '',
        'grc' => 'gr',
        'ar'  => '',
        'sa'  => 'in',
        'ph'  => '',
    ];

    public function codeToLanguage(string $code): string
    {
        return array_flip($this->languages)[$code] ?? 'English';
    }

    public function languageToCode(string $language): string
    {
        return $this->languages[$language] ?? 'en';
    }

    public function getMapping(): array
    {
        return $this->languages;
    }

    public function languageToCountry(string $language): string
    {
        return $this->languageCountry[$language] ?? '';
    }
}
