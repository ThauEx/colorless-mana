<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260704190040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Baseline after DBAL 4 upgrade: drop DC2Type column comments, native JSON for user.roles';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cards CHANGE id id CHAR(36) NOT NULL, CHANGE color_identity color_identity LONGTEXT NOT NULL, CHANGE colors colors LONGTEXT NOT NULL, CHANGE scryfall_id scryfall_id CHAR(36) DEFAULT NULL, CHANGE scryfall_illustration_id scryfall_illustration_id CHAR(36) DEFAULT NULL, CHANGE scryfall_oracle_id scryfall_oracle_id CHAR(36) DEFAULT NULL, CHANGE printings printings LONGTEXT NOT NULL, CHANGE subtypes subtypes LONGTEXT NOT NULL, CHANGE supertypes supertypes LONGTEXT NOT NULL, CHANGE types types LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE collected_cards CHANGE card_id card_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE uuid uuid CHAR(36) NOT NULL, CHANGE roles roles JSON NOT NULL, CHANGE settings settings LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE wishlist CHANGE uuid uuid CHAR(36) NOT NULL, CHANGE scryfall_oracle_id scryfall_oracle_id CHAR(36) NOT NULL, CHANGE languages languages LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE wishlist_card CHANGE card_id card_id CHAR(36) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cards CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE color_identity color_identity LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', CHANGE colors colors LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', CHANGE scryfall_id scryfall_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE scryfall_illustration_id scryfall_illustration_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE scryfall_oracle_id scryfall_oracle_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE printings printings LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', CHANGE subtypes subtypes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', CHANGE supertypes supertypes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', CHANGE types types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE collected_cards CHANGE card_id card_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', CHANGE settings settings LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE wishlist CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE languages languages LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE scryfall_oracle_id scryfall_oracle_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE wishlist_card CHANGE card_id card_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }
}
