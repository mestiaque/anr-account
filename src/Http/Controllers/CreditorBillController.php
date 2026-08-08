<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Creditor;
use ME\Accounts\Models\CreditorBill;
use ME\Accounts\Services\SequenceGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditorBillController extends Controller
{
    public function store(Request $r, Creditor $creditor)
    {
        $r->validate([
            'transaction_date' => 'nullable|date',
            'title' => 'required|max:150',
            'amount' => 'required|numeric',
            'description' => 'nullable|max:1000',
        ]);

        CreditorBill::create([
            'bill_no' => SequenceGenerator::next('creditor_bill'),
            'creditor_id' => $creditor->id,
            'title' => $r->title,
            'amount' => $r->amount,
            'description' => $r->description,
            'transaction_date' => $r->transaction_date ?: Carbon::now(),
            'created_by' => Auth::id(),
        ]);

        Session()->flash('success', 'Bill entry successfully added');
        return redirect()->back();
    }

    public function update(Request $r, CreditorBill $creditorBill)
    {
        $r->validate([
            'title' => 'required|max:150',
            'amount' => 'required|numeric',
            'description' => 'nullable|max:1000',
            'transaction_date' => 'nullable|date',
        ]);

        $creditorBill->title = $r->title;
        $creditorBill->amount = $r->amount;
        $creditorBill->description = $r->description;
        if ($r->transaction_date) {
            $creditorBill->transaction_date = $r->transaction_date;
        }
        $creditorBill->editedby_id = Auth::id();
        $creditorBill->save();

        Session()->flash('success', 'Bill entry successfully updated');
        return redirect()->back();
    }

    public function destroy(CreditorBill $creditorBill)
    {
        $creditorBill->delete();

        Session()->flash('success', 'Bill entry successfully deleted');
        return redirect()->back();
    }
}
