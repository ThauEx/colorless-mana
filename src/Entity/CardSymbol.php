<?php

namespace App\Entity;

class CardSymbol
{
    private ?string $symbol;
    private ?string $svgUri;
    private ?string $convertedManaCosts;
    private array $colors = [];

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

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

    public function getConvertedManaCosts(): ?string
    {
        return $this->convertedManaCosts;
    }

    public function setConvertedManaCosts(?string $convertedManaCosts): self
    {
        $this->convertedManaCosts = $convertedManaCosts;

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

    public static function fromArray($data): self
    {
        $self = new static();

        $self
            ->setSymbol($data['symbol'])
            ->setSvgUri($data['svg_uri'])
            ->setConvertedManaCosts($data['cmc'])
            ->setColors($data['colors'])
        ;

        return $self;
    }
}
