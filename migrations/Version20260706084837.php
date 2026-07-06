<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Collection model: one row per (user, card, language, finish) with a single
 * quantity instead of non_foil_quantity/foil_quantity columns per row.
 *
 * The foil part of a row is mapped to the card's effective foil finish
 * (e.g. "surgefoil") when it is unambiguous, otherwise to plain "foil".
 *
 * Uses direct statements instead of addSql because data transformation and
 * DDL have to interleave in a defined order.
 */
final class Version20260706084837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Collection: one row per finish with a single quantity';
    }

    public function up(Schema $schema): void
    {
        // Merge duplicate (user, card, language) rows so the unique constraint can be created
        $duplicates = $this->connection->fetchAllAssociative(
            'SELECT MIN(id) AS keep_id, SUM(non_foil_quantity) AS nf, SUM(foil_quantity) AS f,
                    GROUP_CONCAT(id) AS ids
             FROM collected_cards
             GROUP BY user_id, card_id, language
             HAVING COUNT(*) > 1'
        );

        foreach ($duplicates as $dupe) {
            $this->connection->executeStatement(
                'UPDATE collected_cards SET non_foil_quantity = :nf, foil_quantity = :f WHERE id = :id',
                ['nf' => $dupe['nf'], 'f' => $dupe['f'], 'id' => $dupe['keep_id']]
            );
            $ids = array_diff(explode(',', $dupe['ids']), [(string) $dupe['keep_id']]);
            $this->connection->executeStatement(
                'DELETE FROM collected_cards WHERE id IN (:ids)',
                ['ids' => $ids],
                ['ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER]
            );
        }

        $this->connection->executeStatement(
            "ALTER TABLE collected_cards
             ADD finish VARCHAR(32) DEFAULT 'nonfoil' NOT NULL,
             ADD quantity INT DEFAULT 0 NOT NULL"
        );

        // Rows with a non-foil part keep their identity as the nonfoil row
        $this->connection->executeStatement(
            'UPDATE collected_cards SET quantity = non_foil_quantity WHERE non_foil_quantity > 0'
        );

        // Rows with a foil part either become the foil row (no non-foil part)
        // or get a new sibling row for it
        $foilRows = $this->connection->fetchAllAssociative(
            'SELECT cc.id, cc.user_id, cc.card_id, cc.edition, cc.number, cc.language,
                    cc.non_foil_quantity, cc.foil_quantity, c.finishes
             FROM collected_cards cc
             LEFT JOIN cards c ON c.id = cc.card_id
             WHERE cc.foil_quantity > 0'
        );

        foreach ($foilRows as $row) {
            $finish = $this->foilFinish(json_decode($row['finishes'] ?? '[]', true) ?: []);

            if ((int) $row['non_foil_quantity'] === 0) {
                $this->connection->executeStatement(
                    'UPDATE collected_cards SET finish = :finish, quantity = :quantity WHERE id = :id',
                    ['finish' => $finish, 'quantity' => $row['foil_quantity'], 'id' => $row['id']]
                );

                continue;
            }

            $this->connection->executeStatement(
                'INSERT INTO collected_cards (user_id, card_id, edition, number, language, finish, quantity, non_foil_quantity, foil_quantity)
                 VALUES (:userId, :cardId, :edition, :number, :language, :finish, :quantity, 0, 0)',
                [
                    'userId'   => $row['user_id'],
                    'cardId'   => $row['card_id'],
                    'edition'  => $row['edition'],
                    'number'   => $row['number'],
                    'language' => $row['language'],
                    'finish'   => $finish,
                    'quantity' => $row['foil_quantity'],
                ]
            );
        }

        $this->connection->executeStatement('ALTER TABLE collected_cards DROP non_foil_quantity, DROP foil_quantity');

        $this->repairUnavailableFinishes();

        $this->connection->executeStatement(
            'CREATE UNIQUE INDEX uniq_collected_card_entry ON collected_cards (user_id, card_id, language, finish)'
        );
    }

    /**
     * Entries whose finish does not exist on their card come from the old
     * two-column model. Foil-only variants live as separate "★" cards on
     * Scryfall, so those entries move there; entries on single-finish cards
     * get that finish. Anything else is left for manual review.
     */
    private function repairUnavailableFinishes(): void
    {
        // Requires cards.finishes to be populated; without a full card import
        // every entry would look mismatched
        $withoutFinishes = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM cards WHERE finishes IS NULL');
        $this->abortIf(
            $withoutFinishes > (int) $this->connection->fetchOne('SELECT COUNT(*) FROM cards WHERE finishes IS NOT NULL'),
            'Most cards have no finishes yet. Run mtgjson:import:cards with a full AllPrintings file first.'
        );

        $mismatches = $this->connection->fetchAllAssociative(
            "SELECT cc.id, cc.user_id, cc.language, cc.finish, cc.quantity, cc.card_id,
                    c.finishes AS card_finishes,
                    star.id AS star_id, star.number AS star_number, star.set_code AS star_set_code, star.finishes AS star_finishes
             FROM collected_cards cc
             JOIN cards c ON c.id = cc.card_id
             LEFT JOIN cards star ON star.set_code = c.set_code AND star.number = CONCAT(c.number, '★')
             WHERE NOT JSON_CONTAINS(COALESCE(c.finishes, '[]'), JSON_QUOTE(cc.finish))"
        );

        foreach ($mismatches as $row) {
            // "★" cards are the foil variants, only foil entries may move there
            if ($row['star_id'] !== null && $row['finish'] !== 'nonfoil') {
                $starFinishes = json_decode($row['star_finishes'] ?? '[]', true) ?: [];
                $targetCardId = $row['star_id'];
                $targetFinish = count($starFinishes) === 1 ? $starFinishes[0] : 'foil';
                $targetNumber = $row['star_number'];
            } else {
                $cardFinishes = json_decode($row['card_finishes'] ?? '[]', true) ?: [];

                if (count($cardFinishes) !== 1) {
                    continue;
                }

                $targetCardId = $row['card_id'];
                $targetFinish = $cardFinishes[0];
                $targetNumber = null;
            }

            $existing = $this->connection->fetchOne(
                'SELECT id FROM collected_cards
                 WHERE user_id = :userId AND card_id = :cardId AND language = :language AND finish = :finish AND id != :id',
                [
                    'userId'   => $row['user_id'],
                    'cardId'   => $targetCardId,
                    'language' => $row['language'],
                    'finish'   => $targetFinish,
                    'id'       => $row['id'],
                ]
            );

            if ($existing !== false) {
                $this->connection->executeStatement(
                    'UPDATE collected_cards SET quantity = quantity + :quantity WHERE id = :id',
                    ['quantity' => $row['quantity'], 'id' => $existing]
                );
                $this->connection->executeStatement('DELETE FROM collected_cards WHERE id = :id', ['id' => $row['id']]);

                continue;
            }

            $this->connection->executeStatement(
                'UPDATE collected_cards
                 SET card_id = :cardId, finish = :finish, number = COALESCE(:number, number)
                 WHERE id = :id',
                [
                    'cardId' => $targetCardId,
                    'finish' => $targetFinish,
                    'number' => $targetNumber,
                    'id'     => $row['id'],
                ]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore a database backup instead.');
    }

    /** @param string[] $cardFinishes */
    private function foilFinish(array $cardFinishes): string
    {
        $candidates = array_values(array_diff($cardFinishes, ['nonfoil']));

        if (in_array('foil', $candidates, true) || count($candidates) !== 1) {
            return 'foil';
        }

        return $candidates[0];
    }
}
