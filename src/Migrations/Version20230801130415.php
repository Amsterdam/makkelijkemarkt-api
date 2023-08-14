<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230801130415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX tarievenplan_unique');
        $this->addSql('CREATE UNIQUE INDEX tarievenplan_unique ON tarievenplan (markt_id, date_from, variant) WHERE deleted = false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX tarievenplan_unique');
        $this->addSql('CREATE UNIQUE INDEX tarievenplan_unique ON tarievenplan (markt_id, date_from, variant, deleted)');
    }
}
