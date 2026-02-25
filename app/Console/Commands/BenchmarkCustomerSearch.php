<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BenchmarkCustomerSearch extends Command
{
    protected $signature = 'benchmark:customer-search {--term=john : Search term to use} {--iterations=50 : Number of times to run each query}';

    protected $description = 'Benchmark LIKE vs FULLTEXT search on the customers table';

    public function handle(): void
    {
        $term = $this->option('term');
        $iterations = (int) $this->option('iterations');

        $this->info("Search term: \"{$term}\" | Iterations: {$iterations}");
        $this->newLine();

        // --- LIKE benchmark ---
        $likeTimes = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start = hrtime(true);
            Customer::query()
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%")
                      ->orWhere('phone', 'like', "%{$term}%")
                      ->orWhere('company', 'like', "%{$term}%");
                })
                ->get();
            $likeTimes[] = (hrtime(true) - $start) / 1_000_000; // ms
        }

        // --- FULLTEXT benchmark ---
        $fulltextTimes = [];
        $fulltextAvailable = $this->isFulltextAvailable();

        if ($fulltextAvailable) {
            for ($i = 0; $i < $iterations; $i++) {
                $start = hrtime(true);
                Customer::query()
                    ->whereFullText(['name', 'email', 'phone', 'company'], $term)
                    ->get();
                $fulltextTimes[] = (hrtime(true) - $start) / 1_000_000; // ms
            }
        }

        // --- Results ---
        $totalCustomers = Customer::count();
        $this->info("Total customers in DB: {$totalCustomers}");
        $this->newLine();

        $likeAvg  = array_sum($likeTimes) / count($likeTimes);
        $likeMin  = min($likeTimes);
        $likeMax  = max($likeTimes);

        $this->info('--- LIKE Search (old) ---');
        $this->table(
            ['Metric', 'Time (ms)'],
            [
                ['Average', number_format($likeAvg, 3)],
                ['Min',     number_format($likeMin, 3)],
                ['Max',     number_format($likeMax, 3)],
            ]
        );

        if ($fulltextAvailable) {
            $ftAvg = array_sum($fulltextTimes) / count($fulltextTimes);
            $ftMin = min($fulltextTimes);
            $ftMax = max($fulltextTimes);

            $this->newLine();
            $this->info('--- FULLTEXT Search (new) ---');
            $this->table(
                ['Metric', 'Time (ms)'],
                [
                    ['Average', number_format($ftAvg, 3)],
                    ['Min',     number_format($ftMin, 3)],
                    ['Max',     number_format($ftMax, 3)],
                ]
            );

            $this->newLine();
            $improvement = (($likeAvg - $ftAvg) / $likeAvg) * 100;
            if ($improvement > 0) {
                $this->info(sprintf('FULLTEXT is %.1f%% faster on average.', $improvement));
            } else {
                $this->warn(sprintf('LIKE is %.1f%% faster on average (FULLTEXT overhead may dominate at small data sizes).', abs($improvement)));
            }
        } else {
            $this->newLine();
            $this->warn('FULLTEXT index not found — run "sail artisan migrate" first to benchmark FULLTEXT.');
        }
    }

    private function isFulltextAvailable(): bool
    {
        try {
            Customer::query()
                ->whereFullText(['name', 'email', 'phone', 'company'], 'test')
                ->limit(1)
                ->get();
            return true;
        } catch (\Exception) {
            return false;
        }
    }
}