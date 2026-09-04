<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Backfills collected_cards.set_release_date from the sets table and indexes it
 * for sorting the collection oldest-first.
 *
 * MUST run after mtgjson:sync:sets (or a mtgjson:import:cards run, which now
 * calls it automatically) has populated the sets table.
 */
final class Version20260709131343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Backfill collected_cards.set_release_date from sets and index it';
    }

    public function up(Schema $schema): void
    {
        $setCount = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM sets');
        $this->abortIf($setCount === 0, 'The sets table is empty. Run mtgjson:sync:sets first.');

        $this->addSql(
            'UPDATE collected_cards cc
             JOIN sets s ON s.code = cc.edition
             SET cc.set_release_date = s.release_date
             WHERE cc.set_release_date = \'\''
        );

        $this->addSql('CREATE INDEX IDX_891E91B5A76ED3952A2928CE96901F54 ON collected_cards (user_id, set_release_date, number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_891E91B5A76ED3952A2928CE96901F54 ON collected_cards');
        $this->addSql('UPDATE collected_cards SET set_release_date = \'\'');
    }
}
