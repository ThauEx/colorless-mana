<?php

namespace App\DataProvider;

use App\Entity\Card;
use App\Entity\CardSet;
use App\Entity\CardSymbol;
use App\Helper\CollectionManager;
use App\Helper\LanguageMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MtgDataProvider
{
    private EntityManagerInterface $em;
    private HttpClientInterface $client;
    private CollectionManager $collectionManager;
    private CacheInterface $cache;
    private TokenStorageInterface $tokenStorage;
    private LanguageMapper $languageMapper;

    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $scryfallClient,
        CollectionManager $collectionManager,
        CacheInterface $scryfallHttpCache,
        TokenStorageInterface $tokenStorage,
        LanguageMapper $languageMapper
    ) {
        $this->em = $em;
        $this->client = $scryfallClient;
        $this->collectionManager = $collectionManager;
        $this->cache = $scryfallHttpCache;
        $this->tokenStorage = $tokenStorage;
        $this->languageMapper = $languageMapper;
    }

    public function getCardsByScryfallOracleId(string $scryfallOracleId): array
    {
        return $this->em->getRepository(Card::class)->findSetCodeAndNumberByScryfallOracleId($scryfallOracleId);
    }

    /** @return CardSymbol[] */
    public function getSymbology(): array
    {
        return $this->cache->get('symbology', function () {
            $response = $this->client->request('GET', '/symbology');

            $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
            $symbols = [];
            foreach ($data['data'] as $item) {
                $symbols[$item['symbol']] = CardSymbol::fromArray($item);
            }

            return $symbols;
        });
    }

    /** @return CardSet[] */
    public function getSets(): array
    {
        return $this->cache->get('sets', function () {
            $response = $this->client->request('GET', '/sets');

            $data = json_decode($response->getContent(false), true, 512, JSON_THROW_ON_ERROR);
            $sets = [];
            foreach ($data['data'] as $item) {
                $sets[$item['code']] = CardSet::fromArray($item);
            }

            return $sets;
        });
    }

    public function importCsv(string $file, bool $updateOnly = false): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $csv = $this->readCsv($file);
        $originalCsv = $csv;
        $csv = $this->mapValues($csv);

        $cardRepo = $this->em->getRepository(Card::class);

        $notFound = [];

        foreach ($csv as $index => $item) {
            $cards = $cardRepo->findBy([
                'setCode' => $item['setCode'],
                'number'  => $item['number'],
            ]);

            if (count($cards) > 1) {
                $card = $cardRepo->findOneBy([
                    'setCode' => $item['setCode'],
                    'number'  => $item['number'],
                    'side'    => 'a',
                ]);
            } else {
                $card = reset($cards);
            }

            if (!$card && strlen($item['setCode']) === 4) {
                if (empty($item['number'])) {
                    $card = $cardRepo->findOneByNameAndSetCode($item['name'], $item['setCode']);
                } else {
                    $card = $cardRepo->findOneBy([
                        'setCode' => $item['setCode'],
                        'number'  => $item['number'] . 'p',
                    ]);
                }
            }

            if (!$card) {
                $notFound[] = [
                    'original' => $originalCsv[$index],
                    'mapped'   => $item,
                ];

                continue;
            }

            $this->collectionManager->addCard(
                $user,
                $card,
                $item['language'],
                $item['nonFoilQuantity'],
                $item['foilQuantity'],
                $updateOnly
            );
        }

        $this->em->flush();

        return $notFound;
    }

    private function readCsv(string $file): array
    {
        $lines = file($file);
        $lines = array_map(static function (string $line) {
            return str_replace(';', ',', $line);
        }, $lines);
        $csv = array_map('str_getcsv', $lines);
        $csv[0] = array_map('trim', $csv[0]);
        $csv[0] = array_map(static function ($item) {
            return str_replace('﻿', '', $item);
        }, $csv[0]);
        array_walk($csv, static function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        return $csv;
    }

    private function mapValues(array $array): array
    {
        return array_map(function ($item) {
            if (isset($item['Collector\'s number'])) {
                $item['number'] = $item['Collector\'s number'];
            }

            if (isset($item['Edition code'])) {
                $item['setCode'] = strtolower($item['Edition code']);
            }

            if (isset($item['Edition (code)'])) {
                $item['setCode'] = strtolower(substr($item['Edition (code)'], 1, -1));
            }

            if (isset($item['Language'])) {
                $item['language'] = $this->languageMapper->languageToCode($item['Language']);
            }

            if (isset($item['Non-foil quantity'])) {
                $item['nonFoilQuantity'] = (int) $item['Non-foil quantity'];
            }

            if (isset($item['Foil quantity'])) {
                $item['foilQuantity'] = (int) $item['Foil quantity'];
            }

            if (isset($item['Name'])) {
                $item['name'] = $item['Name'];
            }

            if (isset($item['CardNumber'])) {
                $item['number'] = $item['CardNumber'];
            }

            if (isset($item['Expansion Code'])) {
                $item['setCode'] = strtolower($item['Expansion Code']);
            }

            if (isset($item['Quantity'], $item['Foil'])) {
                if ($item['Foil'] === 'true') {
                    $item['nonFoilQuantity'] = 0;
                    $item['foilQuantity'] = (int) $item['Quantity'];
                } else {
                    $item['nonFoilQuantity'] = (int) $item['Quantity'];
                    $item['foilQuantity'] = 0;
                }
            }

            $item['number'] = ltrim($item['number'], '0');

            return $item;
        }, $array);
    }
}
