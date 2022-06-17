<?php

namespace App\Entity;

class CardSet
{
    private $code;
    private $svgUri;
    private $name;
    private $type;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getSvgUri(): ?string
    {
        return $this->svgUri;
    }

    public function setSvgUri(string $svgUri): self
    {
        $this->svgUri = $svgUri;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public static function fromArray($data): self
    {
        $self = new static();

        $self
            ->setCode($data['code'])
            ->setSvgUri($data['icon_svg_uri'])
            ->setName($data['name'])
            ->setType($data['set_type'])
        ;

        return $self;
    }
}
