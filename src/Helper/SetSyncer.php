<?php

namespace App\Helper;

use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SetSyncer
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire(service: 'scryfall.client')]
        private readonly HttpClientInterface $client,
    ) {
    }

    /** Upserts every set known to Scryfall, keyed by its (lowercase) code. */
    public function sync(): int
    {
        $response = $this->client->request('GET', '/sets');
        $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);

        $existing = [];
        foreach ($this->em->getRepository(Set::class)->findAll() as $set) {
            $existing[$set->getCode()] = $set;
        }

        $count = 0;
        foreach ($data['data'] as $item) {
            $code = strtolower((string) $item['code']);
            $set = $existing[$code] ?? new Set()->setCode($code);

            $set->setReleaseDate($item['released_at']);
            $this->em->persist($set);
            $count++;
        }

        $this->em->flush();

        return $count;
    }
}
