<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220508172831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card_price');
        $this->addSql('DROP TABLE language_data');
        $this->addSql('CREATE INDEX IDX_4C258FDD3A58DC1 ON cards (scryfall_oracle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_price (id INT AUTO_INCREMENT NOT NULL, card_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\', cardkingdom_average_day_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_week_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_month_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_day_foil DOUBLE PRECISION NOT NULL, cardkingdom_average_week_foil DOUBLE PRECISION NOT NULL, cardkingdom_average_month_foil DOUBLE PRECISION NOT NULL, cardmarket_average_day_normal DOUBLE PRECISION NOT NULL, cardmarket_average_week_normal DOUBLE PRECISION NOT NULL, cardmarket_average_month_normal DOUBLE PRECISION NOT NULL, cardmarket_average_day_foil DOUBLE PRECISION NOT NULL, cardmarket_average_week_foil DOUBLE PRECISION NOT NULL, cardmarket_average_month_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_day_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_week_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_month_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_day_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_week_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_month_foil DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_F2FCD31F4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE language_data (id INT AUTO_INCREMENT NOT NULL, card_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\', flavor_text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, language LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, multiverse_id INT DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_9FFA192F4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE card_price ADD CONSTRAINT FK_F2FCD31F4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
        $this->addSql('ALTER TABLE language_data ADD CONSTRAINT FK_9FFA192F4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
        $this->addSql('DROP INDEX IDX_4C258FDD3A58DC1 ON cards');
    }
}
