<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $page   = $request->input('page', 1);

        $cacheKey  = 'customers.search.' . md5($search . '|page=' . $page);
        $customers = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search) {
            $query = Customer::query();

            if ($search) {
                $query->whereFullText(['name', 'email', 'phone', 'company'], $search);
            }

            return $query->latest()->paginate(10)->withQueryString();
        });

        return view('customers.index', compact('customers', 'search'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        Customer::create($validated);
        Cache::flush();

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($validated);
        Cache::flush();

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        Cache::flush();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
