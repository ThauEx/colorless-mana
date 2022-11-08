<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use KnpU\OAuth2ClientBundle\Security\User\OAuthUser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends OAuthUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private $uuid;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::JSON)]
    private $roles = [];

    #[ORM\Column(name: 'discord_id', type: Types::STRING, length: 255, nullable: true)]
    private ?string $discordId = null;

    /** @var Collection<CollectedCard> */
    #[ORM\OneToMany(targetEntity: CollectedCard::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $collectedCards;

    /** @var Collection<Wishlist> */
    #[ORM\OneToMany(targetEntity: Wishlist::class, mappedBy: 'user', fetch: 'EXTRA_LAZY')]
    private Collection $wishlist;

    #[Embedded(class: 'UserSettings', columnPrefix: false)]
    private UserSettings $settings;

    public function __construct($username = '', array $roles = [])
    {
        parent::__construct($username, $roles);

        $this->uuid = Uuid::uuid4();
        $this->collectedCards = new ArrayCollection();
        $this->wishlist = new ArrayCollection();
        $this->settings = new UserSettings();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }

    public function setDiscordId(?string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    /**
     * @return Collection|CollectedCard[]
     */
    public function getCollectedCards(): Collection
    {
        return $this->collectedCards;
    }

    public function addCollectedCard(CollectedCard $collectedCard): self
    {
        if (!$this->collectedCards->contains($collectedCard)) {
            $this->collectedCards[] = $collectedCard;
            $collectedCard->setUser($this);
        }

        return $this;
    }

    public function removeCollectedCard(CollectedCard $collectedCard): self
    {
        if ($this->collectedCards->removeElement($collectedCard)) {
            // set the owning side to null (unless already changed)
            if ($collectedCard->getUser() === $this) {
                $collectedCard->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Wishlist[]
     */
    public function getWishlist(): Collection
    {
        return $this->wishlist;
    }

    public function addWishlist(Wishlist $wishlist): self
    {
        if (!$this->wishlist->contains($wishlist)) {
            $this->wishlist[] = $wishlist;
            $wishlist->setUser($this);
        }

        return $this;
    }

    public function removeWishlist(Wishlist $wishlist): self
    {
        if ($this->wishlist->removeElement($wishlist)) {
            // set the owning side to null (unless already changed)
            if ($wishlist->getUser() === $this) {
                $wishlist->setUser(null);
            }
        }

        return $this;
    }

    public function getSettings(): UserSettings
    {
        return $this->settings;
    }
}
