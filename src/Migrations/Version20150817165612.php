<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20150817165612 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dagvergunning RENAME COLUMN registrant_invoer_methode TO erkenningsnummer_invoer_methode;');
        $this->addSql('ALTER TABLE dagvergunning RENAME COLUMN registrant_invoer_waarde TO erkenningsnummer_invoer_waarde;');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dagvergunning RENAME COLUMN erkenningsnummer_invoer_methode TO registrant_invoer_methode;');
        $this->addSql('ALTER TABLE dagvergunning RENAME COLUMN erkenningsnummer_invoer_waarde TO registrant_invoer_waarde;');
    }
}
