<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220314114831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE markt_pagina_indelingslijt_group_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS markt_pagina_indelingslijst_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS markt_pagina_indelingslijt_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('DROP SEQUENCE markt_pagina_indelingslijst_group_id_seq CASCADE');
    }
}
