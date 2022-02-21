<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220210150334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dagvergunning ADD groot_per_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD klein_per_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD groot_reiniging INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD klein_reiniging INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD afval_eiland_agf INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dagvergunning ADD krachtstroom_per_stuk INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD groot_per_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD klein_per_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD groot_reiniging INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD klein_reiniging INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD afval_eiland_agf INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD krachtstroom_per_stuk INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD groot_per_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD klein_per_meter INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD groot_reiniging INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD klein_reiniging INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD afval_eiland_agf INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vergunning_controle ADD krachtstroom_per_stuk INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE vergunning_controle DROP groot_per_meter');
        $this->addSql('ALTER TABLE vergunning_controle DROP klein_per_meter');
        $this->addSql('ALTER TABLE vergunning_controle DROP groot_reiniging');
        $this->addSql('ALTER TABLE vergunning_controle DROP klein_reiniging');
        $this->addSql('ALTER TABLE vergunning_controle DROP afval_eiland_agf');
        $this->addSql('ALTER TABLE vergunning_controle DROP krachtstroom_per_stuk');
        $this->addSql('ALTER TABLE sollicitatie DROP groot_per_meter');
        $this->addSql('ALTER TABLE sollicitatie DROP klein_per_meter');
        $this->addSql('ALTER TABLE sollicitatie DROP groot_reiniging');
        $this->addSql('ALTER TABLE sollicitatie DROP klein_reiniging');
        $this->addSql('ALTER TABLE sollicitatie DROP afval_eiland_agf');
        $this->addSql('ALTER TABLE sollicitatie DROP krachtstroom_per_stuk');
        $this->addSql('ALTER TABLE dagvergunning DROP groot_per_meter');
        $this->addSql('ALTER TABLE dagvergunning DROP klein_per_meter');
        $this->addSql('ALTER TABLE dagvergunning DROP groot_reiniging');
        $this->addSql('ALTER TABLE dagvergunning DROP klein_reiniging');
        $this->addSql('ALTER TABLE dagvergunning DROP afval_eiland_agf');
        $this->addSql('ALTER TABLE dagvergunning DROP krachtstroom_per_stuk');
    }
}
