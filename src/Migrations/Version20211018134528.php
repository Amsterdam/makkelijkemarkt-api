<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018134528 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE markt ADD markt_beeindigd BOOLEAN');
        $this->addSql('ALTER TABLE vergunning_controle ALTER status_solliciatie TYPE VARCHAR(15)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE markt DROP markt_beeindigd');
        $this->addSql('ALTER TABLE vergunning_controle ALTER status_solliciatie TYPE VARCHAR(4)');
    }
}
