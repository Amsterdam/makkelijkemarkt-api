<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230112134511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE allocation_v2_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE allocation_v2 (id INT NOT NULL, markt_id INT NOT NULL, markt_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, allocation_status INT NOT NULL, allocation JSONB NOT NULL, log JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D3D10EEDD658EC2D ON allocation_v2 (markt_id)');
        $this->addSql('CREATE UNIQUE INDEX allocation_v2_unique ON allocation_v2 (markt_id, markt_date, creation_date)');
        $this->addSql('ALTER TABLE allocation_v2 ADD CONSTRAINT FK_D3D10EEDD658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE allocation_v2_id_seq CASCADE');
        $this->addSql('DROP TABLE allocation_v2');
    }
}
