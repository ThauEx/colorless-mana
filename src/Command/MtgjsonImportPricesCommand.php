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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mtgjson:import:prices', description: 'Imports cards from a mtgjson source')]
class MtgjsonImportPricesCommand extends Command
{

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Path to the json file')
            ->addOption('price-index', 'i', InputOption::VALUE_OPTIONAL, 'Start at the given set index')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');

        if (!file_exists($path)) {
            $io->error('File does not exist.');

            return Command::FAILURE;
        }

        $loops = 0;

        $data = JsonMachine::fromFile($path, ['pointer' => '/data', 'decoder' => new ExtJsonDecoder(true)]);

        $tz = new DateTimeZone('UTC');

        $metaDate = JsonMachine::fromFile($path, ['pointer' => '/meta/date', 'decoder' => new ExtJsonDecoder(true)]);
        foreach ($metaDate as $value) {
            $fileDate = new DateTimeImmutable($value, $tz);
        }

        $cardRepo = $this->em->getRepository(Card::class);

        $progress = $io->createProgressBar(iterator_count($data));
        $progress->start();
        $index = 0;

        foreach ($data as $uuid => $entry) {
            if ($input->hasOption('price-index') && $index < (int) $input->getOption('price-index')) {
                $progress->advance();
                $index++;
                continue;
            }

            /** @var Card $card */
            $card = $cardRepo->findOneBy(['id' => $uuid]);

            if (!$card) {
                $io->writeln('No card with uuid: ' . $uuid);

                $index++;
                continue;
            }

            $cardkingdom = $entry['paper']['cardkingdom']['retail'] ?? ['normal' => [], 'foil' => []];
            $cardmarket = $entry['paper']['cardmarket']['retail'] ?? ['normal' => [], 'foil' => []];
            $tcgplayer = $entry['paper']['tcgplayer']['retail'] ?? ['normal' => [], 'foil' => []];

            $cardkingdomNormal = $cardkingdom['normal'][$fileDate->format('Y-m-d')] ?? 0.0;
            $cardkingdomFoil = $cardkingdom['foil'][$fileDate->format('Y-m-d')] ?? 0.0;
            $cardmarketNormal = $cardmarket['normal'][$fileDate->format('Y-m-d')] ?? 0.0;
            $cardmarketFoil = $cardmarket['foil'][$fileDate->format('Y-m-d')] ?? 0.0;
            $tcgplayerNormal = $tcgplayer['normal'][$fileDate->format('Y-m-d')] ?? 0.0;
            $tcgplayerFoil = $tcgplayer['foil'][$fileDate->format('Y-m-d')] ?? 0.0;

            if ($cardkingdomNormal > 0.0) {
                $card->getCardkingdomPrices()->setPriceNormal($cardkingdomNormal);
            }
            if ($cardkingdomFoil > 0.0) {
                $card->getCardkingdomPrices()->setPriceFoil($cardkingdomFoil);
            }
            if ($cardmarketNormal > 0.0) {
                $card->getCardmarketPrices()->setPriceNormal($cardmarketNormal);
            }
            if ($cardmarketFoil > 0.0) {
                $card->getCardmarketPrices()->setPriceFoil($cardmarketFoil);
            }
            if ($tcgplayerNormal > 0.0) {
                $card->getTcgplayerPrices()->setPriceNormal($tcgplayerNormal);
            }
            if ($tcgplayerFoil > 0.0) {
                $card->getTcgplayerPrices()->setPriceFoil($tcgplayerFoil);
            }

            if ($loops === 20) {
                $loops = 0;
                $this->em->flush();
                $this->em->clear();
            }

            $loops++;

            $progress->advance();
            $index++;
        }

        $progress->finish();

        $io->success('Finished!');

        return Command::SUCCESS;
    }
}
