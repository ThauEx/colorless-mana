<?php

namespace App\Command;

use App\Entity\Card;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use JsonMachine\Items as JsonMachine;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mtgjson:import:prices', description: 'Imports card prices from a mtgjson source')]
class MtgjsonImportPricesCommand extends Command
{
    private const BATCH_SIZE = 500;

    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to the json file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');

        if (!file_exists($path)) {
            $io->error('File does not exist.');

            return Command::FAILURE;
        }

        $dateKey = null;
        foreach (JsonMachine::fromFile($path, ['pointer' => '/meta/date', 'decoder' => new ExtJsonDecoder(true)]) as $value) {
            $dateKey = (new DateTimeImmutable($value, new DateTimeZone('UTC')))->format('Y-m-d');
        }

        if ($dateKey === null) {
            $io->error('File contains no meta date.');

            return Command::FAILURE;
        }

        $updated = 0;
        $missing = 0;
        $batch = [];

        foreach (JsonMachine::fromFile($path, ['pointer' => '/data', 'decoder' => new ExtJsonDecoder(true)]) as $uuid => $entry) {
            $prices = $this->extractPrices($entry, $dateKey);

            if (empty($prices)) {
                continue;
            }

            $batch[$uuid] = $prices;

            if (count($batch) >= self::BATCH_SIZE) {
                [$u, $m] = $this->processBatch($batch);
                $updated += $u;
                $missing += $m;
                $batch = [];
            }
        }

        if (!empty($batch)) {
            [$u, $m] = $this->processBatch($batch);
            $updated += $u;
            $missing += $m;
        }

        $io->success(sprintf('Finished! Updated prices for %d cards, %d uuids without matching card.', $updated, $missing));

        return Command::SUCCESS;
    }

    /** @return array{0: int, 1: int} numbers of updated and missing cards */
    private function processBatch(array $batch): array
    {
        $cards = $this->em->getRepository(Card::class)->findBy(['mtgjsonUuid' => array_keys($batch)]);
        $updated = 0;

        foreach ($cards as $card) {
            $prices = $batch[$card->getMtgjsonUuid()] ?? null;

            if ($prices === null) {
                continue;
            }

            if (isset($prices['cardkingdom']['normal'])) {
                $card->getCardkingdomPrices()->setPriceNormal($prices['cardkingdom']['normal']);
            }
            if (isset($prices['cardkingdom']['foil'])) {
                $card->getCardkingdomPrices()->setPriceFoil($prices['cardkingdom']['foil']);
            }
            if (isset($prices['cardmarket']['normal'])) {
                $card->getCardmarketPrices()->setPriceNormal($prices['cardmarket']['normal']);
            }
            if (isset($prices['cardmarket']['foil'])) {
                $card->getCardmarketPrices()->setPriceFoil($prices['cardmarket']['foil']);
            }
            if (isset($prices['tcgplayer']['normal'])) {
                $card->getTcgplayerPrices()->setPriceNormal($prices['tcgplayer']['normal']);
            }
            if (isset($prices['tcgplayer']['foil'])) {
                $card->getTcgplayerPrices()->setPriceFoil($prices['tcgplayer']['foil']);
            }

            $updated++;
        }

        $this->em->flush();
        $this->em->clear();

        return [$updated, count($batch) - $updated];
    }

    /** @return array<string, array{normal?: float, foil?: float}> */
    private function extractPrices(array $entry, string $dateKey): array
    {
        $prices = [];

        foreach (['cardkingdom', 'cardmarket', 'tcgplayer'] as $vendor) {
            $retail = $entry['paper'][$vendor]['retail'] ?? [];

            $normal = $retail['normal'][$dateKey] ?? 0.0;
            $foil = $retail['foil'][$dateKey] ?? 0.0;

            if ($normal > 0.0) {
                $prices[$vendor]['normal'] = $normal;
            }
            if ($foil > 0.0) {
                $prices[$vendor]['foil'] = $foil;
            }
        }

        return $prices;
    }
}
