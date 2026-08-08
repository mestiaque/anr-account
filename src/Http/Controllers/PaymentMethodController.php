<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function index(Request $r)
    {
        $paymentMethods = PaymentMethod::where('status', '<>', 'temp')
            ->when($r->search, fn ($q) => $q->where('name', 'LIKE', '%' . $r->search . '%'))
            ->when($r->status, fn ($q) => $q->where('status', $r->status))
            ->paginate(25)
            ->appends(['search' => $r->search, 'status' => $r->status]);

        return view(adminTheme() . 'accounts.paymentMethods', compact('paymentMethods'));
    }

    public function store(Request $r)
    {
        $r->validate(['name' => 'required|max:100', 'description' => 'nullable|max:1000']);

        PaymentMethod::create([
            'name' => $r->name,
            'description' => $r->description,
            'status' => 'active',
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Payment method successfully added');
        return redirect()->back();
    }

    public function update(Request $r, PaymentMethod $paymentMethod)
    {
        $r->validate(['name' => 'required|max:100', 'description' => 'nullable|max:1000']);

        $paymentMethod->name = $r->name;
        $paymentMethod->description = $r->description;
        $paymentMethod->status = $r->status ? 'active' : 'inactive';
        $paymentMethod->editedby_id = Auth::id();
        $paymentMethod->save();

        Session()->flash('success', 'Payment method successfully updated');
        return redirect()->back();
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        Session()->flash('success', 'Payment method successfully deleted');
        return redirect()->back();
    }
}
