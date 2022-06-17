<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embeddable;

/** @Embeddable */
class CardPrice
{
    /** @ORM\Column(type="float") */
    private ?float $priceNormal = 0.0;

    /** @ORM\Column(type="float") */
    private ?float $priceFoil = 0.0;

    public function getPriceNormal(): ?float
    {
        return $this->priceNormal;
    }

    public function setPriceNormal(float $priceNormal): self
    {
        $this->priceNormal = $priceNormal;

        return $this;
    }

    public function getPriceFoil(): ?float
    {
        return $this->priceFoil;
    }

    public function setPriceFoil(float $priceFoil): self
    {
        $this->priceFoil = $priceFoil;

        return $this;
    }
}
