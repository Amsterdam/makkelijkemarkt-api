<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20150907134324 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dagvergunning ADD aantal_elektra INT DEFAULT NULL');
        $this->addSql('UPDATE dagvergunning SET aantal_elektra = 0');
        $this->addSql('ALTER TABLE dagvergunning ALTER COLUMN aantal_elektra SET NOT NULL');

        $this->addSql('ALTER TABLE dagvergunning ADD krachtstroom BOOLEAN DEFAULT NULL');
        $this->addSql('UPDATE dagvergunning SET krachtstroom = false');
        $this->addSql('ALTER TABLE dagvergunning ALTER COLUMN krachtstroom SET NOT NULL');

        $this->addSql('ALTER TABLE dagvergunning ADD reiniging BOOLEAN DEFAULT NULL');
        $this->addSql('UPDATE dagvergunning SET reiniging = false');
        $this->addSql('ALTER TABLE dagvergunning ALTER COLUMN reiniging SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dagvergunning DROP aantal_elektra');
        $this->addSql('ALTER TABLE dagvergunning DROP krachtstroom');
        $this->addSql('ALTER TABLE dagvergunning DROP reiniging');
    }
}
