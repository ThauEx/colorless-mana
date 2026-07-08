<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708094235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index cards.scryfall_oracle_id (only covered by the FULLTEXT index before, unusable for IN() lookups)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_4C258FDD3A58DC1 ON cards (scryfall_oracle_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_4C258FDD3A58DC1 ON cards');
    }
}
