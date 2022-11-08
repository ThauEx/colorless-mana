<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

#[Embeddable]
class UserSettings
{
    public const CAN_SEE_COLLECTION = 'canSeeCollection';
    public const CAN_SEARCH_IN_COLLECTION = 'canSearchInCollection';

    public const NOBODY = 0;
    public const FOLLOWERS = 1;
    public const FOLLOWING_FOLLOWERS = 2;
    public const EVERYBODY = 3;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $settings;

    public function __construct()
    {
        $this->settings = [
            self::CAN_SEE_COLLECTION       => self::NOBODY,
            self::CAN_SEARCH_IN_COLLECTION => self::NOBODY,
        ];
    }

    public function getAll(): ?array
    {
        return $this->settings;
    }

    public function setAll(?array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function set(string $key, $value): self
    {
        $this->settings[$key] = $value;

        return $this;
    }
}
