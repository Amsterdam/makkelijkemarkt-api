<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20150825175241 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE markt ADD standaard_kraam_afmeting INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt ADD extra_meters_mogelijk BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE markt ADD aanwezige_opties TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN markt.aanwezige_opties IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE markt_extra_data ADD aanwezige_opties TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN markt_extra_data.aanwezige_opties IS \'(DC2Type:json_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE markt_extra_data DROP aanwezige_opties');
        $this->addSql('ALTER TABLE markt DROP standaard_kraam_afmeting');
        $this->addSql('ALTER TABLE markt DROP extra_meters_mogelijk');
        $this->addSql('ALTER TABLE markt DROP aanwezige_opties');
    }
}
