<?php

namespace Tests\Unit\Actions\Customer;

use App\Actions\Customer\DeleteCustomerAction;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteCustomerActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_the_customer(): void
    {
        $customer = Customer::factory()->create();

        $service = $this->createMock(CustomerService::class);
        $service->expects($this->once())->method('flushCache');

        $action = new DeleteCustomerAction($service);
        $action->execute($customer);

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    #[Test]
    public function it_flushes_the_cache_after_deleting(): void
    {
        $customer = Customer::factory()->create();

        $service = $this->createMock(CustomerService::class);
        $service->expects($this->once())->method('flushCache');

        $action = new DeleteCustomerAction($service);
        $action->execute($customer);
    }
}
