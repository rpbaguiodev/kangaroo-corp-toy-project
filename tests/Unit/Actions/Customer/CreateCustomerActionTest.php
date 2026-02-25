<?php

namespace Tests\Unit\Actions\Customer;

use App\Actions\Customer\CreateCustomerAction;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCustomerActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_customer_with_the_given_data(): void
    {
        $service = $this->createMock(CustomerService::class);
        $service->expects($this->once())->method('flushCache');

        $action = new CreateCustomerAction($service);

        $data = [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'phone'   => '123456789',
            'company' => 'Acme Corp',
            'status'  => 'active',
        ];

        $customer = $action->execute($data);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', ['email' => 'john@example.com']);
    }

    #[Test]
    public function it_flushes_the_cache_after_creating(): void
    {
        $service = $this->createMock(CustomerService::class);
        $service->expects($this->once())->method('flushCache');

        $action = new CreateCustomerAction($service);

        $action->execute([
            'name'   => 'Jane Doe',
            'status' => 'active',
        ]);
    }
}
