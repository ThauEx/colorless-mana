<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\WishlistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Table(name: 'wishlist')]
#[ORM\Entity(repositoryClass: WishlistRepository::class)]
class Wishlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $uuid;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $languages = [];

    #[ORM\Column(type: Types::INTEGER)]
    private int $nonFoilQuantity = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private int $foilQuantity = 0;

    #[ORM\Column(type: 'uuid')]
    private UuidInterface $scryfallOracleId;

    /** @var Collection<Card> */
    #[ORM\ManyToMany(targetEntity: Card::class)]
    private Collection $cards;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wishlist')]
    private ?User $user;

    private $scryfallOracleIdCards = [];

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    public function setLanguages(?array $languages): self
    {
        $this->languages = $languages;

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

    public function getScryfallOracleId(): UuidInterface
    {
        return $this->scryfallOracleId;
    }

    public function setScryfallOracleId(UuidInterface $scryfallOracleId): self
    {
        $this->scryfallOracleId = $scryfallOracleId;

        return $this;
    }

    /** @return Collection|Card[] */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): self
    {
        if (!$this->cards->contains($card)) {
            $this->cards[] = $card;
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        $this->cards->removeElement($card);

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

    public function getScryfallOracleIdCards(): array
    {
        return $this->scryfallOracleIdCards;
    }

    public function addScryfallOracleIdCards(Card $card): self
    {
        $this->scryfallOracleIdCards[] = $card;

        return $this;
    }
}
