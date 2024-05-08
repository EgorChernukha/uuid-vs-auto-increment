<?php
declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\MySQL;

use VUdaltsov\UuidVsAutoIncrement\Stopwatch\Memory;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\TimePeriod;
use VUdaltsov\UuidVsAutoIncrement\VarBinaryBenchmark\VarBinaryTable;

final class MySQLVarBinaryTable implements VarBinaryTable
{
    public function __construct(
        private readonly MySQLDatabase $database,
    ) {
        $this->database->execute(
            <<<'SQL'
                drop table if exists vb;
                create table vb (
                    id VARBINARY(63) not null,
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
                insert into vb (id)
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