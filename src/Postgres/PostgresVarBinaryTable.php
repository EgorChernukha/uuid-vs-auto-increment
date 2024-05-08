<?php

declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\Postgres;

use VUdaltsov\UuidVsAutoIncrement\Stopwatch\Memory;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\TimePeriod;
use VUdaltsov\UuidVsAutoIncrement\UuidBenchmark\UuidTable;
use VUdaltsov\UuidVsAutoIncrement\VarBinaryBenchmark\VarBinaryTable;

final class PostgresVarBinaryTable implements VarBinaryTable
{
    public function __construct(
        private readonly PostgresDatabase $database,
    ) {
        $this->database->execute(
            <<<'SQL'
                drop table if exists vb;
                create table vb (
                    id bytea not null primary key
                )
                SQL,
        );
    }

    public function measureInsertExecutionTime(array $uuids): TimePeriod
    {
        $values = implode(',', array_map(
            static fn (string $uuid): string => "('{$uuid}')",
            $uuids,
        ));

        return $this->database->measureExecutionTime(
            <<<SQL
                insert into vb (id)
                values {$values}
                SQL,
        );
    }

    public function measureSelectExecutionTime(array $uuids): TimePeriod
    {
        $inValue = implode(',', array_map(
            static fn (string $uuid): string => "'{$uuid}'",
            $uuids,
        ));

        return $this->database->measureExecutionTime(
            <<<SQL
                select id
                from vb
                where id in ({$inValue})
                SQL,
        );
    }

    public function measureIndexSize(): Memory
    {
        return $this->database->measureIndexesSize('vb');
    }
}
