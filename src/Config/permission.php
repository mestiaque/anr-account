<?php

return [
    'ACCOUNTS (NEW)' => [
        'ac_accounts' => [
            'label' => 'Accounts',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'all' => 'All'],
        ],
        'ac_expenses' => [
            'label' => 'Expenses',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'audit' => 'Audit', 'all' => 'All'],
        ],
        'ac_expense_categories' => [
            'label' => 'Expense Categories',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'all' => 'All'],
        ],
        'ac_ious' => [
            'label' => 'I.O.U',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'all' => 'All'],
        ],
        'ac_deposits' => [
            'label' => 'Deposits',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'all' => 'All'],
        ],
        'ac_withdrawals' => [
            'label' => 'Withdrawals',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'view' => 'View', 'delete' => 'Delete', 'all' => 'All'],
        ],
        'ac_balance_transfers' => [
            'label' => 'Balance Transfers',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'all' => 'All'],
        ],
        'ac_creditor_bill_payments' => [
            'label' => 'Creditor Bill Payments',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'all' => 'All'],
        ],
        'ac_creditors' => [
            'label' => 'Creditors',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete', 'all' => 'All'],
        ],
        'ac_payment_methods' => [
            'label' => 'Payment Methods',
            'permissions' => ['list' => 'List', 'add' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete', 'all' => 'All'],
        ],
        'ac_accounts_statement' => [
            'label' => 'Accounts Statement',
            'permissions' => ['list' => 'List', 'all' => 'All'],
        ],
    ],
];
