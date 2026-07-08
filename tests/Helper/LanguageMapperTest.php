<?php

namespace App\Tests\Helper;

use App\Helper\LanguageMapper;
use PHPUnit\Framework\TestCase;

class LanguageMapperTest extends TestCase
{
    private LanguageMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new LanguageMapper();
    }

    public function testLanguageToCodeMapsKnownLanguage(): void
    {
        self::assertSame('de', $this->mapper->languageToCode('German'));
    }

    public function testLanguageToCodeFallsBackToEnglishForUnknownLanguage(): void
    {
        self::assertSame('en', $this->mapper->languageToCode('Klingon'));
    }

    /** Both MTGJSON spellings for the same language must resolve to the same code */
    public function testLanguageToCodeHandlesBothChineseSpellingVariants(): void
    {
        self::assertSame('zhs', $this->mapper->languageToCode('Simplified Chinese'));
        self::assertSame('zhs', $this->mapper->languageToCode('Chinese Simplified'));
        self::assertSame('zht', $this->mapper->languageToCode('Traditional Chinese'));
        self::assertSame('zht', $this->mapper->languageToCode('Chinese Traditional'));
    }

    public function testCodeToLanguageMapsKnownCode(): void
    {
        self::assertSame('German', $this->mapper->codeToLanguage('de'));
    }

    public function testCodeToLanguageFallsBackToEnglishForUnknownCode(): void
    {
        self::assertSame('English', $this->mapper->codeToLanguage('xx'));
    }

    public function testLanguageToCountryMapsKnownCode(): void
    {
        self::assertSame('jp', $this->mapper->languageToCountry('ja'));
    }

    public function testLanguageToCountryReturnsEmptyStringForUnknownCode(): void
    {
        self::assertSame('', $this->mapper->languageToCountry('xx'));
    }
}
