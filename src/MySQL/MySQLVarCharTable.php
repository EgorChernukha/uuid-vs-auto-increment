<?php
declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\MySQL;

use VUdaltsov\UuidVsAutoIncrement\Stopwatch\Memory;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\TimePeriod;
use VUdaltsov\UuidVsAutoIncrement\VarBinaryBenchmark\VarBinaryTable;
use VUdaltsov\UuidVsAutoIncrement\VarCharBenchmark\VarCharTable;

final class MySQLVarCharTable implements VarCharTable
{
    public function __construct(
        private readonly MySQLDatabase $database,
    ) {
        $this->database->execute(
            <<<'SQL'
                drop table if exists vch;
                create table vch (
                    id VARCHAR(255) not null,
                    PRIMARY KEY (id)
                )
                SQL,
        );
    }

    public function measureInsertExecutionTime(array $ids): TimePeriod
    {
        $values = implode(',', array_map(
            static fn (string $id): string => "('{$id}')",
            $ids,
        ));

        return $this->database->measureExecutionTime(
            <<<SQL
                insert into vch (id)
                values {$values}
                SQL,
        );
    }

    public function measureSelectExecutionTime(array $ids): TimePeriod
    {
        $inValue = implode(',', array_map(
            static fn (string $id): string => "'{$id}'",
            $ids,
        ));

        return $this->database->measureExecutionTime(
            <<<SQL
                select id
                from vch
                where id in ({$inValue})
                SQL,
        );
    }

    public function measureIndexSize(): Memory
    {
        return $this->database->measureIndexesSize('vch');
    }
}