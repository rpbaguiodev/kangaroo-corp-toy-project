<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Services\CustomerService;

class DeleteCustomerAction
{
    public function __construct(private CustomerService $customerService) {}

    /**
     * Delete a customer and flush the search cache.
     *
     * @param  Customer  $customer  The customer to delete.
     */
    public function execute(Customer $customer): void
    {
        $customer->delete();

        $this->customerService->flushCache();
    }
}
