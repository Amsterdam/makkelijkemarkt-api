<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200127082648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add indelingstype in markt table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE markt ADD indelingstype VARCHAR(255) NULL');
        $this->addSql('UPDATE markt SET indelingstype = \'traditioneel\'');
        $this->addSql('ALTER TABLE markt ALTER COLUMN indelingstype SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE markt DROP indelingstype');
    }
}
