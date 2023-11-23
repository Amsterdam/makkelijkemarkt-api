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
        $columnNames = ['dagvergunning_key', 'mercato_key', 'translated_to_unit', 'unit', 'tarief_type', 'tariefSoort.label', 'app_label', 'input_type'];

        // TODO after release to PRD this should also be a fixture
        $data = [
            ['aantal3MeterKramen', 'Aantal3', 3, 'meters', 'lineair', null, '3m plaatsen', 'number'],
            ['aantal4MeterKramen', 'Aantal4', 4, 'meters', 'lineair', null, '4m plaatsen', 'number'],
            ['extraMeters', 'Aantal1', 1, 'meters', 'lineair', null, 'Extra meters', 'number'],
            ['aantalElektra', 'Aantelek', 1, 'unit', 'lineair', 'Elektra', 'Elektra', 'number'],
            ['afvaleiland', 'AANTAFV', 1, 'unit', 'lineair', 'Afvaleiland', 'Afvaleiland', 'number'],
            ['grootPerMeter', null, 1, 'meters-groot', 'lineair', null, 'Extra meters Groot', 'number'],
            ['kleinPerMeter', null, 1, 'meters-klein', 'lineair', null, 'Extra meters Klein', 'number'],
            ['afvalEilandAgf', null, 1, 'unit', 'lineair', 'Agf per meter', 'AGF per meter', 'number'],
            ['krachtstroomPerStuk', 'Krachtstroom', 1, 'unit', 'lineair', 'Toeslag krachtstroom per aansluiting', 'Krachtstroom', 'number'],
            ['aantal3MeterKramen', 'Aantal3', 1, 'unit', 'concreet', 'Drie meter', '3m plaatsen', 'number'],
            ['aantal4MeterKramen', 'Aantal4', 1, 'unit', 'concreet', 'Vier meter', '4m plaatsen', 'number'],
            ['extraMeters', 'Aantal1', 1, 'unit', 'concreet', 'Een meter', 'Extra meters', 'number'],
            ['aantalElektra', 'Aantelek', 1, 'unit', 'concreet', 'Elektra', 'Elektra', 'number'],
            ['afvaleiland', 'AANTAFV', 1, 'unit', 'concreet', 'Afvaleiland', 'Afvaleiland', 'number'],
            ['afvalEilandAgf', null, 1, 'unit', 'concreet', 'Agf per meter', 'AGF per meter', 'number'],
            ['krachtstroomPerStuk', 'Krachtstroom', 1, 'unit', 'concreet', 'Toeslag krachtstroom per aansluiting', 'Krachtstroom', 'number'],

             // Legacy that we need to support otherwise data migrations will probably fail
             // Can be removed after deployment to production
            ['eenmaligElektra', null, 1, 'one-off', 'lineair', 'Eenmalig elektra', 'Eenmalig Elektra', 'number'],
            ['eenmaligElektra', null, 1, 'one-off', 'concreet', 'Eenmalig elektra', 'Eenmalig Elektra', 'number'],
        ];

        foreach ($data as $row) {
            $dataMapping = (new DagvergunningMapping())
                ->setDagvergunningKey($row[0])
                ->setMercatoKey($row[1])
                ->setTranslatedToUnit((int) $row[2])
                ->setUnit($row[3])
                ->setTariefType($row[4])
                ->setAppLabel($row[6])
                ->setInputType($row[7]);

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
