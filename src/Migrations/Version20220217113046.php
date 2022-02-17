<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220217113046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dagvergunning ADD aantal_meters_groot_vast INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD aantal_meters_klein_vast INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD aantal_meters_groot_vast INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD aantal_meters_klein_vast INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE vergunning_controle DROP aantal_meters_groot_vast');
        $this->addSql('ALTER TABLE vergunning_controle DROP aantal_meters_klein_vast');
        $this->addSql('ALTER TABLE dagvergunning DROP aantal_meters_groot_vast');
        $this->addSql('ALTER TABLE dagvergunning DROP aantal_meters_klein_vast');
    }
}
