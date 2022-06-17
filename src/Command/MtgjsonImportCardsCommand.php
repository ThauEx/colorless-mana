<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardLanguageData;
use App\Helper\LanguageMapper;
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

#[AsCommand(name: 'mtgjson:import:cards', description: 'Imports cards from a mtgjson source')]
class MtgjsonImportCardsCommand extends Command
{
    private EntityManagerInterface $em;
    private LanguageMapper $languageMapper;

    public function __construct(EntityManagerInterface $em, LanguageMapper $languageMapper)
    {
        $this->em = $em;

        parent::__construct();
        $this->languageMapper = $languageMapper;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Path to the json file')
            ->addOption('set-index', 'i', InputOption::VALUE_OPTIONAL, 'Start at the given set index')
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

        $prevUuid = '';
        $data = JsonMachine::fromFile($path, ['pointer' => '/data', 'decoder' => new ExtJsonDecoder(true)]);
        $cardRepo = $this->em->getRepository(Card::class);

        $setsProgress = $io->createProgressBar(iterator_count($data));
        $setsProgress->start();
        $index = 0;

        foreach ($data as $entry) {
            if ($input->hasOption('set-index') && $index < (int) $input->getOption('set-index')) {
                $io->write("\033[1A");
                $setsProgress->advance();
                print "\n";
                $index++;
                continue;
            }

            print "\n";
            $cardProgress = $io->createProgressBar(count($entry['cards']) + count($entry['tokens']));
            $cardProgress->start();
            foreach (['cards', 'tokens'] as $type) {
                foreach ($entry[$type] as $cardData) {
                    // There are duplicated tokens for some reason
                    if ($prevUuid === $cardData['uuid']) {
                        $io->writeln('Found duplicated uuid: ' . $cardData['uuid']);
                        $index++;
                        continue;
                    }

                    $exists = $cardRepo->findOneBy(['id' => $cardData['uuid']]);

                    if ($exists) {
                        $card = $exists;
                    } else {
                        $card = new Card();
                        $card->setId($cardData['uuid']);
                    }

                    $card
                        ->setArtist($cardData['artist'] ?? '')
                        ->setBorderColor($cardData['borderColor'])
                        ->setColorIdentity($cardData['colorIdentity'])
                        ->setColors($cardData['colors'])
                        ->setConvertedManaCost((float) ($cardData['convertedManaCost'] ?? 0))
                        ->setFrameVersion($cardData['frameVersion'])
                        ->setScryfallId($cardData['identifiers']['scryfallId'] ?? null)
                        ->setScryfallIllustrationId($cardData['identifiers']['scryfallIllustrationId'] ?? null)
                        ->setScryfallOracleId($cardData['identifiers']['scryfallOracleId'] ?? null)
                        ->setLayout($cardData['layout'])
                        ->setManaCost($cardData['manaCost'] ?? '{0}')
                        ->setNumber($cardData['number'])
                        ->setPrintings(array_map('strtolower', $cardData['printings'] ?? [strtolower($cardData['setCode'])]))
                        ->setRarity($cardData['rarity'] ?? 'common')
                        ->setSetCode(strtolower($cardData['setCode']))
                        ->setSubtypes($cardData['subtypes'])
                        ->setSupertypes($cardData['supertypes'])
                        ->setTypes($cardData['types'])
                        ->setSide($cardData['side'] ?? null)
                    ;

                    $languageData = $card->getEnTexts();
                    $languageData
                        ->setMultiverseId($cardData['identifiers']['multiverseId'] ?? null)
                        ->setName($cardData['name'])
                        ->setType($cardData['type'])
                        ->setFlavorText($cardData['flavorText'] ?? '')
                        ->setText($cardData['text'] ?? '')
                    ;

                    foreach ($cardData['foreignData'] ?? [] as $foreignCardData) {
                        $language = $this->languageMapper->languageToCode($foreignCardData['language']);

                        /** @var CardLanguageData $languageData */
                        $languageData = $card->{'get' . ucfirst($language) . 'Texts'}();

                        $languageData
                            ->setMultiverseId($foreignCardData['multiverseId'] ?? null)
                            ->setName($foreignCardData['name'])
                            ->setType($foreignCardData['type'] ?? '')
                            ->setFlavorText($foreignCardData['flavorText'] ?? '')
                            ->setText($foreignCardData['text'] ?? '')
                        ;
                    }

                    if (!$exists) {
                        $this->em->persist($card);
                    }

                    $prevUuid = $cardData['uuid'];

                    if ($loops === 20) {
                        $loops = 0;
                        $this->em->flush();
                        $this->em->clear();
                    }

                    $loops++;
                    $cardProgress->advance();
                }
            }

            $io->write("\033[1A");
            $setsProgress->advance();
            $index++;
        }

        $setsProgress->finish();

        $io->success('Finished!');

        return Command::SUCCESS;
    }
}
