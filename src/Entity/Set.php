<?php

namespace App\Entity;

use App\Repository\SetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'sets')]
#[ORM\Entity(repositoryClass: SetRepository::class)]
class Set
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 16)]
    private string $code;

    #[ORM\Column(name: 'release_date', type: Types::STRING, length: 10)]
    private string $releaseDate;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getReleaseDate(): string
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(string $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }
}
