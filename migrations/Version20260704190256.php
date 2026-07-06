<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260704190256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cards.mtgjson_uuid (backfilled from id) and cards.finishes, index mtgjson_uuid and scryfall_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cards ADD mtgjson_uuid CHAR(36) DEFAULT NULL, ADD finishes JSON DEFAULT NULL');
        $this->addSql('UPDATE cards SET mtgjson_uuid = id');
        $this->addSql('CREATE INDEX IDX_4C258FD5E028D3E ON cards (mtgjson_uuid)');
        $this->addSql('CREATE INDEX IDX_4C258FDC4213070 ON cards (scryfall_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_4C258FD5E028D3E ON cards');
        $this->addSql('DROP INDEX IDX_4C258FDC4213070 ON cards');
        $this->addSql('ALTER TABLE cards DROP mtgjson_uuid, DROP finishes');
    }
}
