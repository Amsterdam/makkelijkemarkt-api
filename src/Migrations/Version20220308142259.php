<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220308142259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE IF EXISTS markt_branche_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS markt_branche_eigenschap_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE markt_branche_eigenschap (id INT NOT NULL, markt_configuratie_id INT DEFAULT NULL, branche_id INT DEFAULT NULL, verplicht BOOLEAN DEFAULT NULL, maximum_plaatsen INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_276ED4CCCDCD1898 ON markt_branche_eigenschap (markt_configuratie_id)');
        $this->addSql('CREATE INDEX IDX_276ED4CC9DDF9A9E ON markt_branche_eigenschap (branche_id)');
        $this->addSql('CREATE TABLE markt_geografie (id INT NOT NULL, markt_configuratie_id INT DEFAULT NULL, obstakel_id INT DEFAULT NULL, kraam_a VARCHAR(255) NOT NULL, kraam_b VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F08E3714CDCD1898 ON markt_geografie (markt_configuratie_id)');
        $this->addSql('CREATE INDEX IDX_F08E37146A5D7349 ON markt_geografie (obstakel_id)');
        $this->addSql('CREATE TABLE markt_locatie (id INT NOT NULL, markt_configuratie_id INT DEFAULT NULL, branche_id INT DEFAULT NULL, plaatseigenschap_id INT DEFAULT NULL, plaats_id VARCHAR(255) DEFAULT NULL, verkoop_inrichting VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_69FF3893CDCD1898 ON markt_locatie (markt_configuratie_id)');
        $this->addSql('CREATE INDEX IDX_69FF38939DDF9A9E ON markt_locatie (branche_id)');
        $this->addSql('CREATE INDEX IDX_69FF3893765CF61B ON markt_locatie (plaatseigenschap_id)');
        $this->addSql('CREATE TABLE markt_opstelling (id INT NOT NULL, markt_configuratie_id INT DEFAULT NULL, elements JSON NOT NULL, position INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F9566BFCCDCD1898 ON markt_opstelling (markt_configuratie_id)');
        $this->addSql('CREATE TABLE markt_pagina (id INT NOT NULL, markt_configuratie_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_233D557CDCD1898 ON markt_pagina (markt_configuratie_id)');
        $this->addSql('CREATE TABLE markt_pagina_indelingslijst_group (id INT NOT NULL, markt_pagina_id INT DEFAULT NULL, class VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, landmark_top VARCHAR(255) NOT NULL, landmark_bottom VARCHAR(255) NOT NULL, plaats_list JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3E18D0F84C2DAD4B ON markt_pagina_indelingslijst_group (markt_pagina_id)');
        $this->addSql('ALTER TABLE markt_branche_eigenschap ADD CONSTRAINT FK_276ED4CCCDCD1898 FOREIGN KEY (markt_configuratie_id) REFERENCES markt_configuratie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_branche_eigenschap ADD CONSTRAINT FK_276ED4CC9DDF9A9E FOREIGN KEY (branche_id) REFERENCES branche (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_geografie ADD CONSTRAINT FK_F08E3714CDCD1898 FOREIGN KEY (markt_configuratie_id) REFERENCES markt_configuratie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_geografie ADD CONSTRAINT FK_F08E37146A5D7349 FOREIGN KEY (obstakel_id) REFERENCES obstakel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie ADD CONSTRAINT FK_69FF3893CDCD1898 FOREIGN KEY (markt_configuratie_id) REFERENCES markt_configuratie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie ADD CONSTRAINT FK_69FF38939DDF9A9E FOREIGN KEY (branche_id) REFERENCES branche (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_locatie ADD CONSTRAINT FK_69FF3893765CF61B FOREIGN KEY (plaatseigenschap_id) REFERENCES plaatseigenschap (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_opstelling ADD CONSTRAINT FK_F9566BFCCDCD1898 FOREIGN KEY (markt_configuratie_id) REFERENCES markt_configuratie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_pagina ADD CONSTRAINT FK_233D557CDCD1898 FOREIGN KEY (markt_configuratie_id) REFERENCES markt_configuratie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE markt_pagina_indelingslijst_group ADD CONSTRAINT FK_3E18D0F84C2DAD4B FOREIGN KEY (markt_pagina_id) REFERENCES markt_pagina (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE markt_pagina_indelingslijst_group DROP CONSTRAINT FK_3E18D0F84C2DAD4B');
        $this->addSql('DROP SEQUENCE markt_branche_eigenschap_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE markt_branche_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP TABLE markt_branche_eigenschap');
        $this->addSql('DROP TABLE markt_geografie');
        $this->addSql('DROP TABLE markt_locatie');
        $this->addSql('DROP TABLE markt_opstelling');
        $this->addSql('DROP TABLE markt_pagina');
        $this->addSql('DROP TABLE markt_pagina_indelingslijst_group');
    }
}
