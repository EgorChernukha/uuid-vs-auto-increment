<?php
declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\VarBinaryBenchmark;

use VUdaltsov\UuidVsAutoIncrement\Benchmark\Benchmark;
use VUdaltsov\UuidVsAutoIncrement\Benchmark\Writer;
use VUdaltsov\UuidVsAutoIncrement\Database\Database;

class VarBinaryBenchmark implements Benchmark
{
    private $nextId = 0;

    public function __construct() {
    }

    public function run(Database $database, int $total, int $step, int $select, Writer $writer): void
    {
        $writer->write([
            'Rows',
            'Insert time, ms',
            'Select time, ms',
            'Index size, MiB',
        ]);

        $table = $database->createTable(VarBinaryTable::class)
            ?? throw new \RuntimeException(sprintf(
                '"%s" does not support "%s".',
                $database::class,
                VarBinaryTable::class,
            ));

        for ($rows = $step; $rows <= $total; $rows += $step) {
            $ids = $this->generateIds($step);

            $randomIds = $this->randomIds(
                min: $rows - $step,
                max: $rows,
                number: $select,
            );

            $writer->write([
                sprintf('%dk', $rows / 1000),
                $table->measureInsertExecutionTime($ids)->milliseconds(),
                $table->measureSelectExecutionTime($randomIds)->milliseconds(),
                $table->measureIndexSize()->mebibytes(),
            ]);
        }
    }

    /**
     * @param positive-int $step
     * @return non-empty-list<string>
     */
    private function generateIds(int $step): array
    {
        $ids = [];

        for ($i = 0; $i < $step; ++$i) {
            $ids[] = 'isoru-' . $this->nextId++;
        }
        shuffle($ids);

        /** @var non-empty-list<string> */
        return $ids;
    }

    /**
     * @param positive-int $number
     * @return non-empty-list<string>
     */
    private function randomIds(int $min, int $max, int $number): array
    {
        $ids = [];

        for ($i = 0; $i < $number; ++$i) {
            $ids[] = 'isoru-' . random_int($min, $max);
        }

        /** @var non-empty-list<string> */
        return $ids;
    }
}