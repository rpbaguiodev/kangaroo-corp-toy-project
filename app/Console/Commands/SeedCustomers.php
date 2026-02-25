<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class SeedCustomers extends Command
{
    protected $signature = 'customers:seed {count=1000}';

    protected $description = 'Seed N fake customers into the database';

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        if ($count <= 0) {
            $this->error('Count must be a positive number.');
            return self::FAILURE;
        }

        $this->info("Seeding {$count} customers...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $chunkSize = 500;

        foreach (array_chunk(range(1, $count), $chunkSize) as $chunk) {
            $rows = Customer::factory()
                ->count(count($chunk))
                ->make()
                ->map(fn ($customer) => [
                    ...$customer->toArray(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->toArray();

            Customer::insert($rows);

            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done! Seeded {$count} customers.");

        return self::SUCCESS;
    }
}
