<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230623125123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tarievenplan ADD variant VARCHAR(50) NOT NULL DEFAULT \'standard\'');
        $this->addSql('ALTER TABLE tarievenplan ADD ignore_vaste_plaats BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE tarievenplan ADD monday BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE tarievenplan ADD tuesday BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE tarievenplan ADD wednesday BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE tarievenplan ADD thursday BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE tarievenplan ADD friday BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE tarievenplan ADD saturday BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE tarievenplan ADD sunday BOOLEAN NOT NULL DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tarievenplan DROP variant');
        $this->addSql('ALTER TABLE tarievenplan DROP ignore_vaste_plaats');
        $this->addSql('ALTER TABLE tarievenplan DROP monday');
        $this->addSql('ALTER TABLE tarievenplan DROP tuesday');
        $this->addSql('ALTER TABLE tarievenplan DROP wednesday');
        $this->addSql('ALTER TABLE tarievenplan DROP thursday');
        $this->addSql('ALTER TABLE tarievenplan DROP friday');
        $this->addSql('ALTER TABLE tarievenplan DROP saturday');
        $this->addSql('ALTER TABLE tarievenplan DROP sunday');
    }
}
