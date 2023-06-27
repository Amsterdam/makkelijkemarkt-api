<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230602114848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE markt_dagvergunning_mapping (markt_id INT NOT NULL, dagvergunning_mapping_id INT NOT NULL, PRIMARY KEY(markt_id, dagvergunning_mapping_id))');
        $this->addSql('CREATE INDEX IDX_49005156D658EC2D ON markt_dagvergunning_mapping (markt_id)');
        $this->addSql('CREATE INDEX IDX_4900515627CF0B67 ON markt_dagvergunning_mapping (dagvergunning_mapping_id)');
        $this->addSql('ALTER TABLE markt_dagvergunning_mapping ADD CONSTRAINT FK_49005156D658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_dagvergunning_mapping ADD CONSTRAINT FK_4900515627CF0B67 FOREIGN KEY (dagvergunning_mapping_id) REFERENCES dagvergunning_mapping (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE markt_dagvergunning_mapping');
    }
}
