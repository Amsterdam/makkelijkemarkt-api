<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230309121717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE btw_tarief_id_seq CASCADE');
        $this->addSql('DROP INDEX btw_plan_unique');
        $this->addSql('CREATE UNIQUE INDEX btw_plan_unique ON btw_plan (tarief_soort_id, date_from, markt_id, archived_on)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE btw_tarief_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP INDEX btw_plan_unique');
        $this->addSql('CREATE UNIQUE INDEX btw_plan_unique ON btw_plan (tarief_soort_id, date_from, markt_id)');
    }
}
