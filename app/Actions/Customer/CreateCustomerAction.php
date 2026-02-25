<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Services\CustomerService;

class CreateCustomerAction
{
    public function __construct(private CustomerService $customerService) {}

    /**
     * Create a new customer and flush the search cache.
     *
     * @param  array  $data  Validated customer attributes.
     */
    public function execute(array $data): Customer
    {
        $customer = Customer::create($data);

        $this->customerService->flushCache();

        return $customer;
    }
}
