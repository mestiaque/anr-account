<?php

namespace ME\Accounts\Services;

use Illuminate\Support\Facades\DB;

class SequenceGenerator
{
    /**
     * Atomically reserve the next 10-digit zero-padded number for the given key
     * (e.g. 'expense', 'iou', 'deposit'). Independent of any table's auto-increment id.
     */
    public static function next(string $key): string
    {
        return DB::transaction(function () use ($key) {
            $sequence = DB::table('ac_sequences')->where('key', $key)->lockForUpdate()->first();

            if (!$sequence) {
                DB::table('ac_sequences')->insert([
                    'key' => $key,
                    'next_value' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $value = 1;
            } else {
                $value = $sequence->next_value;
                DB::table('ac_sequences')->where('key', $key)->update([
                    'next_value' => $value + 1,
                    'updated_at' => now(),
                ]);
            }

            return str_pad((string) $value, 10, '0', STR_PAD_LEFT);
        });
    }
}
