<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220309153325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE markt_geografie_obstakel (markt_geografie_id INT NOT NULL, obstakel_id INT NOT NULL, PRIMARY KEY(markt_geografie_id, obstakel_id))');
        $this->addSql('CREATE INDEX IDX_F7ED8B2BE769C07F ON markt_geografie_obstakel (markt_geografie_id)');
        $this->addSql('CREATE INDEX IDX_F7ED8B2B6A5D7349 ON markt_geografie_obstakel (obstakel_id)');
        $this->addSql('CREATE TABLE markt_locatie_branche (markt_locatie_id INT NOT NULL, branche_id INT NOT NULL, PRIMARY KEY(markt_locatie_id, branche_id))');
        $this->addSql('CREATE INDEX IDX_5C44F61EA3899086 ON markt_locatie_branche (markt_locatie_id)');
        $this->addSql('CREATE INDEX IDX_5C44F61E9DDF9A9E ON markt_locatie_branche (branche_id)');
        $this->addSql('CREATE TABLE markt_locatie_plaatseigenschap (markt_locatie_id INT NOT NULL, plaatseigenschap_id INT NOT NULL, PRIMARY KEY(markt_locatie_id, plaatseigenschap_id))');
        $this->addSql('CREATE INDEX IDX_56EEE7EDA3899086 ON markt_locatie_plaatseigenschap (markt_locatie_id)');
        $this->addSql('CREATE INDEX IDX_56EEE7ED765CF61B ON markt_locatie_plaatseigenschap (plaatseigenschap_id)');
        $this->addSql('ALTER TABLE markt_geografie_obstakel ADD CONSTRAINT FK_F7ED8B2BE769C07F FOREIGN KEY (markt_geografie_id) REFERENCES markt_geografie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_geografie_obstakel ADD CONSTRAINT FK_F7ED8B2B6A5D7349 FOREIGN KEY (obstakel_id) REFERENCES obstakel (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie_branche ADD CONSTRAINT FK_5C44F61EA3899086 FOREIGN KEY (markt_locatie_id) REFERENCES markt_locatie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie_branche ADD CONSTRAINT FK_5C44F61E9DDF9A9E FOREIGN KEY (branche_id) REFERENCES branche (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie_plaatseigenschap ADD CONSTRAINT FK_56EEE7EDA3899086 FOREIGN KEY (markt_locatie_id) REFERENCES markt_locatie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie_plaatseigenschap ADD CONSTRAINT FK_56EEE7ED765CF61B FOREIGN KEY (plaatseigenschap_id) REFERENCES plaatseigenschap (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_geografie DROP CONSTRAINT fk_f08e37146a5d7349');
        $this->addSql('DROP INDEX idx_f08e37146a5d7349');
        $this->addSql('ALTER TABLE markt_geografie DROP obstakel_id');
        $this->addSql('ALTER TABLE markt_locatie DROP CONSTRAINT fk_69ff38939ddf9a9e');
        $this->addSql('ALTER TABLE markt_locatie DROP CONSTRAINT fk_69ff3893765cf61b');
        $this->addSql('DROP INDEX idx_69ff3893765cf61b');
        $this->addSql('DROP INDEX idx_69ff38939ddf9a9e');
        $this->addSql('ALTER TABLE markt_locatie DROP branche_id');
        $this->addSql('ALTER TABLE markt_locatie DROP plaatseigenschap_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE markt_geografie_obstakel');
        $this->addSql('DROP TABLE markt_locatie_branche');
        $this->addSql('DROP TABLE markt_locatie_plaatseigenschap');
        $this->addSql('ALTER TABLE markt_geografie ADD obstakel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt_geografie ADD CONSTRAINT fk_f08e37146a5d7349 FOREIGN KEY (obstakel_id) REFERENCES obstakel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_f08e37146a5d7349 ON markt_geografie (obstakel_id)');
        $this->addSql('ALTER TABLE markt_locatie ADD branche_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt_locatie ADD plaatseigenschap_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt_locatie ADD CONSTRAINT fk_69ff38939ddf9a9e FOREIGN KEY (branche_id) REFERENCES branche (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie ADD CONSTRAINT fk_69ff3893765cf61b FOREIGN KEY (plaatseigenschap_id) REFERENCES plaatseigenschap (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_69ff3893765cf61b ON markt_locatie (plaatseigenschap_id)');
        $this->addSql('CREATE INDEX idx_69ff38939ddf9a9e ON markt_locatie (branche_id)');
    }
}
