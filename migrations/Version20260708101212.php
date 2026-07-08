<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708101212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sync collected_cards.edition/number with their card (drifts when MTGJSON later corrects a set code/number) and index them for sorting the collection';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE collected_cards cc
             JOIN cards c ON c.id = cc.card_id
             SET cc.edition = c.set_code, cc.number = c.number
             WHERE cc.edition != c.set_code OR cc.number != c.number'
        );

        $this->addSql('CREATE INDEX IDX_891E91B5A76ED395A891181F96901F54 ON collected_cards (user_id, edition, number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_891E91B5A76ED395A891181F96901F54 ON collected_cards');
    }
}
