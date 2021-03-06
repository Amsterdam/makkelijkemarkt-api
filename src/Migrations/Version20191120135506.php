<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191120135506 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE lineairplan ADD elektra NUMERIC(10, 2) NULL');
        $this->addSql('UPDATE lineairplan SET elektra = 0');
        $this->addSql('ALTER TABLE lineairplan ALTER COLUMN elektra SET NOT NULL');
        $this->addSql('ALTER TABLE markt ADD kies_je_kraam_geblokeerde_plaatsen TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lineairplan DROP elektra');
        $this->addSql('ALTER TABLE markt DROP kies_je_kraam_geblokeerde_plaatsen');
    }
}
