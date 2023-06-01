<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230505142309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE dagvergunning_mapping_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE feature_flag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tarief_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tarievenplan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dagvergunning_mapping (id INT NOT NULL, tarief_soort_id INT DEFAULT NULL, dagvergunning_key VARCHAR(100) NOT NULL, mercato_key VARCHAR(50) DEFAULT NULL, translated_to_unit INT NOT NULL, tarief_type VARCHAR(30) NOT NULL, archived_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, unit VARCHAR(30) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4520EAA42D2F027D ON dagvergunning_mapping (tarief_soort_id)');
        $this->addSql('CREATE UNIQUE INDEX dagvergunning_mapping_unique ON dagvergunning_mapping (dagvergunning_key, tarief_type, archived_on)');
        $this->addSql('CREATE TABLE feature_flag (id INT NOT NULL, feature VARCHAR(100) NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE tarief (id INT NOT NULL, tarievenplan_id INT NOT NULL, tarief_soort_id INT NOT NULL, waarde DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF4873BE59C62903 ON tarief (tarievenplan_id)');
        $this->addSql('CREATE INDEX IDX_BF4873BE2D2F027D ON tarief (tarief_soort_id)');
        $this->addSql('CREATE UNIQUE INDEX tarief_unique ON tarief (tarief_soort_id, tarievenplan_id)');
        $this->addSql('CREATE TABLE tarievenplan (id INT NOT NULL, markt_id INT NOT NULL, name VARCHAR(100) NOT NULL, type VARCHAR(30) NOT NULL, date_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A29CF2CD658EC2D ON tarievenplan (markt_id)');
        $this->addSql('CREATE UNIQUE INDEX tarievenplan_unique ON tarievenplan (markt_id, date_from)');
        $this->addSql('ALTER TABLE dagvergunning_mapping ADD CONSTRAINT FK_4520EAA42D2F027D FOREIGN KEY (tarief_soort_id) REFERENCES tarief_soort (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tarief ADD CONSTRAINT FK_BF4873BE59C62903 FOREIGN KEY (tarievenplan_id) REFERENCES tarievenplan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tarief ADD CONSTRAINT FK_BF4873BE2D2F027D FOREIGN KEY (tarief_soort_id) REFERENCES tarief_soort (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tarievenplan ADD CONSTRAINT FK_5A29CF2CD658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dagvergunning ADD info_json JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE tarief_soort ADD unit VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE tarief_soort ADD factuur_label VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tarief DROP CONSTRAINT FK_BF4873BE59C62903');
        $this->addSql('DROP SEQUENCE dagvergunning_mapping_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE feature_flag_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tarief_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tarievenplan_id_seq CASCADE');
        $this->addSql('DROP TABLE dagvergunning_mapping');
        $this->addSql('DROP TABLE feature_flag');
        $this->addSql('DROP TABLE tarief');
        $this->addSql('DROP TABLE tarievenplan');
        $this->addSql('ALTER TABLE dagvergunning DROP info_json');
        $this->addSql('ALTER TABLE tarief_soort DROP unit');
        $this->addSql('ALTER TABLE tarief_soort DROP factuur_label');
    }
}
