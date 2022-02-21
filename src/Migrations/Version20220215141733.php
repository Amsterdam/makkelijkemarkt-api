<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220215141733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lineairplan ADD tarief_per_meter_groot NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE lineairplan ADD tarief_per_meter_klein NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE lineairplan ADD reiniging_per_meter_groot NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE lineairplan ADD reiniging_per_meter_klein NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE lineairplan ADD agf_per_meter NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lineairplan DROP tarief_per_meter_groot');
        $this->addSql('ALTER TABLE lineairplan DROP tarief_per_meter_klein');
        $this->addSql('ALTER TABLE lineairplan DROP reiniging_per_meter_groot');
        $this->addSql('ALTER TABLE lineairplan DROP reiniging_per_meter_klein');
        $this->addSql('ALTER TABLE lineairplan DROP agf_per_meter');
    }
}
