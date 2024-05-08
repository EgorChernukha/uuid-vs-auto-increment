<?php

declare(strict_types=1);

namespace VUdaltsov\UuidVsAutoIncrement\MySQL;

use VUdaltsov\UuidVsAutoIncrement\AutoIncrementBenchmark\AutoIncrementTable;
use VUdaltsov\UuidVsAutoIncrement\Database\Database;
use VUdaltsov\UuidVsAutoIncrement\Database\Table;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\Memory;
use VUdaltsov\UuidVsAutoIncrement\Stopwatch\TimePeriod;
use VUdaltsov\UuidVsAutoIncrement\UuidBenchmark\UuidTable;
use VUdaltsov\UuidVsAutoIncrement\VarBinaryBenchmark\VarBinaryTable;
use VUdaltsov\UuidVsAutoIncrement\VarCharBenchmark\VarCharTable;

final class MySQLDatabase implements Database
{
    private readonly \PDO $connection;

    /**
     * @param non-empty-string $dbName
     * @param non-empty-string $user
     * @param non-empty-string $password
     * @param non-empty-string $host
     * @param positive-int     $port
     */
    public function __construct(
        string $host = '0.0.0.0',
        int $port = 33306,
        string $dbName = 'benchmark',
        string $user = 'root',
        string $password = 'root',
    ) {
        $this->connection = new \PDO("mysql:host={$host};port={$port};dbname={$dbName}", $user, $password);
    }

    /**
     * @template T of Table
     * @param class-string<T> $class
     * @return ?T
     */
    public function createTable(string $class): ?Table
    {
        /** @var ?T */
        return match ($class) {
            AutoIncrementTable::class => new MySQLAutoIncrementTable($this),
            UuidTable::class => new MySQLUuidTable($this),
            VarBinaryTable::class => new MySQLVarBinaryTable($this),
            VarCharTable::class => new MySQLVarCharTable($this),
            default => null,
        };
    }

    public function execute(string $query): \PDOStatement
    {
        return $this->connection->query($query);
    }

    public function measureIndexesSize(string $table): Memory
    {
        // Run analyze table to update table statistics
        $statement = $this->connection->query("ANALYZE TABLE {$table}");
        $statement->fetch();

        $statement = $this->connection->prepare(
            <<<SQL
            SELECT
              INDEX_LENGTH + DATA_LENGTH as bytes
            FROM
              information_schema.TABLES 
            WHERE TABLE_NAME = :table;
            SQL,
        );
        $statement->execute([':table' => $table]);

        $bytes = $statement->fetchColumn()
            ?: throw new \RuntimeException(sprintf('Failed to get indexes size of "%s".', $table));

        return new Memory((int) $bytes);
    }

    public function measureExecutionTime(string $query): TimePeriod
    {
        $start = microtime(true);
        $this->execute($query);
        $end = microtime(true);

        $milliseconds = $end - $start;

        return new TimePeriod((int) ($milliseconds * 1_000_000));
    }
}
