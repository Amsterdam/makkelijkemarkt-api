<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220511125444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE markt_extra_data');
        $this->addSql('ALTER TABLE allocation ALTER bak_type SET NOT NULL');
        $this->addSql('ALTER TABLE markt_voorkeur ALTER bak_type SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE markt_extra_data (afkorting VARCHAR(255) NOT NULL, perfect_view_nummer INT DEFAULT NULL, geo_area TEXT DEFAULT NULL, markt_dagen VARCHAR(20) DEFAULT NULL, aanwezige_opties JSON DEFAULT NULL, PRIMARY KEY(afkorting))');
        $this->addSql('COMMENT ON COLUMN markt_extra_data.aanwezige_opties IS \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE allocation ALTER bak_type DROP NOT NULL');
        $this->addSql('ALTER TABLE markt_voorkeur ALTER bak_type DROP NOT NULL');
    }
}
