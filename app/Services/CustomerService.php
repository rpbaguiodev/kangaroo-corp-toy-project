<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CustomerService
{
    /**
     * Search customers by a given term using FULLTEXT index with caching.
     *
     * Results are cached for 5 minutes per unique combination of search term
     * and page number. Returns all customers ordered by latest if no term is given.
     */
    public function search(?string $term, int $page): LengthAwarePaginator
    {
        $cacheKey = 'customers.search.' . md5($term . '|page=' . $page);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($term) {
            $query = Customer::query();

            if ($term) {
                $query->whereFullText(['name', 'email', 'phone', 'company'], $term);
            }

            return $query->latest()->paginate(10)->withQueryString();
        });
    }

    /**
     * Flush all cached customer search results.
     *
     * Should be called whenever a customer record is created, updated, or deleted
     * to prevent stale data from being served.
     */
    public function flushCache(): void
    {
        Cache::flush();
    }
}
