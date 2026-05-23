<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseSqlExportService
{
    /**
     * @throws RuntimeException
     */
    public function export(): string
    {
        $connection = config('database.default');
        $config = config('database.connections.'.$connection);

        if (! is_array($config)) {
            throw new RuntimeException('Database connection configuration is missing.');
        }

        $driver = (string) ($config['driver'] ?? '');

        return match ($driver) {
            'mysql', 'mariadb' => $this->exportMysql($config),
            'pgsql' => $this->exportPgsql($config),
            'sqlite' => $this->exportSqlite($config),
            default => throw new RuntimeException("Database driver [{$driver}] is not supported for SQL export."),
        };
    }

    public function suggestedFilename(): string
    {
        $connection = (string) config('database.default');

        return 'wemx-'.$connection.'-'.now()->format('Y-m-d-His').'.sql';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function exportMysql(array $config): string
    {
        $binary = $this->findBinary(['mysqldump', 'mariadb-dump']);

        if ($binary !== null) {
            $result = Process::timeout(300)
                ->env(['MYSQL_PWD' => (string) ($config['password'] ?? '')])
                ->run([
                    $binary,
                    '--host='.($config['host'] ?? '127.0.0.1'),
                    '--port='.($config['port'] ?? '3306'),
                    '--user='.($config['username'] ?? 'root'),
                    '--single-transaction',
                    '--quick',
                    '--lock-tables=false',
                    (string) ($config['database'] ?? ''),
                ]);

            if ($result->successful() && trim($result->output()) !== '') {
                return $this->prependSqlHeader($result->output());
            }
        }

        return $this->exportViaPhp();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function exportPgsql(array $config): string
    {
        $binary = $this->findBinary(['pg_dump']);

        if ($binary !== null) {
            $result = Process::timeout(300)
                ->env(['PGPASSWORD' => (string) ($config['password'] ?? '')])
                ->run([
                    $binary,
                    '--host='.($config['host'] ?? '127.0.0.1'),
                    '--port='.($config['port'] ?? '5432'),
                    '--username='.($config['username'] ?? ''),
                    '--dbname='.($config['database'] ?? ''),
                    '--no-owner',
                    '--no-acl',
                ]);

            if ($result->successful() && trim($result->output()) !== '') {
                return $this->prependSqlHeader($result->output());
            }
        }

        return $this->exportViaPhp();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function exportSqlite(array $config): string
    {
        $path = $this->resolveSqlitePath((string) ($config['database'] ?? ''));

        if ($path === ':memory:') {
            throw new RuntimeException('Cannot export an in-memory SQLite database.');
        }

        if (! is_file($path)) {
            throw new RuntimeException('SQLite database file was not found at '.$path.'.');
        }

        $binary = $this->findBinary(['sqlite3']);

        if ($binary !== null) {
            $result = Process::timeout(300)->run([$binary, $path, '.dump']);

            if ($result->successful() && trim($result->output()) !== '') {
                return $this->prependSqlHeader($result->output());
            }
        }

        return $this->exportViaPhp();
    }

    protected function exportViaPhp(): string
    {
        $driver = (string) DB::connection()->getDriverName();
        $lines = [$this->sqlHeader()];

        foreach ($this->listTables() as $table) {
            $lines[] = '';
            $lines[] = '-- Table: '.$table;
            $lines[] = $this->dumpTableSchema($table, $driver);
            $lines = array_merge($lines, $this->dumpTableData($table));
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @return list<string>
     */
    protected function listTables(): array
    {
        $driver = (string) DB::connection()->getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => collect(DB::select('SHOW TABLES'))
                ->map(fn (object $row): string => (string) array_values((array) $row)[0])
                ->sort()
                ->values()
                ->all(),
            'pgsql' => collect(DB::select(
                "SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' ORDER BY tablename"
            ))
                ->map(fn (object $row): string => (string) $row->tablename)
                ->values()
                ->all(),
            'sqlite' => collect(DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            ))
                ->map(fn (object $row): string => (string) $row->name)
                ->values()
                ->all(),
            default => Schema::getTableListing(),
        };
    }

    protected function dumpTableSchema(string $table, string $driver): string
    {
        return match ($driver) {
            'mysql', 'mariadb' => $this->dumpMysqlTableSchema($table),
            'pgsql' => $this->dumpPgsqlTableSchema($table),
            'sqlite' => $this->dumpSqliteTableSchema($table),
            default => '',
        };
    }

    protected function dumpMysqlTableSchema(string $table): string
    {
        $quoted = $this->quoteIdentifier($table, 'mysql');
        $row = DB::selectOne('SHOW CREATE TABLE '.$quoted);

        if ($row === null) {
            return '';
        }

        $create = (string) (get_object_vars($row)['Create Table'] ?? '');

        return "DROP TABLE IF EXISTS {$quoted};\n{$create};";
    }

    protected function dumpPgsqlTableSchema(string $table): string
    {
        $quoted = $this->quoteIdentifier($table, 'pgsql');

        $columns = DB::select(
            'SELECT column_name, data_type, is_nullable, column_default
             FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ?
             ORDER BY ordinal_position',
            ['public', $table]
        );

        if ($columns === []) {
            return '';
        }

        $parts = [];

        foreach ($columns as $column) {
            $definition = $this->quoteIdentifier((string) $column->column_name, 'pgsql').' '.(string) $column->data_type;

            if (($column->is_nullable ?? 'YES') === 'NO') {
                $definition .= ' NOT NULL';
            }

            if ($column->column_default !== null) {
                $definition .= ' DEFAULT '.$column->column_default;
            }

            $parts[] = $definition;
        }

        return 'DROP TABLE IF EXISTS '.$quoted." CASCADE;\nCREATE TABLE {$quoted} (\n  "
            .implode(",\n  ", $parts)."\n);";
    }

    protected function dumpSqliteTableSchema(string $table): string
    {
        $row = DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?",
            [$table]
        );

        if ($row === null || empty($row->sql)) {
            return '';
        }

        $quoted = $this->quoteIdentifier($table, 'sqlite');

        return 'DROP TABLE IF EXISTS '.$quoted.";\n".$row->sql.';';
    }

    /**
     * @return list<string>
     */
    protected function dumpTableData(string $table): array
    {
        $driver = (string) DB::connection()->getDriverName();
        $quotedTable = $this->quoteIdentifier($table, $driver);
        $lines = [];
        $columns = null;
        $query = DB::table($table);
        $orderColumn = $this->resolveChunkOrderColumn($table, $driver);

        if ($orderColumn !== null) {
            if ($orderColumn === 'rowid') {
                $query->orderByRaw('rowid');
            } else {
                $query->orderBy($orderColumn);
            }
        }

        $query->chunk(200, function ($rows) use (&$lines, &$columns, $quotedTable, $driver): void {
            foreach ($rows as $row) {
                $values = (array) $row;

                if ($columns === null) {
                    $columns = array_map(
                        fn (string $column): string => $this->quoteIdentifier($column, $driver),
                        array_keys($values),
                    );
                }

                $quotedValues = array_map(
                    fn (mixed $value): string => $this->quoteSqlValue($value),
                    array_values($values),
                );

                $lines[] = 'INSERT INTO '.$quotedTable.' ('.implode(', ', $columns).') VALUES ('.implode(', ', $quotedValues).');';
            }
        });

        return $lines;
    }

    protected function quoteSqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_resource($value)) {
            return 'NULL';
        }

        return "'".str_replace(['\\', "'"], ['\\\\', "''"], (string) $value)."'";
    }

    protected function quoteIdentifier(string $identifier, string $driver): string
    {
        return match ($driver) {
            'mysql', 'mariadb' => '`'.str_replace('`', '``', $identifier).'`',
            'pgsql' => '"'.str_replace('"', '""', $identifier).'"',
            default => '"'.str_replace('"', '""', $identifier).'"',
        };
    }

    protected function resolveSqlitePath(string $database): string
    {
        if ($database === ':memory:') {
            return $database;
        }

        if ($database !== '' && ($database[0] === '/' || preg_match('/^[A-Za-z]:[\\\\\\/]/', $database) === 1)) {
            return $database;
        }

        return base_path($database !== '' ? $database : 'database/database.sqlite');
    }

    /**
     * @param  list<string>  $candidates
     */
    protected function findBinary(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $result = Process::run(['which', $candidate]);

            if ($result->successful()) {
                $path = trim($result->output());

                if ($path !== '') {
                    return $path;
                }
            }
        }

        return null;
    }

    protected function resolveChunkOrderColumn(string $table, string $driver): ?string
    {
        $columns = Schema::getColumnListing($table);

        if ($columns === []) {
            return null;
        }

        if (in_array('id', $columns, true)) {
            return 'id';
        }

        if ($driver === 'sqlite') {
            return 'rowid';
        }

        return $columns[0];
    }

    protected function sqlHeader(): string
    {
        $driver = (string) DB::connection()->getDriverName();
        $lines = [
            '-- WemX database export',
            '-- Connection: '.config('database.default'),
            '-- Generated at: '.now()->toIso8601String(),
            '--',
        ];

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
        }

        return implode("\n", $lines);
    }

    protected function prependSqlHeader(string $sql): string
    {
        return $this->sqlHeader()."\n\n".ltrim($sql);
    }
}
