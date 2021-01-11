<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20160307094136 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE lineairplan ADD afvaleiland NUMERIC(10, 2)');
        $this->addSql('UPDATE lineairplan SET afvaleiland = 0');
        $this->addSql('ALTER TABLE lineairplan ALTER afvaleiland SET NOT NULL');
        $this->addSql('ALTER TABLE concreetplan ADD afvaleiland NUMERIC(10, 2)');
        $this->addSql('UPDATE concreetplan SET afvaleiland = 0');
        $this->addSql('ALTER TABLE concreetplan ALTER afvaleiland SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE lineairplan DROP afvaleiland');
        $this->addSql('ALTER TABLE concreetplan DROP afvaleiland');
    }
}
