<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20160325122527 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE vervanger_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vervanger (id INT NOT NULL, koopman_id INT NOT NULL, vervanger_id INT NOT NULL, pas_uid VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CF85A36AFE3565D3 ON vervanger (koopman_id)');
        $this->addSql('CREATE INDEX IDX_CF85A36A3BF9138C ON vervanger (vervanger_id)');
        $this->addSql('ALTER TABLE vervanger ADD CONSTRAINT FK_CF85A36AFE3565D3 FOREIGN KEY (koopman_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vervanger ADD CONSTRAINT FK_CF85A36A3BF9138C FOREIGN KEY (vervanger_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE vervanger_id_seq CASCADE');
        $this->addSql('DROP TABLE vervanger');
    }
}
