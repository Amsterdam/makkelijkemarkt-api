<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220627112349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE rsvp_pattern_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE rsvp_pattern (id INT NOT NULL, markt_id INT NOT NULL, koopman_id INT NOT NULL, pattern_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, monday BOOLEAN NOT NULL, tuesday BOOLEAN NOT NULL, wednesday BOOLEAN NOT NULL, thursday BOOLEAN NOT NULL, friday BOOLEAN NOT NULL, saturday BOOLEAN NOT NULL, sunday BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6D461966D658EC2D ON rsvp_pattern (markt_id)');
        $this->addSql('CREATE INDEX IDX_6D461966FE3565D3 ON rsvp_pattern (koopman_id)');
        $this->addSql('CREATE UNIQUE INDEX rsvp_plan_unique ON rsvp_pattern (koopman_id, markt_id, pattern_date)');
        $this->addSql('ALTER TABLE rsvp_pattern ADD CONSTRAINT FK_6D461966D658EC2D FOREIGN KEY (markt_id) REFERENCES markt (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rsvp_pattern ADD CONSTRAINT FK_6D461966FE3565D3 FOREIGN KEY (koopman_id) REFERENCES koopman (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE rsvp_pattern_id_seq CASCADE');
        $this->addSql('DROP TABLE rsvp_pattern');
    }
}
