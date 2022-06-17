<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210913101049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cards ADD side VARCHAR(1) DEFAULT NULL');
//        $this->addSql('ALTER TABLE collected_cards ADD CONSTRAINT FK_891E91B54ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
//        $this->addSql('CREATE INDEX IDX_891E91B54ACC9A20 ON collected_cards (card_id)');
//        $this->addSql('ALTER TABLE language_data ADD CONSTRAINT FK_9FFA192F4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
//        $this->addSql('CREATE INDEX IDX_9FFA192F4ACC9A20 ON language_data (card_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cards DROP side');
//        $this->addSql('ALTER TABLE collected_cards DROP FOREIGN KEY FK_891E91B54ACC9A20');
//        $this->addSql('DROP INDEX IDX_891E91B54ACC9A20 ON collected_cards');
//        $this->addSql('ALTER TABLE language_data DROP FOREIGN KEY FK_9FFA192F4ACC9A20');
//        $this->addSql('DROP INDEX IDX_9FFA192F4ACC9A20 ON language_data');
    }
}
