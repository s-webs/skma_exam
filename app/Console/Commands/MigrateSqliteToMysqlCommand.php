<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MigrateSqliteToMysqlCommand extends Command
{
    /**
     * Business tables in FK-safe copy order.
     *
     * @var list<string>
     */
    private const TABLES = [
        'migrations',
        'users',
        'password_reset_tokens',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'exam_types',
        'exams',
        'questions',
        'answers',
        'applicants',
        'exam_registrations',
        'exam_attempts',
        'exam_attempt_questions',
        'exam_attempt_answers',
        'exam_results',
        'exam_type_user',
        'exam_type_role',
    ];

    /**
     * Tables with an auto-increment `id` column.
     *
     * @var list<string>
     */
    private const TABLES_WITH_AUTO_INCREMENT_ID = [
        'users',
        'permissions',
        'roles',
        'exam_types',
        'exams',
        'questions',
        'answers',
        'applicants',
        'exam_registrations',
        'exam_attempts',
        'exam_attempt_questions',
        'exam_attempt_answers',
        'exam_results',
        'exam_type_user',
        'exam_type_role',
    ];

    protected $signature = 'db:migrate-sqlite-to-mysql
                            {--source=sqlite_source : SQLite connection name}
                            {--target=mysql : MySQL connection name}
                            {--dry-run : Show row counts without writing data}';

    protected $description = 'Copy business data from SQLite to MySQL preserving IDs and foreign keys';

    public function handle(): int
    {
        $sourceName = (string) $this->option('source');
        $targetName = (string) $this->option('target');
        $dryRun = (bool) $this->option('dry-run');

        $source = DB::connection($sourceName);
        $target = DB::connection($targetName);

        $this->assertConnectionDrivers($source, $target);

        $stats = [];

        if ($dryRun) {
            $this->info('Dry run: counting rows in SQLite source...');

            foreach (self::TABLES as $table) {
                if (! $this->tableExists($source, $table)) {
                    $stats[] = [$table, 'missing', '-'];

                    continue;
                }

                $count = $source->table($table)->count();
                $stats[] = [$table, (string) $count, 'skipped'];
            }

            $this->table(['Table', 'Rows in source', 'Action'], $stats);

            return self::SUCCESS;
        }

        $this->info('Preparing MySQL target tables...');
        $target->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach (array_reverse(self::TABLES) as $table) {
                if (! $this->tableExists($target, $table)) {
                    throw new RuntimeException("Target table [{$table}] does not exist. Run migrations on MySQL first.");
                }

                $target->table($table)->truncate();
            }

            foreach (self::TABLES as $table) {
                if (! $this->tableExists($source, $table)) {
                    $this->warn("Skipping missing source table [{$table}].");
                    $stats[] = [$table, '0', 'missing'];

                    continue;
                }

                $copied = $this->copyTable($source, $target, $table);
                $stats[] = [$table, (string) $copied, 'copied'];

                if (in_array($table, self::TABLES_WITH_AUTO_INCREMENT_ID, true)) {
                    $this->resetAutoIncrement($target, $table);
                }
            }
        } finally {
            $target->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->newLine();
        $this->info('Migration complete.');
        $this->table(['Table', 'Rows copied', 'Status'], $stats);

        return self::SUCCESS;
    }

    private function assertConnectionDrivers(Connection $source, Connection $target): void
    {
        if ($source->getDriverName() !== 'sqlite') {
            throw new RuntimeException('Source connection must use the sqlite driver.');
        }

        if ($target->getDriverName() !== 'mysql') {
            throw new RuntimeException('Target connection must use the mysql driver.');
        }

        $source->getPdo();
        $target->getPdo();
    }

    private function tableExists(Connection $connection, string $table): bool
    {
        return Schema::connection($connection->getName())->hasTable($table);
    }

    private function copyTable(Connection $source, Connection $target, string $table): int
    {
        $query = $source->table($table);
        $total = 0;
        $chunkSize = 500;
        $orderColumn = $this->resolveOrderColumn($source, $table);

        if ($orderColumn !== null) {
            $query->orderBy($orderColumn);
        }

        $query->chunk($chunkSize, function ($rows) use ($target, $table, &$total) {
            $payload = array_map(static fn ($row) => (array) $row, $rows->all());

            if ($payload === []) {
                return;
            }

            $target->table($table)->insert($payload);
            $total += count($payload);
        });

        $this->line("  {$table}: {$total} rows");

        return $total;
    }

    private function resolveOrderColumn(Connection $source, string $table): ?string
    {
        $columns = Schema::connection($source->getName())->getColumnListing($table);

        if (in_array('id', $columns, true)) {
            return 'id';
        }

        if (in_array('email', $columns, true)) {
            return 'email';
        }

        return $columns[0] ?? null;
    }

    private function resetAutoIncrement(Connection $target, string $table): void
    {
        $maxId = $target->table($table)->max('id');

        if ($maxId === null) {
            return;
        }

        $next = ((int) $maxId) + 1;
        $target->statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
    }
}
