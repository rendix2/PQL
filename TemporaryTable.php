<?php

namespace pql;

use Exception;
use Iterator;
use Nette\Utils\FileSystem;
use SplFileObject;

/**
 * Class TemporaryTable
 */
class TemporaryTable implements ITable, Iterator
{
    private const EXT = 'tmp';
    private const DELIMITER = ', ';

    private string $path;

    /**
     * @var TableColumn[] $columns
     */
    private array $columns;

    private int $rowsCount = 0;

    private ?SplFileObject $stream;

    public function __construct(array $columns, Iterator $dataIterator)
    {
        $this->columns = $columns;
        $this->stream = null;
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('pql_temp_') . '.' . self::EXT;

        $this->materializeData($dataIterator);
    }

    public function __destruct()
    {
        $this->stream = null;

        if (file_exists($this->path)) {
            FileSystem::delete($this->path);
        }
    }

    private function materializeData(Iterator $dataIterator): void
    {
        $handle = new SplFileObject($this->path, 'w');
        $rowsCount = 0;

        foreach ($dataIterator as $row) {
            $handle->fwrite(implode(self::DELIMITER, $row) . "\n");
            $rowsCount++;
        }

        $this->rowsCount = $rowsCount;
        $handle = null;
    }

    /**
     * @return TableColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getRowsCount(): int
    {
        return $this->rowsCount;
    }

    public function getRows($object = false): void
    {
        throw new Exception();
    }

    public function getName(): string
    {
        return "TEMP_TABLE_" . basename($this->path);
    }

    public function rewind(): void
    {
        if ($this->stream === null) {
            $this->stream = new SplFileObject($this->path, 'r');
        } else {
            $this->stream->rewind();
        }
    }

    public function current(): array
    {
        $rawLine = $this->stream->current();

        if (empty($rawLine)) { return []; }

        return explode(self::DELIMITER, trim($rawLine));
    }

    public function key(): int
    {
        return $this->stream->key();
    }

    public function next(): void
    {
        $this->stream->next();
    }

    public function valid(): bool
    {
        return $this->stream->valid();
    }

    public function getIterator(): Iterator
    {
        return $this;
    }

    public function getArray(): array
    {
        return iterator_to_array($this);
    }

}
