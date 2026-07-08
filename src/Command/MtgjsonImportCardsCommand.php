<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardLanguageData;
use App\Helper\MtgjsonFinishResolver;
use App\Helper\LanguageMapper;
use Doctrine\ORM\EntityManagerInterface;
use JsonMachine\Items as JsonMachine;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Symfony\Component\Cache\Adapter\NullAdapter;
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
    private const BATCH_SIZE = 250;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LanguageMapper $languageMapper,
        private readonly MtgjsonFinishResolver $finishResolver,
    ) {
        $this->em->getConnection()->getConfiguration()->setResultCache(new NullAdapter());

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Path to the json file')
            ->addOption('set', 's', InputOption::VALUE_OPTIONAL, 'Only import the given set')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $onlySet = $input->getOption('set');

        if (!file_exists($path)) {
            $io->error('File does not exist.');

            return Command::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $sets = JsonMachine::fromFile($path, ['pointer' => '/data', 'decoder' => new ExtJsonDecoder(true)]);

        foreach ($sets as $set) {
            if ($onlySet !== null && strcasecmp((string) $set['code'], (string) $onlySet) !== 0) {
                continue;
            }

            $batch = [];
            $seenUuids = [];

            foreach ($set['cards'] as $cardData) {
                if (!in_array('paper', $cardData['availability'] ?? [], true)) {
                    continue;
                }

                // Only front faces are stored; they carry the full "A // B" name
                // and share the print's Scryfall id with their back faces
                if (($cardData['side'] ?? 'a') !== 'a') {
                    continue;
                }

                if (empty($cardData['identifiers']['scryfallId'])) {
                    $skipped++;

                    continue;
                }

                if (isset($seenUuids[$cardData['uuid']])) {
                    continue;
                }
                $seenUuids[$cardData['uuid']] = true;

                $batch[] = $cardData;

                if (count($batch) >= self::BATCH_SIZE) {
                    [$c, $u] = $this->processBatch($batch);
                    $created += $c;
                    $updated += $u;
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                [$c, $u] = $this->processBatch($batch);
                $created += $c;
                $updated += $u;
            }

            $io->writeln(sprintf('%s done (created: %d, updated: %d)', $set['code'], $created, $updated));
        }

        $io->success(sprintf('Finished! Created %d, updated %d, skipped %d cards without Scryfall id.', $created, $updated, $skipped));

        return Command::SUCCESS;
    }

    /** @return array{0: int, 1: int} numbers of created and updated cards */
    private function processBatch(array $batch): array
    {
        $existing = $this->findExisting($batch);
        $created = 0;
        $updated = 0;

        foreach ($batch as $cardData) {
            $key = strtolower((string) $cardData['identifiers']['scryfallId']);

            $card = $existing[$key] ?? null;

            if ($card === null) {
                $card = new Card();
                $card->setId($cardData['identifiers']['scryfallId']);
                $this->em->persist($card);
                // Later occurrences of the same print in this batch must update
                // this instance instead of creating a second row.
                $existing[$key] = $card;
                $created++;
            } else {
                $updated++;
            }

            $this->populateCard($card, $cardData);
        }

        $this->em->flush();
        $this->em->clear();

        return [$created, $updated];
    }

    /** @return array<string, Card> existing cards keyed by their lowercased Scryfall id */
    private function findExisting(array $batch): array
    {
        $scryfallIds = array_values(array_unique(array_map(
            static fn (array $cardData) => $cardData['identifiers']['scryfallId'],
            $batch
        )));

        $existing = [];

        foreach ($this->em->getRepository(Card::class)->findBy(['id' => $scryfallIds]) as $card) {
            $existing[strtolower((string) $card->getId())] = $card;
        }

        return $existing;
    }

    private function populateCard(Card $card, array $cardData): void
    {
        $card
            ->setArtist($cardData['artist'] ?? '')
            ->setBorderColor($cardData['borderColor'])
            ->setColorIdentity($cardData['colorIdentity'])
            ->setColors($cardData['colors'])
            ->setConvertedManaCost((float) ($cardData['convertedManaCost'] ?? 0))
            ->setFrameVersion($cardData['frameVersion'])
            ->setMtgjsonUuid($cardData['uuid'])
            ->setScryfallIllustrationId($cardData['identifiers']['scryfallIllustrationId'] ?? null)
            ->setScryfallOracleId($cardData['identifiers']['scryfallOracleId'] ?? null)
            ->setLayout($cardData['layout'])
            ->setManaCost($cardData['manaCost'] ?? '{0}')
            ->setNumber($cardData['number'])
            ->setPrintings(array_map(strtolower(...), $cardData['printings'] ?? [$cardData['setCode']]))
            ->setRarity($cardData['rarity'] ?? 'common')
            ->setSetCode(strtolower((string) $cardData['setCode']))
            ->setSubtypes($cardData['subtypes'])
            ->setSupertypes($cardData['supertypes'])
            ->setTypes($cardData['types'])
            ->setSide($cardData['side'] ?? null)
            ->setFinishes($this->finishResolver->effectiveFinishes($cardData))
        ;

        $card
            ->getEnTexts()
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
    }

}
