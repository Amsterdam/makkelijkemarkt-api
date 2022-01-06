<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220106153000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE markt_configuratie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE markt_configuratie (id INT NOT NULL, markt_id INT NOT NULL, geografie JSON NOT NULL, locaties JSON NOT NULL, markt_opstelling JSON NOT NULL, paginas JSON NOT NULL, branches JSON NOT NULL, aanmaak_datumtijd TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX markt_id ON markt_configuratie (markt_id)');
        $this->addSql('CREATE INDEX aanmaak_datumtijd ON markt_configuratie (aanmaak_datumtijd)');
        $this->addSql('ALTER TABLE markt_configuratie ADD CONSTRAINT FK_E2771CD8D658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE markt_configuratie_id_seq CASCADE');
        $this->addSql('DROP TABLE markt_configuratie');
    }
}
