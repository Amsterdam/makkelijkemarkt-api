<?php

declare(strict_types=1);

namespace App\Process;

use App\Utils\CsvIterator;
use App\Utils\Logger;
use Doctrine\DBAL\Query\QueryBuilder;

class PerfectViewSollicitatieImport
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $markten;

    public function __construct(\Doctrine\DBAL\Connection $conn)
    {
        $this->conn = $conn;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function execute(CsvIterator $content)
    {
        $headings = $content->getHeadings();
        $requiredHeadings = ['Afkorting', 'Erkenningsnummer', 'SollicitantenNummer', 'MarktStatus', 'PreDoorhaalStatus', 'Aantal3', 'Aantal4', 'Aantal1', 'Aantelek', 'Krachtstroom', 'DoorHaalReden', 'PLTSNR1', 'PLTSNR2', 'PLTSNR3', 'PLTSNR4', 'PLTSNR5', 'PLTSNR6', 'PLTSNR7', 'PLTSNR8', 'Koppelveld'];
        foreach ($requiredHeadings as $requiredHeading) {
            if (false === in_array($requiredHeading, $headings)) {
                throw new \RuntimeException('Missing column "'.$requiredHeading.'" in import file');
            }
        }

        // iterate the csv-file
        foreach ($content as $pvRecord) {
            // skip empty records
            if (null === $pvRecord || '' === $pvRecord) {
                $this->logger->info('Skip, record is empty');
                continue;
            }

            $upperCaseAfkorting = strtoupper($pvRecord['Afkorting']);

            // get relation fields
            $markt = $this->getMarktRecord($upperCaseAfkorting);
            if (null === $markt) {
                $this->logger->warning('Skip record, MARKT not found in database', ['Koppelveld' => $pvRecord['Koppelveld'], 'Markt afkorting' => $upperCaseAfkorting]);
                continue;
            }
            $koopman = $this->getKoopmanRecord($pvRecord['Erkenningsnummer']);
            if (null === $koopman) {
                continue;
            }
            if ('' === $pvRecord['SollicitantenNummer'] || null === $pvRecord['SollicitantenNummer']) {
                $this->logger->warning('Skip record, SollicitantenNummer-nummer is empty');
                continue;
            }
            if ('' === $pvRecord['Erkenningsnummer'] || null === $pvRecord['Erkenningsnummer']) {
                $this->logger->warning('Skip record, Erkenningsnummer is empty');
                continue;
            }
            if ('' === $pvRecord['Koppelveld'] || null === $pvRecord['Koppelveld']) {
                $this->logger->warning('Skip record, Koppelveld is empty');
                continue;
            }

            // get the record from the database (if it is already in the database)
            $solliciatieRecord = $this->getSolliciatieRecord($pvRecord['Koppelveld']);
            // prepare query builder
            $qb = $this->conn->createQueryBuilder();

            if (null !== $solliciatieRecord) {
                // update
                $qb->update('sollicitatie', 'e');
                $qb->where('e.id = :id')->setParameter('id', $solliciatieRecord['id']);
            } else {
                // insert
                $qb->insert('sollicitatie');
                $qb->setValue('id', 'NEXTVAL(\'sollicitatie_id_seq\')'); // IMPORTANT setValue on Query Builder, not via helper!
            }

            // set data
            $this->setValue($qb, 'markt_id', \PDO::PARAM_INT, $markt['id']);
            $this->setValue($qb, 'koopman_id', \PDO::PARAM_INT, $koopman['id']);
            $this->setValue($qb, 'sollicitatie_nummer', \PDO::PARAM_INT, $pvRecord['SollicitantenNummer']);
            $this->setValue($qb, 'status', \PDO::PARAM_STR, $this->convertMarktstatus(('Doorgehaald' === $pvRecord['MarktStatus']) ? $pvRecord['PreDoorhaalStatus'] : $pvRecord['MarktStatus']));
            $this->setValue($qb, 'vaste_plaatsen', \PDO::PARAM_STR, utf8_encode(implode(',', $this->getVastePlaatsenArray($pvRecord))));
            $this->setValue($qb, 'aantal_3meter_kramen', \PDO::PARAM_INT, intval($pvRecord['Aantal3']));
            $this->setValue($qb, 'aantal_4meter_kramen', \PDO::PARAM_INT, intval($pvRecord['Aantal4']));
            $this->setValue($qb, 'aantal_extra_meters', \PDO::PARAM_INT, intval($pvRecord['Aantal1']));
            $this->setValue($qb, 'aantal_elektra', \PDO::PARAM_INT, intval($pvRecord['Aantelek']));
            $this->setValue($qb, 'aantal_afvaleilanden', \PDO::PARAM_INT, intval($pvRecord['AANTAFV']));

            // NOTE: we slaan hier hetzelfde om toe te werken naar krachstroom per stuk. De krachtstroom kolom wordt deprecated.
            $this->setValue($qb, 'krachtstroom', \PDO::PARAM_BOOL, in_array($pvRecord['Krachtstroom'], ['True', '1', 1]));
            $this->setValue($qb, 'krachtstroom_per_stuk', \PDO::PARAM_INT, in_array($pvRecord['Krachtstroom'], ['True', '1', 1]) ? 1 : 0);

            $this->setValue($qb, 'inschrijf_datum', \PDO::PARAM_STR, $this->convertToDateTimeString($pvRecord['Inschrijfdatum'].' '.$pvRecord['Inschrijftijd']));
            $this->setValue($qb, 'doorgehaald', \PDO::PARAM_BOOL, 'Doorgehaald' === $pvRecord['MarktStatus']);
            $this->setValue($qb, 'doorgehaald_reden', \PDO::PARAM_STR, utf8_encode($pvRecord['DoorHaalReden']));
            // $this->setValue($qb, 'perfect_view_nummer',     \PDO::PARAM_INT,  $pvRecord['Kaartnr']);
            $this->setValue($qb, 'koppelveld', \PDO::PARAM_STR, $pvRecord['Koppelveld']);

            // execute insert/update query
            $result = $this->conn->executeUpdate($qb->getSQL(), $qb->getParameters(), $qb->getParameterTypes());
        }
    }

    /**
     * @param string $koppelveld
     *
     * @return array|null Sollicitatie-record
     */
    protected function getSolliciatieRecord($koppelveld)
    {
        $qb = $this->conn->createQueryBuilder()->select('e.*')->from('sollicitatie', 'e');
        $qb->where('e.koppelveld = :koppelveld')->setParameter('koppelveld', $koppelveld);

        $stmt = $this->conn->executeQuery($qb->getSQL(), $qb->getParameters());

        if (0 === $stmt->rowCount()) {
            return null;
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param number $afkorting
     *
     * @return array Markt-record
     */
    protected function getMarktRecord($afkorting)
    {
        $this->preloadMarkten();

        if (false === isset($this->markten[$afkorting])) {
            return null;
        }

        return $this->markten[$afkorting];
    }

    /**
     * Internal function to preload all the markt data, markt data is a small dataset which easy fits in memory.
     */
    protected function preloadMarkten()
    {
        if (null !== $this->markten) {
            return;
        }

        $qb = $this->conn->createQueryBuilder()->select('e.*')->from('markt', 'e')->orderBy('e.perfect_view_nummer', 'ASC');
        $stmt = $this->conn->executeQuery($qb->getSQL());

        $this->markten = [];
        while ($record = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $this->markten[$record['afkorting']] = $record;
        }
    }

    /**
     * @param string $erkenningsnummer
     *
     * @return array Koopman-record
     */
    protected function getKoopmanRecord($erkenningsnummer)
    {
        // remove the dot from this value
        $erkenningsnummer = str_replace('.', '', $erkenningsnummer);

        $qb = $this->conn->createQueryBuilder()->select('e.*')->from('koopman', 'e');
        $qb->where('e.erkenningsnummer = :erkenningsnummer')->setParameter('erkenningsnummer', $erkenningsnummer);

        $stmt = $this->conn->executeQuery($qb->getSQL(), $qb->getParameters());

        if (0 === $stmt->rowCount()) {
            return null;
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper method for marktstatus.
     *
     * @param string $status
     *
     * @return string
     */
    private function convertMarktstatus($status)
    {
        if (true === in_array(strtolower($status), ['vpl', 'vkk', 'soll', 'eb'])) {
            return strtolower($status);
        }
        if ('tvpl' == strtolower($status)) {
            return 'tvpl';
        }
        if ('exp. zone' == strtolower($status)) {
            return 'exp';
        }
        if ('tvplz' == strtolower($status)) {
            return 'tvplz';
        }
        if ('exp. zonef' == strtolower($status)) {
            return 'expf';
        }

        return '?';
    }

    /**
     * @param string $datetime_string
     *
     * @return string
     */
    private function convertToDateTimeString($datetime_string)
    {
        $object = \DateTime::createFromFormat('d-m-Y H:i:s', $datetime_string);
        if (false === $object) {
            return null;
        }

        return $object->format('Y-m-d H:i:s');
    }

    /**
     * Helper method for array of vaste plaatsen from Perfect View record.
     *
     * @param array $pvRecord
     *
     * @return multitype:string
     */
    private function getVastePlaatsenArray($pvRecord)
    {
        $a = [];
        if (true === isset($pvRecord['PLTSNR1']) && '' !== $pvRecord['PLTSNR1']) {
            $a[] = $pvRecord['PLTSNR1'];
        }
        if (true === isset($pvRecord['PLTSNR2']) && '' !== $pvRecord['PLTSNR2']) {
            $a[] = $pvRecord['PLTSNR2'];
        }
        if (true === isset($pvRecord['PLTSNR3']) && '' !== $pvRecord['PLTSNR3']) {
            $a[] = $pvRecord['PLTSNR3'];
        }
        if (true === isset($pvRecord['PLTSNR4']) && '' !== $pvRecord['PLTSNR4']) {
            $a[] = $pvRecord['PLTSNR4'];
        }
        if (true === isset($pvRecord['PLTSNR5']) && '' !== $pvRecord['PLTSNR5']) {
            $a[] = $pvRecord['PLTSNR5'];
        }
        if (true === isset($pvRecord['PLTSNR6']) && '' !== $pvRecord['PLTSNR6']) {
            $a[] = $pvRecord['PLTSNR6'];
        }

        return $a;
    }

    /**
     * Helper function to abstract INSERT and UPDATE.
     *
     * @param string $field
     * @param string $value
     */
    private function setValue(QueryBuilder $qb, $field, $type = null, $value = null)
    {
        if (QueryBuilder::UPDATE === $qb->getType()) {
            $qb->set($field, ':'.$field)->setParameter($field, $value, $type);
        } else {
            $qb->setValue($field, ':'.$field)->setParameter($field, $value, $type);
        }
    }
}
