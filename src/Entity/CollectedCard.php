<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\CollectedCardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'collected_cards')]
#[ORM\Index(columns: ['edition', 'number', 'language'])]
#[ORM\Index(columns: ['user_id', 'edition', 'number'])]
#[ORM\UniqueConstraint(name: 'uniq_collected_card_entry', columns: ['user_id', 'card_id', 'language', 'finish'])]
#[ORM\Entity(repositoryClass: CollectedCardRepository::class)]
class CollectedCard
{
    public const FINISH_NONFOIL = 'nonfoil';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[ORM\Column(name: 'edition', type: Types::STRING, length: 255)]
    private string $edition;

    #[ORM\Column(name: 'number', type: Types::STRING, length: 255)]
    private string $number;

    #[ORM\Column(name: 'language', type: Types::STRING, length: 3)]
    private string $language;

    #[ORM\Column(name: 'finish', type: Types::STRING, length: 32, options: ['default' => self::FINISH_NONFOIL])]
    private string $finish = self::FINISH_NONFOIL;

    #[ORM\Column(name: 'quantity', type: Types::INTEGER, options: ['default' => 0])]
    private int $quantity = 0;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY', inversedBy: 'collectedCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Card::class)]
    private ?Card $card = null;

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

    public function getFinish(): string
    {
        return $this->finish;
    }

    public function setFinish(string $finish): self
    {
        $this->finish = $finish;

        return $this;
    }

    public function isFoil(): bool
    {
        return $this->finish !== self::FINISH_NONFOIL;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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
