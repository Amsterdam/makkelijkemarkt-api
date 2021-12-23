<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211223193019 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE allocation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE allocation (id INT NOT NULL, koopman_id INT NOT NULL, branche_id INT NOT NULL, markt_id INT NOT NULL, is_allocated BOOLEAN NOT NULL, reject_reason VARCHAR(255) DEFAULT NULL, plaatsen TEXT DEFAULT NULL, date DATE NOT NULL, anywhere BOOLEAN NOT NULL, minimum INT NOT NULL, maximum INT NOT NULL, is_bak BOOLEAN NOT NULL, has_inrichting BOOLEAN NOT NULL, plaatsvoorkeuren TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C44232AFE3565D3 ON allocation (koopman_id)');
        $this->addSql('CREATE INDEX IDX_5C44232A9DDF9A9E ON allocation (branche_id)');
        $this->addSql('CREATE INDEX IDX_5C44232AD658EC2D ON allocation (markt_id)');
        $this->addSql('CREATE UNIQUE INDEX allocation_unique ON allocation (koopman_id, markt_id, date)');
        $this->addSql('COMMENT ON COLUMN allocation.plaatsen IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN allocation.plaatsvoorkeuren IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE allocation ADD CONSTRAINT FK_5C44232AFE3565D3 FOREIGN KEY (koopman_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE allocation ADD CONSTRAINT FK_5C44232A9DDF9A9E FOREIGN KEY (branche_id) REFERENCES branche (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE allocation ADD CONSTRAINT FK_5C44232AD658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE allocation_id_seq CASCADE');
        $this->addSql('DROP TABLE allocation');
    }
}
