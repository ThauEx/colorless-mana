<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Removes leftover card rows from earlier imports (e.g. spoiler-season data whose
 * MTGJSON uuid changed, leaving the old row behind). A card row is considered
 * stale when the last card import did not touch it, i.e. finishes is NULL.
 * Collection and wishlist references are re-pointed to the current row with the
 * same Scryfall id and side before stale rows are deleted.
 */
#[AsCommand(name: 'mtgjson:cleanup:stale-cards', description: 'Re-points references and deletes card rows the last import did not touch')]
class MtgjsonCleanupStaleCardsCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Apply the changes (default is a dry run)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $current = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM cards WHERE finishes IS NOT NULL');
        $stale = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM cards WHERE finishes IS NULL');

        if ($current < $stale) {
            $io->error(sprintf(
                'Only %d of %d cards look up to date. Run mtgjson:import:cards with a full AllPrintings file first.',
                $current,
                $current + $stale
            ));

            return Command::FAILURE;
        }

        $successorJoin = 'LEFT JOIN cards cur ON cur.scryfall_id = c.scryfall_id AND cur.finishes IS NOT NULL AND COALESCE(cur.side, \'\') = COALESCE(c.side, \'\')';

        $repointable = $this->connection->fetchAllAssociative(
            "SELECT cc.id AS cc_id, cur.id AS successor_id, cur.set_code, cur.number,
                    conflict.id AS conflict_id
             FROM collected_cards cc
             JOIN cards c ON c.id = cc.card_id AND c.finishes IS NULL
             $successorJoin
             LEFT JOIN collected_cards conflict
                    ON conflict.user_id = cc.user_id
                   AND conflict.card_id = cur.id
                   AND conflict.language = cc.language
                   AND conflict.id != cc.id
             WHERE cur.id IS NOT NULL"
        );

        $wishlistRepointable = $this->connection->fetchAllAssociative(
            "SELECT wc.wishlist_id, wc.card_id, cur.id AS successor_id
             FROM wishlist_card wc
             JOIN cards c ON c.id = wc.card_id AND c.finishes IS NULL
             $successorJoin
             WHERE cur.id IS NOT NULL"
        );

        $orphans = $this->connection->fetchAllAssociative(
            "SELECT c.set_code, c.number, c.en_name
             FROM cards c
             $successorJoin
             WHERE c.finishes IS NULL AND cur.id IS NULL
               AND (EXISTS (SELECT 1 FROM collected_cards cc WHERE cc.card_id = c.id)
                    OR EXISTS (SELECT 1 FROM wishlist_card wc WHERE wc.card_id = c.id))"
        );

        $deletable = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM cards c
             WHERE c.finishes IS NULL
               AND NOT EXISTS (SELECT 1 FROM collected_cards cc WHERE cc.card_id = c.id)
               AND NOT EXISTS (SELECT 1 FROM wishlist_card wc WHERE wc.card_id = c.id)'
        );

        $io->listing([
            sprintf('%d stale card rows total', $stale),
            sprintf('%d collection entries will be re-pointed (%d of them merged into an existing entry)', count($repointable), count(array_filter($repointable, static fn (array $r) => $r['conflict_id'] !== null))),
            sprintf('%d wishlist entries will be re-pointed', count($wishlistRepointable)),
            sprintf('%d stale rows will be deleted', $deletable),
            sprintf('%d stale rows are kept because they are referenced but have no successor', count($orphans)),
        ]);

        foreach ($orphans as $orphan) {
            $io->writeln(sprintf('  kept: %s %s "%s"', $orphan['set_code'], $orphan['number'], $orphan['en_name']));
        }

        if (!$force) {
            $io->note('Dry run only. Re-run with --force to apply.');

            return Command::SUCCESS;
        }

        $this->connection->beginTransaction();

        try {
            foreach ($repointable as $row) {
                if ($row['conflict_id'] !== null) {
                    $this->connection->executeStatement(
                        'UPDATE collected_cards conflict
                         JOIN collected_cards cc ON cc.id = :ccId
                         SET conflict.non_foil_quantity = conflict.non_foil_quantity + cc.non_foil_quantity,
                             conflict.foil_quantity = conflict.foil_quantity + cc.foil_quantity
                         WHERE conflict.id = :conflictId',
                        ['ccId' => $row['cc_id'], 'conflictId' => $row['conflict_id']]
                    );
                    $this->connection->executeStatement('DELETE FROM collected_cards WHERE id = :id', ['id' => $row['cc_id']]);

                    continue;
                }

                $this->connection->executeStatement(
                    'UPDATE collected_cards SET card_id = :successorId, edition = :edition, number = :number WHERE id = :id',
                    ['successorId' => $row['successor_id'], 'edition' => $row['set_code'], 'number' => $row['number'], 'id' => $row['cc_id']]
                );
            }

            foreach ($wishlistRepointable as $row) {
                $this->connection->executeStatement(
                    'UPDATE IGNORE wishlist_card SET card_id = :successorId WHERE wishlist_id = :wishlistId AND card_id = :cardId',
                    ['successorId' => $row['successor_id'], 'wishlistId' => $row['wishlist_id'], 'cardId' => $row['card_id']]
                );
            }

            $deleted = $this->connection->executeStatement(
                'DELETE c FROM cards c
                 WHERE c.finishes IS NULL
                   AND NOT EXISTS (SELECT 1 FROM collected_cards cc WHERE cc.card_id = c.id)
                   AND NOT EXISTS (SELECT 1 FROM wishlist_card wc WHERE wc.card_id = c.id)'
            );

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }

        $io->success(sprintf('Deleted %d stale card rows.', $deleted));

        return Command::SUCCESS;
    }
}
