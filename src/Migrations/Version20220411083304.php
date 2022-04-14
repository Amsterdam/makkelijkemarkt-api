<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220411083304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allocation ADD bak_type VARCHAR');
        $this->addSql('UPDATE allocation SET bak_type = \'geen\' WHERE is_bak = false');
        $this->addSql('UPDATE allocation SET bak_type = \'bak\' WHERE is_bak = true');
        $this->addSql('ALTER TABLE allocation DROP is_bak');
        $this->addSql('ALTER TABLE markt_voorkeur ADD bak_type VARCHAR');
        $this->addSql('UPDATE markt_voorkeur SET bak_type = \'geen\' WHERE is_bak = false');
        $this->addSql('UPDATE markt_voorkeur SET bak_type = \'bak\' WHERE is_bak = true');
        $this->addSql('ALTER TABLE markt_voorkeur DROP is_bak');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE allocation ADD is_bak BOOLEAN NOT NULL');
        $this->addSql('UPDATE allocation SET is_bak = false WHERE bak_type = \'geen\'');
        $this->addSql('UPDATE allocation SET is_bak = true WHERE bak_type = \'bak\'');
        $this->addSql('ALTER TABLE allocation DROP bak_type');
        $this->addSql('ALTER TABLE markt_voorkeur ADD is_bak BOOLEAN NOT NULL');
        $this->addSql('UPDATE markt_voorkeur SET is_bak = false WHERE bak_type = \'geen\'');
        $this->addSql('UPDATE markt_voorkeur SET is_bak = true WHERE bak_type = \'bak\'');
        $this->addSql('ALTER TABLE markt_voorkeur DROP bak_type');
    }
}
