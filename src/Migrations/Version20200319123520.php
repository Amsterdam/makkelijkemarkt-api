<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200319123520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'enlarge sollicitatie status for dagvergunning';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE "dagvergunning" ALTER COLUMN "status_solliciatie" TYPE VARCHAR(15)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE "dagvergunning" ALTER COLUMN "status_solliciatie" TYPE VARCHAR(4)');
    }
}
