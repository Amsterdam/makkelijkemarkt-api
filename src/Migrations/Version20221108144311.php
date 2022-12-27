<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221108144311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE btw_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE btw_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE btw_waarde_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tarief_soort_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE btw_plan (id INT NOT NULL, tarief_soort_id INT NOT NULL, btw_type_id INT NOT NULL, markt_id INT DEFAULT NULL, date_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BFB16D952D2F027D ON btw_plan (tarief_soort_id)');
        $this->addSql('CREATE INDEX IDX_BFB16D95DC79A76C ON btw_plan (btw_type_id)');
        $this->addSql('CREATE INDEX IDX_BFB16D95D658EC2D ON btw_plan (markt_id)');
        $this->addSql('CREATE UNIQUE INDEX btw_plan_unique ON btw_plan (tarief_soort_id, date_from)');
        $this->addSql('CREATE TABLE btw_type (id INT NOT NULL, label TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX btw_type_unique ON btw_type (label)');
        $this->addSql('CREATE TABLE btw_waarde (id INT NOT NULL, btw_type_id INT NOT NULL, tarief INT NOT NULL, date_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B20C5B9DDC79A76C ON btw_waarde (btw_type_id)');
        $this->addSql('CREATE UNIQUE INDEX btw_waarde_unique ON btw_waarde (btw_type_id, date_from)');
        $this->addSql('CREATE TABLE tarief_soort (id INT NOT NULL, label TEXT NOT NULL, tarief_type TEXT NOT NULL, deleted BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX tarief_soort_unique ON tarief_soort (label, tarief_type)');
        $this->addSql('ALTER TABLE btw_plan ADD CONSTRAINT FK_BFB16D952D2F027D FOREIGN KEY (tarief_soort_id) REFERENCES tarief_soort (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE btw_plan ADD CONSTRAINT FK_BFB16D95DC79A76C FOREIGN KEY (btw_type_id) REFERENCES btw_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE btw_plan ADD CONSTRAINT FK_BFB16D95D658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE btw_waarde ADD CONSTRAINT FK_B20C5B9DDC79A76C FOREIGN KEY (btw_type_id) REFERENCES btw_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE btw_plan DROP CONSTRAINT FK_BFB16D95DC79A76C');
        $this->addSql('ALTER TABLE btw_waarde DROP CONSTRAINT FK_B20C5B9DDC79A76C');
        $this->addSql('ALTER TABLE btw_plan DROP CONSTRAINT FK_BFB16D952D2F027D');
        $this->addSql('DROP SEQUENCE btw_plan_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE btw_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE btw_waarde_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tarief_soort_id_seq CASCADE');
        $this->addSql('DROP TABLE btw_plan');
        $this->addSql('DROP TABLE btw_type');
        $this->addSql('DROP TABLE btw_waarde');
        $this->addSql('DROP TABLE tarief_soort');
    }
}
