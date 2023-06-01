<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FTUpdateTariefSoortCommand extends Command
{
    protected static $defaultName = 'flextarieven:tariefsoort:update';
    protected static $defaultDescription = 'Updates tariefsoort table for flexibele tarieven with units and factuur labels';
    private $connection;

    public function __construct(
        \Doctrine\DBAL\Connection $connection
    ) {
        $this->connection = $connection;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->connection->executeStatement('ALTER TABLE tarief_soort ALTER COLUMN unit SET DEFAULT NULL');
            $this->connection->executeStatement('ALTER TABLE tarief_soort ALTER COLUMN factuur_label SET DEFAULT NULL');

            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters', factuur_label = 'afgenomen meters (normaal tarief)' WHERE label = 'Tarief per meter' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters', factuur_label = 'reiniging (normaal tarief)' WHERE label = 'Reiniging per meter' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters', factuur_label = 'toeslag bedrijfsafval' WHERE label = 'Toeslag bedrijfsafval per meter' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'elektra krachtstroom' WHERE label = 'Toeslag krachtstroom per aansluiting' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters', factuur_label = 'promotiegelden per meter' WHERE label = 'Promotie gelden per meter' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'one-off', factuur_label = 'promotiegelden per koopman' WHERE label = 'Promotie gelden per kraam' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'afvaleiland' WHERE label = 'Afvaleiland' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'one-off', factuur_label = 'eenmalige elektra' WHERE label = 'Eenmalig elektra' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'elektra' WHERE label = 'Elektra' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters-groot', factuur_label = 'afgenomen meters (groot tarief)' WHERE label = 'Tarief per meter groot' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters-klein', factuur_label = 'afgenomen meters (klein tarief)' WHERE label = 'Tarief per meter klein' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters-groot', factuur_label = 'reiniging (groot tarief)' WHERE label = 'Reiniging per meter groot' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters-klein', factuur_label = 'reiniging (klein tarief)' WHERE label = 'Reiniging per meter klein' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'AGF per meter' WHERE label = 'Agf per meter' AND tarief_type = 'lineair';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'extra meter' WHERE label = 'Een meter' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = '3 meter plaats' WHERE label = 'Drie meter' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = '4 meter plaats' WHERE label = 'Vier meter' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'elektra' WHERE label = 'Elektra' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'meters', factuur_label = 'promotiegelden per meter' WHERE label = 'Promotie gelden per meter' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'one-off', factuur_label = 'promotiegelden per koopman' WHERE label = 'Promotie gelden per kraam' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'afvaleiland' WHERE label = 'Afvaleiland' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'one-off', factuur_label = 'eenmalige elektra' WHERE label = 'Eenmalig elektra' AND tarief_type = 'concreet';");
            $this->connection->executeStatement("UPDATE tarief_soort SET unit = 'unit', factuur_label = 'AGF per meter' WHERE label = 'Agf per meter' AND tarief_type = 'concreet';");

            $this->connection->executeStatement('ALTER TABLE tarief_soort ALTER COLUMN unit SET NOT NULL');
            $this->connection->executeStatement('ALTER TABLE tarief_soort ALTER COLUMN factuur_label SET NOT NULL');
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $io->error($e->getMessage());
            throw $e;
        }

        $io->success('Tarief soorten geupdate');

        return COMMAND::SUCCESS;
    }
}
