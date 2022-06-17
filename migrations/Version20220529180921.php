<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220529180921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('DROP INDEX IDX_4C258FDD3A58DC1 ON cards');
        $this->addSql('CREATE FULLTEXT INDEX IDX_4C258FDD3A58DC1B3382B16C703A53F6C4F36B7F88D061D6D5609F2493B ON cards (scryfall_oracle_id, en_name, es_name, fr_name, de_name, it_name, pt_name, ja_name, ko_name, ru_name, zhs_name, zht_name, he_name, la_name, grc_name, ar_name, sa_name, ph_name)');
        $this->addSql('CREATE INDEX IDX_891E91B5A891181F96901F54D4DB71B5 ON collected_cards (edition, number, language)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_4C258FDD3A58DC1D690F2D769D8E392936C2AAA1A2009A0B37DB95E413B ON cards');
        $this->addSql('CREATE INDEX IDX_4C258FDD3A58DC1 ON cards (scryfall_oracle_id)');
        $this->addSql('DROP INDEX IDX_891E91B5A891181F96901F54D4DB71B5 ON collected_cards');
    }
}
