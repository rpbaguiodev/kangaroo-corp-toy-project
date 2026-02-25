<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Fallback record count when the SQL file is not available.
     * Enough records to meaningfully demonstrate search performance.
     */
    const FALLBACK_COUNT = 10000;

    /**
     * Seed the customers table. Imports mock-db-fake-data.sql when available
     * for a full ~1.1 million record dataset. Falls back to generating records
     * via CustomerFactory when the SQL file is not present (e.g. on cloud deployments).
     */
    public function run(): void
    {
        $sqlFile = base_path('mock-db-fake-data.sql');

        if (file_exists($sqlFile)) {
            $this->importFromSql($sqlFile);
        } else {
            $this->command->warn('mock-db-fake-data.sql not found. Falling back to factory seed (' . self::FALLBACK_COUNT . ' records).');
            $this->seedFromFactory();
        }
    }

    /**
     * Import customers directly from the SQL file via shell command,
     * bypassing PHP memory limits for large file handling.
     */
    private function importFromSql(string $sqlFile): void
    {
        $this->command->info('Truncating customers table...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('customers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = sprintf(
            'mysql -h %s -P %s -u %s -p%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        $this->command->info('Importing mock-db-fake-data.sql — this may take a while...');

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->command->error('Import failed. Check your database credentials and that the mysql client is available.');
            return;
        }

        $count = DB::table('customers')->count();
        $this->command->info("Import complete. {$count} customer records loaded.");
    }

    /**
     * Generate fake customer records using CustomerFactory.
     * Used as a fallback when the SQL file is not available.
     */
    private function seedFromFactory(): void
    {
        $this->command->info('Seeding ' . self::FALLBACK_COUNT . ' customers via factory...');

        Customer::factory(self::FALLBACK_COUNT)->create();

        $this->command->info('Done. ' . self::FALLBACK_COUNT . ' customer records created.');
    }
}
