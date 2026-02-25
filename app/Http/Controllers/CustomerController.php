<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CreateCustomerAction;
use App\Actions\Customer\DeleteCustomerAction;
use App\Actions\Customer\UpdateCustomerAction;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(private CustomerService $customerService) {}

    /**
     * Display a paginated list of customers, with optional FULLTEXT search.
     */
    public function index(Request $request)
    {
        $search    = $request->input('search');
        $page      = $request->input('page', 1);
        $customers = $this->customerService->search($search, $page);

        return view('customers.index', compact('customers', 'search'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Validate and store a newly created customer.
     */
    public function store(StoreCustomerRequest $request, CreateCustomerAction $action)
    {
        $action->execute($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show the form for editing an existing customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Validate and apply updates to an existing customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer, UpdateCustomerAction $action)
    {
        $action->execute($customer, $request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Delete a customer record.
     */
    public function destroy(Customer $customer, DeleteCustomerAction $action)
    {
        $action->execute($customer);

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
