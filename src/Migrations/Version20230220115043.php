<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220115043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Removes old BTW Tarieven Table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE btw_tarief');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
