<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Services\CustomerService;

class UpdateCustomerAction
{
    public function __construct(private CustomerService $customerService) {}

    /**
     * Update an existing customer and flush the search cache.
     *
     * @param  Customer  $customer  The customer to update.
     * @param  array     $data      Validated attributes to apply.
     */
    public function execute(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        $this->customerService->flushCache();

        return $customer;
    }
}
