<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20150812150421 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE markt ADD afkorting VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE markt ADD soort VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE markt ADD perfect_view_nummer INT DEFAULT NULL');
        $this->addSql('CREATE INDEX marktAfkorting ON markt (afkorting)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX marktAfkorting');
        $this->addSql('ALTER TABLE markt DROP afkorting');
        $this->addSql('ALTER TABLE markt DROP soort');
        $this->addSql('ALTER TABLE markt DROP perfect_view_nummer');
    }
}
