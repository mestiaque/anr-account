<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Creditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditorController extends Controller
{
    public function index(Request $r)
    {
        $creditors = Creditor::where('status', '<>', 'temp')
            ->when($r->search, fn ($q) => $q->where('name', 'LIKE', '%' . $r->search . '%')
                ->orWhere('company_name', 'LIKE', '%' . $r->search . '%')
                ->orWhere('mobile', 'LIKE', '%' . $r->search . '%'))
            ->when($r->status, fn ($q) => $q->where('status', $r->status))
            ->orderBy('name')
            ->paginate(25)
            ->appends(['search' => $r->search, 'status' => $r->status]);

        return view('erp-accounts::accounts.creditors', compact('creditors'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|max:100',
            'company_name' => 'nullable|max:150',
            'mobile' => 'nullable|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|max:1000',
        ]);

        Creditor::create([
            'name' => $r->name,
            'company_name' => $r->company_name,
            'mobile' => $r->mobile,
            'email' => $r->email,
            'address' => $r->address,
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        Session()->flash('success', 'Creditor successfully added');
        return redirect()->back();
    }

    public function update(Request $r, Creditor $creditor)
    {
        $r->validate([
            'name' => 'required|max:100',
            'company_name' => 'nullable|max:150',
            'mobile' => 'nullable|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|max:1000',
        ]);

        $creditor->name = $r->name;
        $creditor->company_name = $r->company_name;
        $creditor->mobile = $r->mobile;
        $creditor->email = $r->email;
        $creditor->address = $r->address;
        $creditor->status = $r->status ? 'active' : 'inactive';
        $creditor->editedby_id = Auth::id();
        $creditor->save();

        Session()->flash('success', 'Creditor successfully updated');
        return redirect()->back();
    }

    public function destroy(Creditor $creditor)
    {
        $creditor->delete();

        Session()->flash('success', 'Creditor successfully deleted');
        return redirect()->back();
    }
}
