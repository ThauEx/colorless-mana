<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\CollectedCardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'collected_cards')]
#[ORM\Index(columns: ['edition', 'number', 'language'])]
#[ORM\Entity(repositoryClass: CollectedCardRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
class CollectedCard
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id;

    #[ORM\Column(name: 'edition', type: Types::STRING, length: 255)]
    private string $edition;

    #[ORM\Column(name: 'number', type: Types::STRING, length: 255)]
    private string $number;

    #[ORM\Column(name: 'language', type: Types::STRING, length: 3)]
    private string $language;

    #[ORM\Column(name: 'non_foil_quantity', type: Types::INTEGER)]
    private int $nonFoilQuantity;

    #[ORM\Column(name: 'foil_quantity', type: Types::INTEGER)]
    private int $foilQuantity;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY', inversedBy: 'collectedCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Card::class)]
    #[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
    private ?Card $card;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEdition(): string
    {
        return $this->edition;
    }

    public function setEdition(string $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getNonFoilQuantity(): int
    {
        return $this->nonFoilQuantity;
    }

    public function setNonFoilQuantity(int $nonFoilQuantity): self
    {
        $this->nonFoilQuantity = $nonFoilQuantity;

        return $this;
    }

    public function getFoilQuantity(): int
    {
        return $this->foilQuantity;
    }

    public function setFoilQuantity(int $foilQuantity): self
    {
        $this->foilQuantity = $foilQuantity;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCard(): ?Card
    {
        return $this->card;
    }

    public function setCard(?Card $card): self
    {
        $this->card = $card;

        return $this;
    }
}
