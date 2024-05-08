<?php

declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\MySQL;

use VUdaltsov\UuidVsAutoIncrement\Stopwatch\Memory;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\TimePeriod;
use VUdaltsov\UuidVsAutoIncrement\UuidBenchmark\UuidTable;

final class MySQLUuidTable implements UuidTable
{
    public function __construct(
        private readonly MySQLDatabase $database,
    ) {
        $this->database->execute(
            <<<'SQL'
                drop table if exists uuid;
                create table uuid (
                    id BINARY(16) not null,
                    PRIMARY KEY (id)
                )
                SQL,
        );
    }

    public function measureInsertExecutionTime(array $uuids): TimePeriod
    {
        $values = implode(',', array_map(
            static fn (string $uuid): string => "(UUID_TO_BIN('{$uuid}'))",
            $uuids,
        ));

        return $this->database->measureExecutionTime(
            <<<SQL
                insert into uuid (id)
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
                from uuid
                where id in ({$inValue})
                SQL,
        );
    }

    public function measureIndexSize(): Memory
    {
        return $this->database->measureIndexesSize('uuid');
    }
}
