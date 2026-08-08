<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\Account;
use ME\Accounts\Models\Creditor;
use ME\Accounts\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CreditorController extends Controller
{
    public function index(Request $r)
    {
        if ($r->action && $r->checkid) {
            $creditors = Creditor::whereIn('id', $r->checkid)->get();
            foreach ($creditors as $creditor) {
                if ($r->action == 1) {
                    $creditor->status = 'active';
                    $creditor->save();
                } elseif ($r->action == 2) {
                    $creditor->status = 'inactive';
                    $creditor->save();
                } elseif ($r->action == 5) {
                    $creditor->deleted_by = Auth::id();
                    $creditor->save();
                    $creditor->delete();
                }
            }
            Session()->flash('success', 'Action Successfully Completed!');
            return redirect()->back();
        }

        $base = Creditor::query()
            ->when($r->search, fn ($q) => $q->where('name', 'LIKE', '%' . $r->search . '%')
                ->orWhere('company_name', 'LIKE', '%' . $r->search . '%')
                ->orWhere('mobile', 'LIKE', '%' . $r->search . '%')
                ->orWhere('email', 'LIKE', '%' . $r->search . '%'))
            ->when($r->startDate, fn ($q) => $q->whereDate('created_at', '>=', $r->startDate))
            ->when($r->endDate, fn ($q) => $q->whereDate('created_at', '<=', $r->endDate));

        $total = (object) [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'deleted' => (clone $base)->onlyTrashed()->count(),
        ];

        if ($r->view == 'deleted') {
            $creditors = (clone $base)->onlyTrashed()->latest()->paginate(12);
            return view('erp-accounts::accounts.creditors', compact('creditors', 'total'));
        }

        $creditors = $base->when($r->status, fn ($q) => $q->where('status', $r->status))
            ->latest()
            ->paginate(25)
            ->appends($r->all());

        return view('erp-accounts::accounts.creditors', compact('creditors', 'total'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|max:100',
            'code' => 'nullable|max:50',
            'company_name' => 'nullable|max:150',
            'email_mobile' => 'required|max:100',
            'address' => 'nullable|max:1000',
        ]);

        $isEmail = filter_var($r->email_mobile, FILTER_VALIDATE_EMAIL);

        Creditor::create([
            'name' => $r->name,
            'code' => $r->code,
            'company_name' => $r->company_name,
            'email' => $isEmail ? $r->email_mobile : null,
            'mobile' => $isEmail ? null : $r->email_mobile,
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
            'code' => 'nullable|max:50',
            'company_name' => 'nullable|max:150',
            'mobile' => 'nullable|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|max:1000',
        ]);

        $creditor->name = $r->name;
        $creditor->code = $r->code;
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
        $creditor->deleted_by = Auth::id();
        $creditor->save();
        $creditor->delete();

        Session()->flash('success', 'Creditor successfully deleted');
        return redirect()->back();
    }

    public function restore($id)
    {
        Creditor::onlyTrashed()->findOrFail($id)->restore();

        Session()->flash('success', 'Creditor successfully restored');
        return redirect()->back();
    }

    public function show(Request $r, Creditor $creditor)
    {
        $bills = $creditor->bills()
            ->when($r->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $r->startDate))
            ->when($r->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $r->endDate))
            ->when($r->search, fn ($q) => $q->where('title', 'LIKE', '%' . $r->search . '%'))
            ->get()
            ->map(fn ($bill) => (object) [
                'id' => $bill->id,
                'type' => 'bill',
                'date' => $bill->transaction_date,
                'title' => $bill->bill_no . ' - ' . $bill->title,
                'note' => $bill->description,
                'credit' => (float) $bill->amount,
                'debit' => 0,
            ]);

        $payments = $creditor->billPayments()
            ->when($r->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $r->startDate))
            ->when($r->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $r->endDate))
            ->when($r->search, fn ($q) => $q->where('payment_no', 'LIKE', '%' . $r->search . '%'))
            ->get()
            ->map(fn ($payment) => (object) [
                'id' => $payment->id,
                'type' => 'payment',
                'date' => $payment->transaction_date,
                'title' => $payment->payment_no,
                'note' => $payment->description,
                'credit' => 0,
                'debit' => (float) $payment->amount,
            ]);

        $ledger = $bills->concat($payments)->sortBy('date')->values();

        $balance = 0;
        $ledger = $ledger->map(function ($item) use (&$balance) {
            $balance += $item->credit - $item->debit;
            $item->balance = $balance;
            return $item;
        })->sortByDesc('date')->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $ledgerEntries = new LengthAwarePaginator(
            $ledger->slice(($page - 1) * $perPage, $perPage)->values(),
            $ledger->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        $totalPaid = $creditor->billPayments()->sum('amount');
        $totalPurchases = $creditor->bills()->sum('amount');

        $accountMethods = Account::where('status', 'active')->where('owner', Auth::id())->orderBy('name')->get();
        $paymentMethods = PaymentMethod::where('status', 'active')->orderBy('name')->get();

        return view('erp-accounts::accounts.creditorProfile', compact('creditor', 'ledgerEntries', 'totalPaid', 'totalPurchases', 'accountMethods', 'paymentMethods'));
    }
}
