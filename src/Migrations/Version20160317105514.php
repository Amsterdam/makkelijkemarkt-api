<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20160317105514 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE lineairplan ADD eenmalig_elektra NUMERIC(10, 2)');
        $this->addSql('UPDATE lineairplan SET eenmalig_elektra = 0');
        $this->addSql('ALTER TABLE lineairplan ALTER eenmalig_elektra SET NOT NULL');
        $this->addSql('ALTER TABLE concreetplan ADD eenmalig_elektra NUMERIC(10, 2)');
        $this->addSql('UPDATE concreetplan SET eenmalig_elektra = 0');
        $this->addSql('ALTER TABLE concreetplan ALTER eenmalig_elektra SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lineairplan DROP eenmalig_elektra');
        $this->addSql('ALTER TABLE concreetplan DROP eenmalig_elektra');
    }
}
