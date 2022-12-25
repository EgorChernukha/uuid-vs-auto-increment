<?php

declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\MySQL;

use VUdaltsov\UuidVsAutoIncrement\AutoIncrementBenchmark\AutoIncrementTable;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\Memory;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\TimePeriod;

final class MySQLAutoIncrementTable implements AutoIncrementTable
{
    public function __construct(
        private readonly MySQLDatabase $database,
    ) {
        $this->database->execute(
            <<<'SQL'
                drop table if exists int_auto_increment;
                create table int_auto_increment (
                    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
                    PRIMARY KEY (id)
                )
                SQL,
        );
    }

    public function measureInsertExecutionTime(int $rowsNumber): TimePeriod
    {
        $values = implode(',', array_fill(0, $rowsNumber, '(default)'));

        return $this->database->measureExecutionTime(
            <<<SQL
                insert into int_auto_increment (id)
                values {$values}
                SQL,
        );
    }

    public function measureSelectExecutionTime(array $ids): TimePeriod
    {
        $inValue = implode(', ', $ids);

        return $this->database->measureExecutionTime(
            <<<SQL
                select id
                from int_auto_increment
                where id in ({$inValue})
                SQL,
        );
    }

    public function measureIndexSize(): Memory
    {
        return $this->database->measureIndexesSize('int_auto_increment');
    }
}
