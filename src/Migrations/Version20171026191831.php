<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20171026191831 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE markt ADD aantal_kramen INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt ADD aantal_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt_extra_data DROP aantal_kramen');
        $this->addSql('ALTER TABLE markt_extra_data DROP aantal_meter');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE markt_extra_data ADD aantal_kramen INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt_extra_data ADD aantal_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE markt DROP aantal_kramen');
        $this->addSql('ALTER TABLE markt DROP aantal_meter');
    }
}
