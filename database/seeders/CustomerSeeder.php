<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the customers table by importing the mock SQL data file directly
     * into MySQL. Using a native shell import avoids PHP memory limits that
     * would be hit when loading a large SQL file via DB::unprepared().
     */
    public function run(): void
    {
        $sqlFile = base_path('mock-db-fake-data.sql');

        if (! file_exists($sqlFile)) {
            $this->command->error("SQL file not found: {$sqlFile}");
            $this->command->line('Please ensure mock-db-fake-data.sql is in the project root.');
            return;
        }

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
}
