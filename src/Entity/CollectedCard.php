<?php

namespace App\Entity;

use App\Repository\CollectedCardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="collected_cards", indexes={@ORM\Index(columns={"edition", "number", "language"})})
 * @ORM\Entity(repositoryClass=CollectedCardRepository::class)
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class CollectedCard
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $id;

    /** @ORM\Column(name="edition", type="string", length=255, nullable=false) */
    private string $edition;

    /** @ORM\Column(name="number", type="string", length=255, nullable=false) */
    private string $number;

    /** @ORM\Column(name="language", type="string", length=3, nullable=false) */
    private string $language;

    /** @ORM\Column(name="non_foil_quantity", type="integer", nullable=false) */
    private int $nonFoilQuantity;

    /** @ORM\Column(name="foil_quantity", type="integer", nullable=false) */
    private int $foilQuantity;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="collectedCards", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Card::class)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
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
