<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));

        $customers = Customer::query()
            ->when($q, function ($query) use ($q) {
                $query->where('customer_code', 'like', "%{$q}%")
                    ->orWhere('full_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('nic', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'q'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'email'     => ['nullable', 'email', 'max:255'],
            'nic'       => ['nullable', 'string', 'max:50'],
            'address'   => ['nullable', 'string'],
            'notes'     => ['nullable', 'string'],
        ]);

        $customer = Customer::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Customer created ({$customer->customer_code})",
                'data' => $customer
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'email'     => ['nullable', 'email', 'max:255'],
            'nic'       => ['nullable', 'string', 'max:50'],
            'address'   => ['nullable', 'string'],
            'notes'     => ['nullable', 'string'],
        ]);

        $customer->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Customer updated ({$customer->customer_code})",
                'data' => $customer
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated');
    }

    public function destroy(Request $request, Customer $customer)
    {
        $customer->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer deleted');
    }
}
