<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709131342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the sets table (code + release date, from Scryfall) and an empty set_release_date column on collected_cards. Run mtgjson:sync:sets before the follow-up migration that backfills and indexes it.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE sets (
              code VARCHAR(16) NOT NULL,
              release_date VARCHAR(10) NOT NULL,
              PRIMARY KEY (code)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql('ALTER TABLE collected_cards ADD set_release_date VARCHAR(10) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sets');
        $this->addSql('ALTER TABLE collected_cards DROP set_release_date');
    }
}
