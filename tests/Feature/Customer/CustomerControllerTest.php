<?php

namespace Tests\Feature\Customer;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    #[Test]
    public function it_displays_the_customer_list(): void
    {
        $customers = Customer::factory(3)->create();

        $paginator = new LengthAwarePaginator($customers, 3, 10);

        $this->mock(CustomerService::class)
            ->shouldReceive('search')
            ->once()
            ->andReturn($paginator);

        $this->get(route('customers.index'))
            ->assertOk()
            ->assertViewIs('customers.index');
    }

    #[Test]
    public function it_passes_the_search_term_to_the_service(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->mock(CustomerService::class)
            ->shouldReceive('search')
            ->with('john', 1)
            ->once()
            ->andReturn($paginator);

        $this->get(route('customers.index', ['search' => 'john']))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    #[Test]
    public function it_displays_the_create_form(): void
    {
        $this->get(route('customers.create'))
            ->assertOk()
            ->assertViewIs('customers.create');
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    #[Test]
    public function it_stores_a_new_customer_and_redirects(): void
    {
        $data = [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'phone'   => '123456789',
            'company' => 'Acme Corp',
            'status'  => 'active',
        ];

        $this->post(route('customers.store'), $data)
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('customers', ['email' => 'john@example.com']);
    }

    #[Test]
    public function it_fails_validation_when_name_is_missing_on_store(): void
    {
        $this->post(route('customers.store'), ['status' => 'active'])
            ->assertSessionHasErrors('name');
    }

    #[Test]
    public function it_fails_validation_when_status_is_invalid_on_store(): void
    {
        $this->post(route('customers.store'), ['name' => 'John', 'status' => 'unknown'])
            ->assertSessionHasErrors('status');
    }

    // -------------------------------------------------------------------------
    // Edit
    // -------------------------------------------------------------------------

    #[Test]
    public function it_displays_the_edit_form_for_an_existing_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('customers.edit', $customer))
            ->assertOk()
            ->assertViewIs('customers.edit')
            ->assertViewHas('customer', $customer);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    #[Test]
    public function it_updates_a_customer_and_redirects(): void
    {
        $customer = Customer::factory()->create(['name' => 'Old Name']);

        $this->put(route('customers.update', $customer), [
            'name'   => 'New Name',
            'status' => 'inactive',
        ])->assertRedirect(route('customers.index'))
          ->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'id'   => $customer->id,
            'name' => 'New Name',
        ]);
    }

    #[Test]
    public function it_fails_validation_when_name_is_missing_on_update(): void
    {
        $customer = Customer::factory()->create();

        $this->put(route('customers.update', $customer), ['status' => 'active'])
            ->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    #[Test]
    public function it_deletes_a_customer_and_redirects(): void
    {
        $customer = Customer::factory()->create();

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
