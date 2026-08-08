<?php

return [
    [
        'group_title' => '',
        [
            'title'      => 'Accounts (New)',
            'icon'       => 'fa-solid fa-wallet',
            'icon_color' => 'text-primary',
            'permission' => '',
            'order'      => 11,
            'children'   => [
                [
                    'title'      => 'Accounts',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/accounts',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_accounts',
                ],
                [
                    'title'      => 'Expenses',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/expenses',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_expenses',
                ],
                [
                    'title'      => 'Expense Categories',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/expense-categories',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_expense_categories',
                ],
                [
                    'title'      => 'I.O.U',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/ious',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_ious',
                ],
                [
                    'title'      => 'Completed I.O.U',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/ious/completed',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_ious',
                ],
                [
                    'title'      => 'Deposits',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/deposits',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_deposits',
                ],
                [
                    'title'      => 'Withdrawals',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/withdrawals',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_withdrawals',
                ],
                [
                    'title'      => 'Balance Transfers',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/balance-transfers',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_balance_transfers',
                ],
                [
                    'title'      => 'Creditor Bill Payments',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/creditor-bill-payments',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_creditor_bill_payments',
                ],
                [
                    'title'      => 'Payment Methods',
                    'icon'       => 'fa-solid fa-arrow-right',
                    'route'      => '/admin/v2/payment-methods',
                    'icon_color' => 'text-warning',
                    'permission' => 'ac_payment_methods',
                ],
            ],
        ],
    ],
];
