<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Switches the cards primary key from the MTGJSON uuid to the Scryfall id.
 *
 * The MTGJSON uuid is a hash over the card data and changes whenever the data
 * is corrected, which used to leave duplicate rows behind. The Scryfall id is
 * stable per physical print. Multi-faced cards share the Scryfall id across
 * their face rows, so only front faces (side NULL or 'a') are kept; the front
 * row carries the full "A // B" name. References to back-face rows are merged
 * into or re-pointed to the front row first.
 *
 * MUST run after a full mtgjson:import:cards run and mtgjson:cleanup:stale-cards
 * (both ensure every row has a Scryfall id and duplicates are gone).
 */
final class Version20260706075507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cards primary key: MTGJSON uuid -> Scryfall id, drop back-face rows';
    }

    public function up(Schema $schema): void
    {
        $missingScryfallId = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM cards WHERE scryfall_id IS NULL');
        $this->abortIf($missingScryfallId > 0, sprintf(
            '%d cards have no Scryfall id. Run mtgjson:import:cards and mtgjson:cleanup:stale-cards first.',
            $missingScryfallId
        ));

        $duplicates = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM (SELECT scryfall_id FROM cards WHERE side IS NULL OR side = 'a' GROUP BY scryfall_id HAVING COUNT(*) > 1) d"
        );
        $this->abortIf($duplicates > 0, sprintf(
            '%d Scryfall ids are shared by multiple front-face rows. Run mtgjson:cleanup:stale-cards first.',
            $duplicates
        ));

        // Merge collection entries pointing at a back face into an existing front-face entry
        $this->addSql(
            "UPDATE collected_cards tgt
             JOIN collected_cards src ON src.user_id = tgt.user_id AND src.language = tgt.language AND src.id != tgt.id
             JOIN cards bc ON bc.id = src.card_id AND bc.side IS NOT NULL AND bc.side != 'a'
             JOIN cards a ON a.scryfall_id = bc.scryfall_id AND a.side = 'a' AND a.id = tgt.card_id
             SET tgt.non_foil_quantity = tgt.non_foil_quantity + src.non_foil_quantity,
                 tgt.foil_quantity = tgt.foil_quantity + src.foil_quantity"
        );
        $this->addSql(
            "DELETE src FROM collected_cards src
             JOIN cards bc ON bc.id = src.card_id AND bc.side IS NOT NULL AND bc.side != 'a'
             JOIN cards a ON a.scryfall_id = bc.scryfall_id AND a.side = 'a'
             JOIN collected_cards tgt ON tgt.user_id = src.user_id AND tgt.language = src.language AND tgt.card_id = a.id
             WHERE src.id != tgt.id"
        );

        // Re-point the remaining back-face references to the front face
        $this->addSql(
            "UPDATE collected_cards src
             JOIN cards bc ON bc.id = src.card_id AND bc.side IS NOT NULL AND bc.side != 'a'
             JOIN cards a ON a.scryfall_id = bc.scryfall_id AND a.side = 'a'
             SET src.card_id = a.id, src.edition = a.set_code, src.number = a.number"
        );
        $this->addSql(
            "UPDATE IGNORE wishlist_card wc
             JOIN cards bc ON bc.id = wc.card_id AND bc.side IS NOT NULL AND bc.side != 'a'
             JOIN cards a ON a.scryfall_id = bc.scryfall_id AND a.side = 'a'
             SET wc.card_id = a.id"
        );
        $this->addSql(
            "DELETE wc FROM wishlist_card wc
             JOIN cards bc ON bc.id = wc.card_id AND bc.side IS NOT NULL AND bc.side != 'a'"
        );

        $this->addSql("DELETE FROM cards WHERE side IS NOT NULL AND side != 'a'");

        // Swap the key: references first, then the primary key itself
        $this->addSql('ALTER TABLE collected_cards DROP FOREIGN KEY FK_891E91B54ACC9A20');
        $this->addSql('ALTER TABLE wishlist_card DROP FOREIGN KEY FK_6D2B49254ACC9A20');

        $this->addSql('UPDATE collected_cards cc JOIN cards c ON c.id = cc.card_id SET cc.card_id = c.scryfall_id');
        $this->addSql('UPDATE wishlist_card wc JOIN cards c ON c.id = wc.card_id SET wc.card_id = c.scryfall_id');
        $this->addSql('UPDATE cards SET id = scryfall_id');

        $this->addSql('DROP INDEX IDX_4C258FDC4213070 ON cards');
        $this->addSql('ALTER TABLE cards DROP scryfall_id');

        $this->addSql('ALTER TABLE collected_cards ADD CONSTRAINT FK_891E91B54ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id)');
        $this->addSql('ALTER TABLE wishlist_card ADD CONSTRAINT FK_6D2B49254ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restore the pre-migration database backup instead.');
    }
}
