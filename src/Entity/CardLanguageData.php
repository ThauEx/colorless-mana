<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

/** @Embeddable */
class CardLanguageData
{
    /** @ORM\Column(type="text", nullable=true) */
    private ?string $flavorText = null;

    /** @ORM\Column(type="integer", nullable=true) */
    private ?int $multiverseId = null;

    /** @ORM\Column(type="text", nullable=true) */
    private ?string $name = null;

    /** @ORM\Column(name="text", type="text", nullable=true) */
    private ?string $text = null;

    /** @ORM\Column(type="text", nullable=true) */
    private ?string $type = null;

    public function getFlavorText(): ?string
    {
        return $this->flavorText;
    }

    public function setFlavorText(?string $flavorText): self
    {
        $this->flavorText = $flavorText;
        return $this;
    }

    public function getMultiverseId(): ?int
    {
        return $this->multiverseId;
    }

    public function setMultiverseId(?int $multiverseId): self
    {
        $this->multiverseId = $multiverseId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
