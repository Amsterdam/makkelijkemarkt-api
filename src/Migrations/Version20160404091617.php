<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20160404091617 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dagvergunning ADD vervanger_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD CONSTRAINT FK_8F5B17373BF9138C FOREIGN KEY (vervanger_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8F5B17373BF9138C ON dagvergunning (vervanger_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dagvergunning DROP CONSTRAINT FK_8F5B17373BF9138C');
        $this->addSql('DROP INDEX IDX_8F5B17373BF9138C');
        $this->addSql('ALTER TABLE dagvergunning DROP vervanger_id');
    }
}
