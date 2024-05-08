<?php
declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\VarBinaryBenchmark;

use VUdaltsov\UuidVsAutoIncrement\Database\Table;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\Memory;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\TimePeriod;

interface VarBinaryTable extends Table
{
    /**
     * @param non-empty-list<string> $ids
     */
    public function measureInsertExecutionTime(array $ids): TimePeriod;

    /**
     * @param non-empty-list<string> $ids
     */
    public function measureSelectExecutionTime(array $ids): TimePeriod;

    public function measureIndexSize(): Memory;
}