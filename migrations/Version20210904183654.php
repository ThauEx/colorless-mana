<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210904183654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cards (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', artist VARCHAR(255) NOT NULL, border_color VARCHAR(255) NOT NULL, color_identity LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', colors LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', converted_mana_cost DOUBLE PRECISION NOT NULL, frame_version VARCHAR(255) NOT NULL, scryfall_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', scryfall_illustration_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', scryfall_oracle_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', layout VARCHAR(255) NOT NULL, mana_cost VARCHAR(255) NOT NULL, number VARCHAR(255) NOT NULL, printings LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', rarity VARCHAR(255) NOT NULL, set_code VARCHAR(255) NOT NULL, subtypes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', supertypes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collected_cards (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, card_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', edition VARCHAR(255) NOT NULL, number VARCHAR(255) NOT NULL, language VARCHAR(3) NOT NULL, non_foil_quantity INT NOT NULL, foil_quantity INT NOT NULL, INDEX IDX_891E91B5A76ED395 (user_id), INDEX IDX_891E91B54ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE language_data (id INT AUTO_INCREMENT NOT NULL, card_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', flavor_text LONGTEXT DEFAULT NULL, language LONGTEXT DEFAULT NULL, multiverse_id INT DEFAULT NULL, name LONGTEXT DEFAULT NULL, text LONGTEXT DEFAULT NULL, type LONGTEXT DEFAULT NULL, INDEX IDX_9FFA192F4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tokens (id INT AUTO_INCREMENT NOT NULL, artist LONGTEXT DEFAULT NULL, asciiName LONGTEXT DEFAULT NULL, availability LONGTEXT DEFAULT NULL, borderColor LONGTEXT DEFAULT NULL, colorIdentity LONGTEXT DEFAULT NULL, colors LONGTEXT DEFAULT NULL, edhrecRank INT DEFAULT NULL, faceName LONGTEXT DEFAULT NULL, flavorText LONGTEXT DEFAULT NULL, frameEffects LONGTEXT DEFAULT NULL, frameVersion LONGTEXT DEFAULT NULL, hasFoil INT DEFAULT NULL, hasNonFoil INT DEFAULT NULL, isFullArt INT DEFAULT NULL, isPromo INT DEFAULT NULL, isReprint INT DEFAULT NULL, keywords LONGTEXT DEFAULT NULL, layout LONGTEXT DEFAULT NULL, mcmId LONGTEXT DEFAULT NULL, mtgArenaId LONGTEXT DEFAULT NULL, mtgjsonV4Id LONGTEXT DEFAULT NULL, multiverseId LONGTEXT DEFAULT NULL, name LONGTEXT DEFAULT NULL, number LONGTEXT DEFAULT NULL, originalText LONGTEXT DEFAULT NULL, originalType LONGTEXT DEFAULT NULL, power LONGTEXT DEFAULT NULL, promoTypes LONGTEXT DEFAULT NULL, reverseRelated LONGTEXT DEFAULT NULL, scryfallId LONGTEXT DEFAULT NULL, scryfallIllustrationId LONGTEXT DEFAULT NULL, scryfallOracleId LONGTEXT DEFAULT NULL, setCode LONGTEXT DEFAULT NULL, side LONGTEXT DEFAULT NULL, subtypes LONGTEXT DEFAULT NULL, supertypes LONGTEXT DEFAULT NULL, tcgplayerProductId LONGTEXT DEFAULT NULL, text LONGTEXT DEFAULT NULL, toughness LONGTEXT DEFAULT NULL, type LONGTEXT DEFAULT NULL, types LONGTEXT DEFAULT NULL, uuid LONGTEXT DEFAULT NULL, watermark LONGTEXT DEFAULT NULL, UNIQUE INDEX tokens_id_uindex (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', username VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', discord_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE collected_cards ADD CONSTRAINT FK_891E91B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE collected_cards ADD CONSTRAINT FK_891E91B54ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
        $this->addSql('ALTER TABLE language_data ADD CONSTRAINT FK_9FFA192F4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collected_cards DROP FOREIGN KEY FK_891E91B54ACC9A20');
        $this->addSql('ALTER TABLE language_data DROP FOREIGN KEY FK_9FFA192F4ACC9A20');
        $this->addSql('ALTER TABLE collected_cards DROP FOREIGN KEY FK_891E91B5A76ED395');
        $this->addSql('DROP TABLE cards');
        $this->addSql('DROP TABLE collected_cards');
        $this->addSql('DROP TABLE language_data');
        $this->addSql('DROP TABLE tokens');
        $this->addSql('DROP TABLE user');
    }
}
