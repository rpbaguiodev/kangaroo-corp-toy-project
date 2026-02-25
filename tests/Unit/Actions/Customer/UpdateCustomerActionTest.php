<?php

namespace Tests\Unit\Actions\Customer;

use App\Actions\Customer\UpdateCustomerAction;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateCustomerActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_the_customer_with_the_given_data(): void
    {
        $customer = Customer::factory()->create(['name' => 'Old Name']);

        $service = $this->createMock(CustomerService::class);
        $service->expects($this->once())->method('flushCache');

        $action = new UpdateCustomerAction($service);

        $action->execute($customer, ['name' => 'New Name', 'status' => 'inactive']);

        $this->assertDatabaseHas('customers', [
            'id'   => $customer->id,
            'name' => 'New Name',
        ]);
    }

    #[Test]
    public function it_flushes_the_cache_after_updating(): void
    {
        $customer = Customer::factory()->create();

        $service = $this->createMock(CustomerService::class);
        $service->expects($this->once())->method('flushCache');

        $action = new UpdateCustomerAction($service);

        $action->execute($customer, ['name' => 'Updated', 'status' => 'active']);
    }

    #[Test]
    public function it_returns_the_updated_customer(): void
    {
        $customer = Customer::factory()->create(['status' => 'active']);

        $service = $this->createMock(CustomerService::class);
        $service->method('flushCache');

        $action   = new UpdateCustomerAction($service);
        $returned = $action->execute($customer, ['name' => $customer->name, 'status' => 'inactive']);

        $this->assertInstanceOf(Customer::class, $returned);
        $this->assertEquals('inactive', $returned->fresh()->status);
    }
}
