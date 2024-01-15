<?php

declare(strict_types=1);

namespace App\Process;

use App\Entity\Koopman;
use App\Entity\KoopmanRepository;
use App\Utils\Logger;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;

class PerfectViewKoopmanImport
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var KoopmanRepository
     */
    protected $koopmanRepository;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $soortStatusConversion = [
        'Actief' => Koopman::STATUS_ACTIEF,
        'Koopman' => Koopman::STATUS_ACTIEF,
        'Verwijderd' => Koopman::STATUS_VERWIJDERD,
        'Wachter' => Koopman::STATUS_WACHTER,
        'Vervanger' => Koopman::STATUS_VERVANGER,
    ];

    public function __construct(\Doctrine\DBAL\Connection $conn)
    {
        $this->conn = $conn;
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
        $requiredHeadings = ['Erkenningsnummer', 'ACHTERNAAM', 'email', 'Telefoonnummer', 'Voorletters', 'Status', 'NFCHEX', 'Tussenvoegsels'];
        foreach ($requiredHeadings as $requiredHeading) {
            if (false === in_array($requiredHeading, $headings)) {
                throw new \RuntimeException('Missing column "'.$requiredHeading.'" in import file');
            }
        }

        $i = 0;
        $aantalNieuw = 0;
        $aantalBijgewerkt = 0;

        // iterate the csv-file
        foreach ($perfectViewRecords as $pvRecord) {
            // skip empty records
            if (null === $pvRecord || '' === $pvRecord) {
                $this->logger->info('Skip, record is empty');
                continue;
            }

            // get the record from the database (if it is already in the database)
            $koopmanRecord = $this->getKoopmanRecord($pvRecord['Erkenningsnummer']);
            // prepare query builder
            $qb = $this->conn->createQueryBuilder();

            if (null !== $koopmanRecord) {
                // update
                $qb->update('koopman', 'e');
                $qb->where('e.id = :id')->setParameter('id', $koopmanRecord['id']);
                ++$aantalNieuw;
            } else {
                // insert
                $qb->insert('koopman');
                $qb->setValue('id', 'NEXTVAL(\'koopman_id_seq\')'); // IMPORTANT setValue on Query Builder, not via helper!
                ++$aantalBijgewerkt;
            }

            // set data
            $this->setValue($qb, 'erkenningsnummer', \PDO::PARAM_STR, str_replace('.', '', $pvRecord['Erkenningsnummer']));
            $this->setValue($qb, 'achternaam', \PDO::PARAM_STR, utf8_encode(str_replace('.', '', $pvRecord['ACHTERNAAM'])));
            $this->setValue($qb, 'email', \PDO::PARAM_STR, utf8_encode($pvRecord['email']));
            $this->setValue($qb, 'telefoon', \PDO::PARAM_STR, str_replace('.', '', $pvRecord['Telefoonnummer']));
            $this->setValue($qb, 'voorletters', \PDO::PARAM_STR, utf8_encode(str_replace('.', '', $pvRecord['Voorletters'])));
            $this->setValue($qb, 'tussenvoegsels', \PDO::PARAM_STR, utf8_encode($pvRecord['Tussenvoegsels']));
            $this->setValue($qb, 'status', \PDO::PARAM_STR, $this->convertKoopmanStatus($pvRecord['Status']));
            // $this->setValue($qb, 'perfect_view_nummer',  \PDO::PARAM_INT,  $pvRecord['Kaartnr']);
            $this->setValue($qb, 'pas_uid', \PDO::PARAM_STR, strtoupper($pvRecord['NFCHEX']));

            // execute insert/update query
            $result = $this->conn->executeUpdate($qb->getSQL(), $qb->getParameters(), $qb->getParameterTypes());
        }

        $this->logger->info('Alle records verwerkt', ['nieuw' => $aantalNieuw, 'bijgewerkt' => $aantalBijgewerkt, 'totaal' => $i]);
    }

    /**
     * @param string $erkenningsnummer
     *
     * @return array|null Koopman-record
     */
    protected function getKoopmanRecord($erkenningsnummer)
    {
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
     * Helper method for koopmanstatus.
     *
     * @param string $status
     *
     * @return string
     */
    private function convertKoopmanStatus($status)
    {
        if (true === isset($this->soortStatusConversion[$status])) {
            return $this->soortStatusConversion[$status];
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
