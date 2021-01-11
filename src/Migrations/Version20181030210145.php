<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181030210145 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE markt SET afkorting = UPPER(afkorting)');
    }

    public function down(Schema $schema): void
    {
    }
}
