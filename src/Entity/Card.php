<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\CardRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;

#[ORM\Table(name: 'cards')]
#[ORM\Index(columns: ['scryfall_oracle_id', 'en_name', 'es_name', 'fr_name', 'de_name', 'it_name', 'pt_name', 'ja_name', 'ko_name', 'ru_name', 'zhs_name', 'zht_name', 'he_name', 'la_name', 'grc_name', 'ar_name', 'sa_name', 'ph_name'], flags: ['fulltext'])]
#[ORM\Index(columns: ['mtgjson_uuid'])]
#[ORM\Index(columns: ['scryfall_id'])]
#[ORM\Index(columns: ['set_code', 'number'])]
#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $artist;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $borderColor;

    #[ORM\Column(type: 'array')]
    private array $colorIdentity = [];

    #[ORM\Column(type: 'array')]
    private array $colors = [];

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $convertedManaCost;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $frameVersion;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?string $scryfallId;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?string $scryfallIllustrationId;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?string $scryfallOracleId;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $layout;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $manaCost;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $number;

    #[ORM\Column(type: 'array')]
    private array $printings = [];

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $rarity;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $setCode;

    #[ORM\Column(type: 'array')]
    private array $subtypes = [];

    #[ORM\Column(type: 'array')]
    private array $supertypes = [];

    #[ORM\Column(type: 'array')]
    private array $types = [];

    #[ORM\Column(type: Types::STRING, length: 1, nullable: true)]
    private ?string $side;

    // Current MTGJSON uuid; MTGJSON regenerates it when card data changes, so it
    // can differ from the immutable primary key. Price data is matched against it.
    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?string $mtgjsonUuid = null;

    // Effective finishes: MTGJSON "finishes" merged with foil-like promo types
    // (e.g. surgefoil, galaxyfoil)
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $finishes = [];

    #[Embedded(class: CardPrice::class, columnPrefix: 'cardkingdom_')]
    private CardPrice $cardkingdomPrices;

    #[Embedded(class: CardPrice::class, columnPrefix: 'cardmarket_')]
    private CardPrice $cardmarketPrices;

    #[Embedded(class: CardPrice::class, columnPrefix: 'tcgplayer_')]
    private CardPrice $tcgplayerPrices;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'en_')]
    private CardLanguageData $enTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'es_')]
    private CardLanguageData $esTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'fr_')]
    private CardLanguageData $frTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'de_')]
    private CardLanguageData $deTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'it_')]
    private CardLanguageData $itTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'pt_')]
    private CardLanguageData $ptTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'ja_')]
    private CardLanguageData $jaTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'ko_')]
    private CardLanguageData $koTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'ru_')]
    private CardLanguageData $ruTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'zhs_')]
    private CardLanguageData $zhsTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'zht_')]
    private CardLanguageData $zhtTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'he_')]
    private CardLanguageData $heTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'la_')]
    private CardLanguageData $laTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'grc_')]
    private CardLanguageData $grcTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'ar_')]
    private CardLanguageData $arTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'sa_')]
    private CardLanguageData $saTexts;

    #[Embedded(class: CardLanguageData::class, columnPrefix: 'ph_')]
    private CardLanguageData $phTexts;

    public const CARD_LANGUAGES = ['en', 'de', 'es', 'fr', 'it', 'pt', 'ja', 'ko', 'ru', 'zhs', 'zht', 'he', 'la', 'grc', 'ar', 'sa', 'ph'];

    public function __construct()
    {
//        $this->languageData = new ArrayCollection();

        $this->cardkingdomPrices = new CardPrice();
        $this->cardmarketPrices = new CardPrice();
        $this->tcgplayerPrices = new CardPrice();
        $this->enTexts = new CardLanguageData();
        $this->esTexts = new CardLanguageData();
        $this->frTexts = new CardLanguageData();
        $this->deTexts = new CardLanguageData();
        $this->itTexts = new CardLanguageData();
        $this->ptTexts = new CardLanguageData();
        $this->jaTexts = new CardLanguageData();
        $this->koTexts = new CardLanguageData();
        $this->ruTexts = new CardLanguageData();
        $this->zhsTexts = new CardLanguageData();
        $this->zhtTexts = new CardLanguageData();
        $this->heTexts = new CardLanguageData();
        $this->laTexts = new CardLanguageData();
        $this->grcTexts = new CardLanguageData();
        $this->arTexts = new CardLanguageData();
        $this->saTexts = new CardLanguageData();
        $this->phTexts = new CardLanguageData();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(string $borderColor): self
    {
        $this->borderColor = $borderColor;

        return $this;
    }

    public function getColorIdentity(): ?array
    {
        return $this->colorIdentity;
    }

    public function setColorIdentity(array $colorIdentity): self
    {
        $this->colorIdentity = $colorIdentity;

        return $this;
    }

    public function getColors(): ?array
    {
        return $this->colors;
    }

    public function setColors(array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function getConvertedManaCost(): ?float
    {
        return $this->convertedManaCost;
    }

    public function setConvertedManaCost(float $convertedManaCost): self
    {
        $this->convertedManaCost = $convertedManaCost;

        return $this;
    }

    public function getFrameVersion(): ?string
    {
        return $this->frameVersion;
    }

    public function setFrameVersion(string $frameVersion): self
    {
        $this->frameVersion = $frameVersion;

        return $this;
    }

    public function getScryfallId(): ?string
    {
        return $this->scryfallId;
    }

    public function setScryfallId(?string $scryfallId): self
    {
        $this->scryfallId = $scryfallId;

        return $this;
    }

    public function getScryfallIllustrationId(): ?string
    {
        return $this->scryfallIllustrationId;
    }

    public function setScryfallIllustrationId(?string $scryfallIllustrationId): self
    {
        $this->scryfallIllustrationId = $scryfallIllustrationId;

        return $this;
    }

    public function getScryfallOracleId(): ?string
    {
        return $this->scryfallOracleId;
    }

    public function setScryfallOracleId(?string $scryfallOracleId): self
    {
        $this->scryfallOracleId = $scryfallOracleId;

        return $this;
    }

    public function getLayout(): ?string
    {
        return $this->layout;
    }

    public function setLayout(string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    public function getManaCost(): ?string
    {
        return $this->manaCost;
    }

    public function setManaCost(string $manaCost): self
    {
        $this->manaCost = $manaCost;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPrintings(): ?array
    {
        return $this->printings;
    }

    public function setPrintings(array $printings): self
    {
        $this->printings = $printings;

        return $this;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): self
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function getSetCode(): ?string
    {
        return $this->setCode;
    }

    public function setSetCode(string $setCode): self
    {
        $this->setCode = $setCode;

        return $this;
    }

    public function getSubtypes(): ?array
    {
        return $this->subtypes;
    }

    public function setSubtypes(array $subtypes): self
    {
        $this->subtypes = $subtypes;

        return $this;
    }

    public function getSupertypes(): ?array
    {
        return $this->supertypes;
    }

    public function setSupertypes(array $supertypes): self
    {
        $this->supertypes = $supertypes;

        return $this;
    }

    public function getTypes(): ?array
    {
        return $this->types;
    }

    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function getSide(): ?string
    {
        return $this->side;
    }

    public function setSide(?string $side): self
    {
        $this->side = $side;

        return $this;
    }

    public function getMtgjsonUuid(): ?string
    {
        return $this->mtgjsonUuid;
    }

    public function setMtgjsonUuid(?string $mtgjsonUuid): self
    {
        $this->mtgjsonUuid = $mtgjsonUuid;

        return $this;
    }

    public function getFinishes(): array
    {
        return $this->finishes ?? [];
    }

    public function setFinishes(array $finishes): self
    {
        $this->finishes = $finishes;

        return $this;
    }

//    /** @return Collection|CardLanguageData[] */
//    public function getLanguageData(): Collection
//    {
//        return $this->languageData;
//    }
//
//    public function addLanguageData(CardLanguageData $languageData): self
//    {
//        if (!$this->languageData->contains($languageData)) {
//            $this->languageData[] = $languageData;
//            $languageData->setCard($this);
//        }
//
//        return $this;
//    }
//
//    public function removeLanguageData(CardLanguageData $languageData): self
//    {
//        if ($this->languageData->removeElement($languageData)) {
//            // set the owning side to null (unless already changed)
//            if ($languageData->getCard() === $this) {
//                $languageData->setCard(null);
//            }
//        }
//
//        return $this;
//    }
//

    public function getCardLanguages(): array
    {
        $languages = [];

        foreach (self::CARD_LANGUAGES as $lang) {
            if ($this->getTextsForLanguage($lang)) {
                $languages[] = $lang;
            }
        }

        return $languages;
    }

    public function getTexts(string $language = 'en', ?string $fallback = null): ?CardLanguageData
    {
        if ($language === '') {
            foreach (self::CARD_LANGUAGES as $lang) {
                if ($texts = $this->getTextsForLanguage($lang)) {
                    return $texts;
                }
            }

            return new CardLanguageData();
        }

        // Show readable text
        if ($language === 'ph') {
            $language = 'en';
        }

        if ($texts = $this->getTextsForLanguage($language)) {
            return $texts;
        }

        if ($fallback && $language !== $fallback) {
            return $this->getTextsForLanguage($fallback);
        }

        return new CardLanguageData();
    }

    private function getTextsForLanguage(string $language): ?CardLanguageData
    {
        /** @var CardLanguageData $texts */
        $texts = $this->{$language . 'Texts'};
        if (!empty($texts->getName())) {
            return $texts;
        }

        return null;
    }

    public function getCardkingdomPrices(): CardPrice
    {
        return $this->cardkingdomPrices;
    }

    public function getCardmarketPrices(): CardPrice
    {
        return $this->cardmarketPrices;
    }

    public function getTcgplayerPrices(): CardPrice
    {
        return $this->tcgplayerPrices;
    }

    public function getEnTexts(): CardLanguageData
    {
        return $this->enTexts;
    }

    public function getEsTexts(): CardLanguageData
    {
        return $this->esTexts;
    }

    public function getFrTexts(): CardLanguageData
    {
        return $this->frTexts;
    }

    public function getDeTexts(): CardLanguageData
    {
        return $this->deTexts;
    }

    public function getItTexts(): CardLanguageData
    {
        return $this->itTexts;
    }

    public function getPtTexts(): CardLanguageData
    {
        return $this->ptTexts;
    }

    public function getJaTexts(): CardLanguageData
    {
        return $this->jaTexts;
    }

    public function getKoTexts(): CardLanguageData
    {
        return $this->koTexts;
    }

    public function getRuTexts(): CardLanguageData
    {
        return $this->ruTexts;
    }

    public function getZhsTexts(): CardLanguageData
    {
        return $this->zhsTexts;
    }

    public function getZhtTexts(): CardLanguageData
    {
        return $this->zhtTexts;
    }

    public function getHeTexts(): CardLanguageData
    {
        return $this->heTexts;
    }

    public function getLaTexts(): CardLanguageData
    {
        return $this->laTexts;
    }

    public function getGrcTexts(): CardLanguageData
    {
        return $this->grcTexts;
    }

    public function getArTexts(): CardLanguageData
    {
        return $this->arTexts;
    }

    public function getSaTexts(): CardLanguageData
    {
        return $this->saTexts;
    }

    public function getPhTexts(): CardLanguageData
    {
        return $this->phTexts;
    }
}
