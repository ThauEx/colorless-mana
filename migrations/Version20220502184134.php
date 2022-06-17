<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220502184134 extends AbstractMigration
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
        $this->addSql('ALTER TABLE cards ADD cardkingdom_price_normal DOUBLE PRECISION NOT NULL, ADD cardkingdom_price_foil DOUBLE PRECISION NOT NULL, ADD cardmarket_price_normal DOUBLE PRECISION NOT NULL, ADD cardmarket_price_foil DOUBLE PRECISION NOT NULL, ADD tcgplayer_price_normal DOUBLE PRECISION NOT NULL, ADD tcgplayer_price_foil DOUBLE PRECISION NOT NULL, ADD en_flavor_text LONGTEXT DEFAULT NULL, ADD en_multiverse_id INT DEFAULT NULL, ADD en_name LONGTEXT DEFAULT NULL, ADD en_text LONGTEXT DEFAULT NULL, ADD en_type LONGTEXT DEFAULT NULL, ADD es_flavor_text LONGTEXT DEFAULT NULL, ADD es_multiverse_id INT DEFAULT NULL, ADD es_name LONGTEXT DEFAULT NULL, ADD es_text LONGTEXT DEFAULT NULL, ADD es_type LONGTEXT DEFAULT NULL, ADD fr_flavor_text LONGTEXT DEFAULT NULL, ADD fr_multiverse_id INT DEFAULT NULL, ADD fr_name LONGTEXT DEFAULT NULL, ADD fr_text LONGTEXT DEFAULT NULL, ADD fr_type LONGTEXT DEFAULT NULL, ADD de_flavor_text LONGTEXT DEFAULT NULL, ADD de_multiverse_id INT DEFAULT NULL, ADD de_name LONGTEXT DEFAULT NULL, ADD de_text LONGTEXT DEFAULT NULL, ADD de_type LONGTEXT DEFAULT NULL, ADD it_flavor_text LONGTEXT DEFAULT NULL, ADD it_multiverse_id INT DEFAULT NULL, ADD it_name LONGTEXT DEFAULT NULL, ADD it_text LONGTEXT DEFAULT NULL, ADD it_type LONGTEXT DEFAULT NULL, ADD pt_flavor_text LONGTEXT DEFAULT NULL, ADD pt_multiverse_id INT DEFAULT NULL, ADD pt_name LONGTEXT DEFAULT NULL, ADD pt_text LONGTEXT DEFAULT NULL, ADD pt_type LONGTEXT DEFAULT NULL, ADD ja_flavor_text LONGTEXT DEFAULT NULL, ADD ja_multiverse_id INT DEFAULT NULL, ADD ja_name LONGTEXT DEFAULT NULL, ADD ja_text LONGTEXT DEFAULT NULL, ADD ja_type LONGTEXT DEFAULT NULL, ADD ko_flavor_text LONGTEXT DEFAULT NULL, ADD ko_multiverse_id INT DEFAULT NULL, ADD ko_name LONGTEXT DEFAULT NULL, ADD ko_text LONGTEXT DEFAULT NULL, ADD ko_type LONGTEXT DEFAULT NULL, ADD ru_flavor_text LONGTEXT DEFAULT NULL, ADD ru_multiverse_id INT DEFAULT NULL, ADD ru_name LONGTEXT DEFAULT NULL, ADD ru_text LONGTEXT DEFAULT NULL, ADD ru_type LONGTEXT DEFAULT NULL, ADD zhs_flavor_text LONGTEXT DEFAULT NULL, ADD zhs_multiverse_id INT DEFAULT NULL, ADD zhs_name LONGTEXT DEFAULT NULL, ADD zhs_text LONGTEXT DEFAULT NULL, ADD zhs_type LONGTEXT DEFAULT NULL, ADD zht_flavor_text LONGTEXT DEFAULT NULL, ADD zht_multiverse_id INT DEFAULT NULL, ADD zht_name LONGTEXT DEFAULT NULL, ADD zht_text LONGTEXT DEFAULT NULL, ADD zht_type LONGTEXT DEFAULT NULL, ADD he_flavor_text LONGTEXT DEFAULT NULL, ADD he_multiverse_id INT DEFAULT NULL, ADD he_name LONGTEXT DEFAULT NULL, ADD he_text LONGTEXT DEFAULT NULL, ADD he_type LONGTEXT DEFAULT NULL, ADD la_flavor_text LONGTEXT DEFAULT NULL, ADD la_multiverse_id INT DEFAULT NULL, ADD la_name LONGTEXT DEFAULT NULL, ADD la_text LONGTEXT DEFAULT NULL, ADD la_type LONGTEXT DEFAULT NULL, ADD grc_flavor_text LONGTEXT DEFAULT NULL, ADD grc_multiverse_id INT DEFAULT NULL, ADD grc_name LONGTEXT DEFAULT NULL, ADD grc_text LONGTEXT DEFAULT NULL, ADD grc_type LONGTEXT DEFAULT NULL, ADD ar_flavor_text LONGTEXT DEFAULT NULL, ADD ar_multiverse_id INT DEFAULT NULL, ADD ar_name LONGTEXT DEFAULT NULL, ADD ar_text LONGTEXT DEFAULT NULL, ADD ar_type LONGTEXT DEFAULT NULL, ADD sa_flavor_text LONGTEXT DEFAULT NULL, ADD sa_multiverse_id INT DEFAULT NULL, ADD sa_name LONGTEXT DEFAULT NULL, ADD sa_text LONGTEXT DEFAULT NULL, ADD sa_type LONGTEXT DEFAULT NULL, ADD ph_flavor_text LONGTEXT DEFAULT NULL, ADD ph_multiverse_id INT DEFAULT NULL, ADD ph_name LONGTEXT DEFAULT NULL, ADD ph_text LONGTEXT DEFAULT NULL, ADD ph_type LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card_price (id INT AUTO_INCREMENT NOT NULL, card_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\', cardkingdom_average_day_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_week_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_month_normal DOUBLE PRECISION NOT NULL, cardkingdom_average_day_foil DOUBLE PRECISION NOT NULL, cardkingdom_average_week_foil DOUBLE PRECISION NOT NULL, cardkingdom_average_month_foil DOUBLE PRECISION NOT NULL, cardmarket_average_day_normal DOUBLE PRECISION NOT NULL, cardmarket_average_week_normal DOUBLE PRECISION NOT NULL, cardmarket_average_month_normal DOUBLE PRECISION NOT NULL, cardmarket_average_day_foil DOUBLE PRECISION NOT NULL, cardmarket_average_week_foil DOUBLE PRECISION NOT NULL, cardmarket_average_month_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_day_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_week_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_month_normal DOUBLE PRECISION NOT NULL, tcgplayer_average_day_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_week_foil DOUBLE PRECISION NOT NULL, tcgplayer_average_month_foil DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_F2FCD31F4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE language_data (id INT AUTO_INCREMENT NOT NULL, card_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\', flavor_text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, language LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, multiverse_id INT DEFAULT NULL, name LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_9FFA192F4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE card_price ADD CONSTRAINT FK_F2FCD31F4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
        $this->addSql('ALTER TABLE language_data ADD CONSTRAINT FK_9FFA192F4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
        $this->addSql('ALTER TABLE cards DROP cardkingdom_price_normal, DROP cardkingdom_price_foil, DROP cardmarket_price_normal, DROP cardmarket_price_foil, DROP tcgplayer_price_normal, DROP tcgplayer_price_foil, DROP en_flavor_text, DROP en_multiverse_id, DROP en_name, DROP en_text, DROP en_type, DROP es_flavor_text, DROP es_multiverse_id, DROP es_name, DROP es_text, DROP es_type, DROP fr_flavor_text, DROP fr_multiverse_id, DROP fr_name, DROP fr_text, DROP fr_type, DROP de_flavor_text, DROP de_multiverse_id, DROP de_name, DROP de_text, DROP de_type, DROP it_flavor_text, DROP it_multiverse_id, DROP it_name, DROP it_text, DROP it_type, DROP pt_flavor_text, DROP pt_multiverse_id, DROP pt_name, DROP pt_text, DROP pt_type, DROP ja_flavor_text, DROP ja_multiverse_id, DROP ja_name, DROP ja_text, DROP ja_type, DROP ko_flavor_text, DROP ko_multiverse_id, DROP ko_name, DROP ko_text, DROP ko_type, DROP ru_flavor_text, DROP ru_multiverse_id, DROP ru_name, DROP ru_text, DROP ru_type, DROP zhs_flavor_text, DROP zhs_multiverse_id, DROP zhs_name, DROP zhs_text, DROP zhs_type, DROP zht_flavor_text, DROP zht_multiverse_id, DROP zht_name, DROP zht_text, DROP zht_type, DROP he_flavor_text, DROP he_multiverse_id, DROP he_name, DROP he_text, DROP he_type, DROP la_flavor_text, DROP la_multiverse_id, DROP la_name, DROP la_text, DROP la_type, DROP grc_flavor_text, DROP grc_multiverse_id, DROP grc_name, DROP grc_text, DROP grc_type, DROP ar_flavor_text, DROP ar_multiverse_id, DROP ar_name, DROP ar_text, DROP ar_type, DROP sa_flavor_text, DROP sa_multiverse_id, DROP sa_name, DROP sa_text, DROP sa_type, DROP ph_flavor_text, DROP ph_multiverse_id, DROP ph_name, DROP ph_text, DROP ph_type');
    }
}