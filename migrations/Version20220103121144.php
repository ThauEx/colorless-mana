<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103121144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_price (id INT AUTO_INCREMENT NOT NULL, card_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', cardkingdom_average_day_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_week_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_month_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_day_foil DOUBLE PRECISION NOT NULL, cardkingdom_average_week_foil DOUBLE PRECISION NOT NULL, cardkingdom_average_month_foil DOUBLE PRECISION NOT NULL, cardmarket_average_day_normal DOUBLE PRECISION NOT NULL, cardmarket_average_week_normal DOUBLE PRECISION NOT NULL, cardmarket_average_month_normal DOUBLE PRECISION NOT NULL, cardmarket_average_day_foil DOUBLE PRECISION NOT NULL, cardmarket_average_week_foil DOUBLE PRECISION NOT NULL, cardmarket_average_month_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_day_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_week_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_month_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_day_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_week_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_month_foil DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_F2FCD31F4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card_price ADD CONSTRAINT FK_F2FCD31F4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE card_price');
    }
}
