<?php

declare(strict_types=1);

namespace App\Process;

use App\Entity\Markt;
use App\Repository\MarktRepository;
use App\Utils\Logger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class PerfectViewMarktImport
{
    /**
     * @var MarktRepository
     */
    protected $marktRepository;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $soortMarkConversion = [
        'Dag' => Markt::SOORT_DAG,
        'Week' => Markt::SOORT_WEEK,
        'Seizoen' => Markt::SOORT_SEIZOEN,
    ];

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(MarktRepository $marktRepository, EntityManagerInterface $em)
    {
        $this->marktRepository = $marktRepository;
        $this->em = $em;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $perfectViewRecords
     */
    public function execute($perfectViewRecords)
    {
        $headings = $perfectViewRecords->getHeadings();
        $requiredHeadings = ['AFKORTING', 'MARKTNAAM', 'SOORT_MARK', 'A1_METER', 'A3_METER', 'A4_METER', 'ELEKTRA', 'KRACHTROOM'];
        foreach ($requiredHeadings as $requiredHeading) {
            if (false === in_array($requiredHeading, $headings)) {
                throw new \RuntimeException('Missing column "'.$requiredHeading.'" in import file');
            }
        }

        foreach ($perfectViewRecords as $pvRecord) {
            // skip empty records
            if (null === $pvRecord || '' === $pvRecord) {
                $this->logger->info('Skip, record is empty');
                continue;
            }

            $upperCaseAfkorting = strtoupper($pvRecord['AFKORTING']);

            $this->logger->info('PerfectView record import', ['afkorting' => $upperCaseAfkorting]);
            $markt = $this->marktRepository->getByAfkorting($upperCaseAfkorting);

            // create new markt
            if (null === $markt) {
                $this->logger->info('Nieuwe markt, aanmaken in database', ['afkorting' => $upperCaseAfkorting]);
                $markt = new Markt();
                $this->em->persist($markt);
            } else {
                $this->logger->info('Bestaande markt, bijwerken in database', ['afkorting' => $upperCaseAfkorting, 'id' => $markt->getId()]);
            }

            // update markt
            $markt->setAfkorting($upperCaseAfkorting);
            $markt->setNaam($pvRecord['MARKTNAAM']);
            $markt->setSoort($this->soortMarkConversion[$pvRecord['SOORT_MARK']]);
            $markt->setExtraMetersMogelijk('True' === $pvRecord['A1_METER']);
            $markt->setStandaardKraamAfmeting((('True' === $pvRecord['A3_METER']) ? 3 : (('True' === $pvRecord['A4_METER']) ? 4 : 0)));

            $opties = [];
            if ('True' === $pvRecord['A3_METER'] || 'Waar' === $pvRecord['A3_METER']) {
                $opties[] = '3mKramen';
            }
            if ('True' === $pvRecord['A4_METER'] || 'Waar' === $pvRecord['A4_METER']) {
                $opties[] = '4mKramen';
            }
            if ('True' === $pvRecord['A1_METER'] || 'Waar' === $pvRecord['A1_METER']) {
                $opties[] = 'extraMeters';
            }

            // Only show the afvaleiland option in the app for markets with a Concreet Plan
            if ((
                    'True' === $pvRecord['AFVAL'] || 'Waar' === $pvRecord['AFVAL']
                ) && (
                    'BKSL' === $upperCaseAfkorting
                    || 'BBKSL' === $upperCaseAfkorting
                    || 'TK' === $upperCaseAfkorting
                )
            ) {
                $opties[] = 'afvaleiland';
            }
            if ('True' === $pvRecord['KRACHTROOM'] || 'Waar' === $pvRecord['KRACHTROOM']) {
                $opties[] = 'elektra';
                $opties[] = 'krachtstroom';
            }
            /* TODO: Zorg dat deze optie in perfectview gedefineerd wordt */
            if ('PEK' === $upperCaseAfkorting) {
                $opties[] = 'eenmaligElektra';
            }
            if ('WAT-2022' === $upperCaseAfkorting) {
                $opties[] = 'grootPerMeter';
                $opties[] = 'kleinPerMeter';
            }

            // Add this option manually, because it's currently not set in Mercato
            $opties[] = 'afvalEilandAgf';

            /* End fix */
            $markt->setAanwezigeOpties($opties);
        }

        $this->em->flush();
    }
}
