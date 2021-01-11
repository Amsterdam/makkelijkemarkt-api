<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20150826105615 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dagvergunning ADD notitie TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD aanmaak_datumtijd TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('UPDATE dagvergunning SET aanmaak_datumtijd = registratie_datumtijd');
        $this->addSql('ALTER TABLE dagvergunning ALTER COLUMN aanmaak_datumtijd SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dagvergunning DROP notitie');
        $this->addSql('ALTER TABLE dagvergunning DROP aanmaak_datumtijd');
    }
}
