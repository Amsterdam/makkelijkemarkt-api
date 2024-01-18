<?php

declare(strict_types=1);

namespace App\Utils;

class CsvIterator implements \Iterator
{
    // configuration
    private $csvDelimiter = ';';
    private $csvEnclosure = '"';
    private $csvEscape = '\\';

    private $file;
    private $stream;
    private $currentLine;
    private $counter;
    private $headings;

    public function __construct($file)
    {
        $this->file = $file;
        $this->rewind();
    }

    public function rewind(): void
    {
        if (null !== $this->stream) {
            fclose($this->stream);
        }
        $this->stream = fopen($this->file, 'r');
        $this->counter = 0;

        $headingsLine = fgets($this->stream);
        $headings = str_getcsv($headingsLine, $this->csvDelimiter, $this->csvEnclosure, $this->csvEscape);
        $this->headings = array_map('trim', $headings);
    }

    public function current(): array|null
    {
        return $this->parseLine($this->currentLine);
    }

    public function key(): int
    {
        return $this->counter;
    }

    public function next(): void
    {
        $this->currentLine = fgets($this->stream);
        ++$this->counter;
    }

    public function valid(): bool
    {
        return false === feof($this->stream);
    }

    public function getHeadings(): array
    {
        return $this->headings;
    }

    protected function parseLine($line): array|null
    {
        if ('' === $line || null === $line) {
            return null;
        }

        $values = str_getcsv($line, $this->csvDelimiter, $this->csvEnclosure, $this->csvEscape);

        $record = [];
        foreach ($this->headings as $pos => $name) {
            if (false === isset($values[$pos])) {
                $record[$name] = null;
            } else {
                $record[$name] = trim($values[$pos]);
            }
        }

        return $record;
    }
}
