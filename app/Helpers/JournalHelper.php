<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('createJournalEntry')) {
       function createJournalEntry($reference, $description, array $lines, $invoiceId , $invoiceTableName )
    {

        try {
        return DB::transaction(function () use ($reference, $description, $lines, $invoiceId, $invoiceTableName) {
            $totalDebit  = array_sum(array_column($lines, 'debit'));
            $totalCredit = array_sum(array_column($lines, 'credit'));

            if ($totalDebit != $totalCredit) {
                throw new Exception("Journal entry is not balanced. Debit = $totalDebit, Credit = $totalCredit");
            }

            $journalEntryId = DB::table('journal_entries')->insertGetId([
                'reference'   => $reference,
                'description' => $description,
                'invoice_id' => $invoiceId,
                'invoice_table_name' => $invoiceTableName,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            foreach ($lines as $line) {
                DB::table('journal_entries_lines')->insert([
                    'journal_entry_id' => $journalEntryId,
                    'account_id'       => $line['account_id'],
                    'debit'            => $line['debit'],
                    'credit'           => $line['credit'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        } );
    } catch (\Exception $e) {

        throw $e;
    }
    }

}


//update journal entry
//don't delete lines, just update them
if (!function_exists('updateJournalEntry')) {
    function updateJournalEntry($journalEntryId, $reference, $description, array $lines)
    {
        try {
            return DB::transaction(function () use ($journalEntryId, $reference, $description, $lines) {
                $totalDebit  = array_sum(array_column($lines, 'debit'));
                $totalCredit = array_sum(array_column($lines, 'credit'));

                if ($totalDebit != $totalCredit) {
                    throw new Exception("Journal entry is not balanced. Debit = $totalDebit, Credit = $totalCredit");
                }

                DB::table('journal_entries')
                    ->where('id', $journalEntryId)
                    ->update([
                        'reference'   => $reference,
                        'description' => $description,
                        'updated_at'  => now(),
                    ]);

                DB::table('journal_entries_lines')->where('journal_entry_id', $journalEntryId)->delete();
            
                foreach ($lines as $line) {
                    DB::table('journal_entries_lines')->insert([
                        'journal_entry_id' => $journalEntryId,
                        'account_id'       => $line['account_id'],
                        'debit'            => $line['debit'],
                        'credit'           => $line['credit'],
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

