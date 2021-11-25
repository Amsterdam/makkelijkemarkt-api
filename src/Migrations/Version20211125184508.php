<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211125184508 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE branche_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE markt_voorkeur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE plaats_voorkeur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE rsvp_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE branche (id INT NOT NULL, afkorting VARCHAR(255) NOT NULL, omschrijving VARCHAR(255) NOT NULL, color VARCHAR(6) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX afkorting ON branche (afkorting)');
        $this->addSql('CREATE TABLE markt_voorkeur (id INT NOT NULL, branche_id INT NOT NULL, markt_id INT NOT NULL, koopman_id INT NOT NULL, anywhere BOOLEAN NOT NULL, minimum INT DEFAULT NULL, maximum INT DEFAULT NULL, has_inrichting BOOLEAN NOT NULL, is_bak BOOLEAN NOT NULL, absent_from DATE DEFAULT NULL, absent_until DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F33D6E6D9DDF9A9E ON markt_voorkeur (branche_id)');
        $this->addSql('CREATE INDEX IDX_F33D6E6DD658EC2D ON markt_voorkeur (markt_id)');
        $this->addSql('CREATE INDEX IDX_F33D6E6DFE3565D3 ON markt_voorkeur (koopman_id)');
        $this->addSql('CREATE UNIQUE INDEX markt_voorkeur_unique ON markt_voorkeur (koopman_id, markt_id)');
        $this->addSql('CREATE TABLE plaats_voorkeur (id INT NOT NULL, markt_id INT NOT NULL, koopman_id INT NOT NULL, plaatsen TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CA523BEFD658EC2D ON plaats_voorkeur (markt_id)');
        $this->addSql('CREATE INDEX IDX_CA523BEFFE3565D3 ON plaats_voorkeur (koopman_id)');
        $this->addSql('CREATE UNIQUE INDEX plaats_voorkeur_unique ON plaats_voorkeur (koopman_id, markt_id)');
        $this->addSql('COMMENT ON COLUMN plaats_voorkeur.plaatsen IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE rsvp (id INT NOT NULL, markt_id INT NOT NULL, koopman_id INT NOT NULL, markt_date DATE NOT NULL, attending BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9FA5CE4ED658EC2D ON rsvp (markt_id)');
        $this->addSql('CREATE INDEX IDX_9FA5CE4EFE3565D3 ON rsvp (koopman_id)');
        $this->addSql('CREATE UNIQUE INDEX rsvp_unique ON rsvp (koopman_id, markt_id, markt_date)');
        $this->addSql('ALTER TABLE markt_voorkeur ADD CONSTRAINT FK_F33D6E6D9DDF9A9E FOREIGN KEY (branche_id) REFERENCES branche (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_voorkeur ADD CONSTRAINT FK_F33D6E6DD658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_voorkeur ADD CONSTRAINT FK_F33D6E6DFE3565D3 FOREIGN KEY (koopman_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plaats_voorkeur ADD CONSTRAINT FK_CA523BEFD658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE plaats_voorkeur ADD CONSTRAINT FK_CA523BEFFE3565D3 FOREIGN KEY (koopman_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rsvp ADD CONSTRAINT FK_9FA5CE4ED658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rsvp ADD CONSTRAINT FK_9FA5CE4EFE3565D3 FOREIGN KEY (koopman_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE markt_voorkeur DROP CONSTRAINT FK_F33D6E6D9DDF9A9E');
        $this->addSql('DROP SEQUENCE branche_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE markt_voorkeur_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE plaats_voorkeur_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE rsvp_id_seq CASCADE');
        $this->addSql('DROP TABLE branche');
        $this->addSql('DROP TABLE markt_voorkeur');
        $this->addSql('DROP TABLE plaats_voorkeur');
        $this->addSql('DROP TABLE rsvp');
    }
}
