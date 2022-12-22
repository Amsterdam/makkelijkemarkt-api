<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221219115751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX btw_plan_unique');
        $this->addSql('CREATE UNIQUE INDEX btw_plan_unique ON btw_plan (tarief_soort_id, date_from, markt_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX btw_plan_unique');
        $this->addSql('CREATE UNIQUE INDEX btw_plan_unique ON btw_plan (tarief_soort_id, date_from)');
    }
}
