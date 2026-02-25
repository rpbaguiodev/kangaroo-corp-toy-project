<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CustomerService();
    }

    #[Test]
    public function it_returns_all_customers_when_no_search_term_is_given(): void
    {
        Customer::factory(5)->create();

        $result = $this->service->search(null, 1);

        $this->assertCount(5, $result->items());
    }

    #[Test]
    public function it_caches_search_results(): void
    {
        Customer::factory(3)->create();

        Cache::spy();

        $this->service->search(null, 1);

        Cache::shouldHaveReceived('remember')->once();
    }

    #[Test]
    public function it_returns_paginated_results(): void
    {
        Customer::factory(15)->create();

        $result = $this->service->search(null, 1);

        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(15, $result->total());
    }

    #[Test]
    public function it_flushes_the_cache(): void
    {
        Cache::spy();

        $this->service->flushCache();

        Cache::shouldHaveReceived('flush')->once();
    }
}
