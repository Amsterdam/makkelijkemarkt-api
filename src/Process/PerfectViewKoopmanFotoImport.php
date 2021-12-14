<?php

declare(strict_types=1);

namespace App\Process;

use App\Utils\Logger;
use Doctrine\DBAL\Query\QueryBuilder;
use App\Utils\CsvIterator;
use League\Flysystem\Filesystem;

class PerfectViewKoopmanFotoImport
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var Filesystem
     */
    protected $storage;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param \Doctrine\DBAL\Connection $conn
     * @param Filesystem $storage
     */
    public function __construct(\Doctrine\DBAL\Connection $conn, Filesystem $storage)
    {
        $this->conn = $conn;
        $this->storage = $storage;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $perfectViewData
     * @param string $imageSourceDirectory
     */
    public function execute(CsvIterator $content, $imageSourceDirectory)
    {
        $headings = $content->getHeadings();
        $requiredHeadings = ['Erkenningsnummer', 'FotoKop'];
        foreach ($requiredHeadings as $requiredHeading) {
            if (in_array($requiredHeading, $headings) === false) {
                throw new \RuntimeException('Missing column "' . $requiredHeading . '" in import file');
            }
        }

        // iterate the csv-file
        foreach ($content as $pvRecord) {

            // skip empty records
            if ($pvRecord === null || $pvRecord === '') {
                $this->logger->info('Skip, record is empty');
                continue;
            }

            // get relation fields
            $koopman = $this->getKoopmanRecord($pvRecord['Erkenningsnummer']);
            if ($koopman === null) {
                continue;
            }

            // skip empty foto record
            if ($pvRecord['FotoKop'] === '') {
                $this->logger->warning('Skip record, FOTO field is empty');
                continue;
            }

            // rewrite foto kop value
            $pvRecord['FotoKop'] = str_replace(['\\\\basis.lan\\amsterdamapps\\SDC\\PerfectviewMarktbureau\\Fotos\\'], '', $pvRecord['FotoKop']);

            // get expected import path
            $fullPath = $imageSourceDirectory . DIRECTORY_SEPARATOR . $pvRecord['FotoKop'];
            if (file_exists($fullPath) === false) {
                $this->logger->warning('Skip record, FILE does not exists');
                continue;
            }

            // determine values
            $checksum = md5_file($fullPath, false);
            $filename = $checksum . '-' . $koopman['erkenningsnummer'] . '.jpg';

            // calculate checksum
            if ($this->storage->has($filename) === true && $koopman['foto_hash'] === $checksum) {
                continue;
            }

            // prepare query builder
            $qb = $this->conn->createQueryBuilder();
            $qb->update('koopman', 'e');
            $qb->where('e.id = :id')->setParameter('id', $koopman['id']);

            // copy the file to the new location
            $result = $this->storage->put($filename, file_get_contents($fullPath));
            if ($result === false) {
                $this->logger->error('Can not copy photo to data directory');
                continue;
            }

            // set data
            $this->setValue($qb, 'foto',                \PDO::PARAM_STR,  $filename);
            $this->setValue($qb, 'foto_last_update',    \PDO::PARAM_STR,  date('Y-m-d H:i:s'));
            $this->setValue($qb, 'foto_hash',           \PDO::PARAM_STR,  $checksum);

            // execute insert/update query
            $result = $this->conn->executeUpdate($qb->getSQL(), $qb->getParameters(), $qb->getParameterTypes());

        }
    }

    /**
     * @param string $erkenningsnummer
     * @return array Koopman-record
     */
    protected function getKoopmanRecord($erkenningsnummer)
    {
        // remove the dot from this value
        $erkenningsnummer = str_replace('.', '', $erkenningsnummer);

        $qb = $this->conn->createQueryBuilder()->select('e.*')->from('koopman', 'e');
        $qb->where('e.erkenningsnummer = :erkenningsnummer')->setParameter('erkenningsnummer', $erkenningsnummer);

        $stmt = $this->conn->executeQuery($qb->getSQL(), $qb->getParameters());

        if ($stmt->rowCount() === 0)
            return null;
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string $datetime_string
     * @return string
     */
    private function convertToDateTimeString($datetime_string)
    {
        $object = \DateTime::createFromFormat('d-m-Y H:i:s', $datetime_string);
        if ($object === false)
            return null;
        return $object->format('Y-m-d H:i:s');
    }

    /**
     * Helper function to abstract INSERT and UPDATE
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $qb
     * @param string $field
     * @param string $value
     */
    private function setValue(\Doctrine\DBAL\Query\QueryBuilder $qb, $field, $type = null, $value = null)
    {
        if ($qb->getType() === QueryBuilder::UPDATE) {
            $qb->set($field, ':' . $field)->setParameter($field, $value, $type);
        } else {
            $qb->setValue($field, ':' . $field)->setParameter($field, $value, $type);
        }
    }

}