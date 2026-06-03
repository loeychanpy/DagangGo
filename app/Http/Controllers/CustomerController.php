<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string|max:300',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'credit_limit' => $request->credit_limit ?? 0,
        ]);

        return response()->json(['customer' => $customer], 201);
    }

    public function show(Customer $customer)
    {
        $totalTransactions = $customer->transactions()->count();
        $totalSpent        = $customer->transactions()->sum('total_price');
        $totalDebt         = $customer->transactions()
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('remaining_bill');

        return response()->json([
            'customer' => $customer,
            'stats'    => [
                'total_transactions' => $totalTransactions,
                'total_spent'        => $totalSpent,
                'total_debt'         => $totalDebt,
            ],
        ]);
    }
}
