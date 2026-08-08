<?php

namespace ME\Accounts\Http\Controllers;

use ME\Accounts\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    public function index(Request $r)
    {
        if ($r->action && $r->checkid) {
            $categories = ExpenseCategory::whereIn('id', $r->checkid)->get();
            foreach ($categories as $category) {
                if ($r->action == 1) {
                    $category->status = 'active';
                    $category->save();
                } elseif ($r->action == 2) {
                    $category->status = 'inactive';
                    $category->save();
                } elseif ($r->action == 5) {
                    $category->delete();
                }
            }
            Session()->flash('success', 'Action Successfully Completed!');
            return redirect()->back();
        }

        $categories = ExpenseCategory::where('status', '<>', 'temp')
            ->when($r->search, fn ($q) => $q->where('name', 'LIKE', '%' . $r->search . '%'))
            ->when($r->status, fn ($q) => $q->where('status', $r->status))
            ->orderBy('name')
            ->paginate(25)
            ->appends(['search' => $r->search, 'status' => $r->status]);

        return view('erp-accounts::expenses.expensesTypes', compact('categories'));
    }

    public function store(Request $r)
    {
        $r->validate(['name' => 'required|max:100', 'description' => 'nullable|max:1000']);

        ExpenseCategory::create([
            'name' => $r->name,
            'description' => $r->description,
            'status' => 'active',
            'addedby_id' => Auth::id(),
        ]);

        Session()->flash('success', 'Expense category successfully added');
        return redirect()->back();
    }

    public function update(Request $r, ExpenseCategory $expenseCategory)
    {
        $r->validate(['name' => 'required|max:100', 'description' => 'nullable|max:1000']);

        $expenseCategory->name = $r->name;
        $expenseCategory->description = $r->description;
        $expenseCategory->status = $r->status ? 'active' : 'inactive';
        $expenseCategory->editedby_id = Auth::id();
        $expenseCategory->save();

        Session()->flash('success', 'Expense category successfully updated');
        return redirect()->back();
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        Session()->flash('success', 'Expense category successfully deleted');
        return redirect()->back();
    }
}
