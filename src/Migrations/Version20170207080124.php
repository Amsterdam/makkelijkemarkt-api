<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20170207080124 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE account ADD active BOOLEAN DEFAULT NULL');
        $this->addSql('UPDATE account SET active = false WHERE locked = true');
        $this->addSql('UPDATE account SET active = true WHERE locked = false');
        $this->addSql('UPDATE account SET active = true WHERE locked IS NULL');
        $this->addSql('ALTER TABLE account ALTER active SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE account DROP active');
    }
}
