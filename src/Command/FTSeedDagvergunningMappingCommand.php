<?php

namespace App\Command;

use App\Entity\DagvergunningMapping;
use App\Repository\TariefSoortRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FTSeedDagvergunningMappingCommand extends Command
{
    // The name and description of the command
    protected static $defaultName = 'flextarieven:dagvergunningmapping:seed';
    protected static $defaultDescription = 'Fills dagvergunning mapping table';

    private EntityManagerInterface $entityManager;

    private TariefSoortRepository $tariefSoortRepository;

    public function __construct(EntityManagerInterface $entityManager, TariefSoortRepository $tariefSoortRepository)
    {
        $this->entityManager = $entityManager;
        $this->tariefSoortRepository = $tariefSoortRepository;

        parent::__construct();
    }

    protected function configure()
    {
        // Configure the command
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $i = 0;

        // For reference
        $columnNames = ['dagvergunning_key', 'mercato_key', 'translated_to_unit', 'unit', 'tarief_type', 'tariefSoort.label'];

        // TODO after release to PRD this should also be a fixture
        $data = [
            ['3MeterKramen', 'Aantal3', 3, 'meters', 'lineair', null],
            ['4MeterKramen', 'Aantal4', 4, 'meters', 'lineair', null],
            ['extraMeters', 'Aantal1', 1, 'meters', 'lineair', null],
            ['elektra', 'Aantelek', 1, 'unit', 'lineair', 'Elektra'],
            ['krachtstroom', 'Krachtstroom', 1, 'unit', 'lineair', 'Toeslag krachtstroom per aansluiting'], // TODO remove this one, once we have migrated to krachstroom per stuk
            ['afvaleiland', 'AANTAFV', 1, 'unit', 'lineair', 'Afvaleiland'],
            ['grootPerMeter', null, 1, 'meters-groot', 'lineair', 'Tarief per meter groot'],
            ['kleinPerMeter', null, 1, 'meters-klein', 'lineair', 'Tarief per meter klein'],
            ['grootReiniging', null, 1, 'meters-groot', 'lineair', 'Reiniging per meter groot'],
            ['kleinReiniging', null, 1, 'meters-klein', 'lineair', 'Reiniging per meter klein'],
            ['afvalEilandAgf', null, 1, 'unit', 'lineair', 'Agf per meter'],
            ['krachtstroomPerstuk', 'Krachtstroom', 1, 'unit', 'lineair', 'Toeslag krachtstroom per aansluiting'],
            ['reiniging', null, 1, 'meters', 'lineair', 'Reiniging per meter'],
            ['3MeterKramen', 'Aantal3', 1, 'unit', 'concreet', 'Drie meter'],
            ['4MeterKramen', 'Aantal4', 1, 'unit', 'concreet', 'Vier meter'],
            ['extraMeters', 'Aantal1', 1, 'unit', 'concreet', 'Een meter'],
            ['elektra', 'Aantelek', 1, 'unit', 'concreet', 'Elektra'],
            ['afvaleiland', 'AANTAFV', 1, 'unit', 'concreet', 'Afvaleiland'],
            ['afvalEilandAgf', null, 1, 'unit', 'concreet', 'Agf per meter'],
        ];

        foreach ($data as $row) {
            $dataMapping = new DagvergunningMapping();
            $dataMapping->setDagvergunningKey($row[0]);
            $dataMapping->setMercatoKey($row[1]);
            $dataMapping->setTranslatedToUnit((int) $row[2]);
            $dataMapping->setUnit($row[3]);
            $dataMapping->setTariefType($row[4]);

            $tariefSoort = $this->tariefSoortRepository->findOneBy(['tariefType' => $dataMapping->getTariefType(), 'label' => $row[5]]);
            $dataMapping->setTariefSoort($tariefSoort);

            $this->entityManager->persist($dataMapping);

            ++$i;
        }

        $this->entityManager->flush();

        $io->success("$i rows written to Dagvergunning Mapping entity.");

        return Command::SUCCESS;
    }
}
