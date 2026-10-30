<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends Controller
{
    public function show(Customer $customer)
    {
        $customer->load([
            'leads',
            'opportunities',
            'quotations.lines',
            'erpOrders',
        ]);

        return view('customer.show', compact('customer'));
    }
}
