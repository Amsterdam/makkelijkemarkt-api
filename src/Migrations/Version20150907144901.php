<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20150907144901 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sollicitatie ADD vaste_plaatsen TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD aantal_3meter_kramen INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD aantal_4meter_kramen INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD aantal_extra_meters INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD aantal_elektra INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sollicitatie ADD krachtstroom BOOLEAN DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN sollicitatie.vaste_plaatsen IS \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE sollicitatie DROP vaste_plaatsen');
        $this->addSql('ALTER TABLE sollicitatie DROP aantal_3meter_kramen');
        $this->addSql('ALTER TABLE sollicitatie DROP aantal_4meter_kramen');
        $this->addSql('ALTER TABLE sollicitatie DROP aantal_extra_meters');
        $this->addSql('ALTER TABLE sollicitatie DROP aantal_elektra');
        $this->addSql('ALTER TABLE sollicitatie DROP krachtstroom');
    }
}
