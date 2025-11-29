<?php

use Illuminate\Support\Facades\DB;
if (!function_exists('createJournalEntry')) {
    function createJournalEntry($amount, $currency_id, $dollar_price, array $lines, $invoiceId, $invoiceTableName, $note)
    {
        try {
            return DB::transaction(function () use ($amount, $currency_id, $dollar_price, $note, $lines, $invoiceId, $invoiceTableName) {

                $totalDebit  = array_sum(array_column($lines, 'debit'));
                $totalCredit = array_sum(array_column($lines, 'credit'));

                if ($totalDebit != $totalCredit) {
                    throw new Exception("Journal entry is not balanced. Debit = $totalDebit, Credit = $totalCredit");
                }

                // Find existing journal entry
                $journalEntry = DB::table('journal_entries')
                    ->where('invoice_id', $invoiceId)
                    ->where('invoice_table_name', $invoiceTableName)
                    ->first();

                if ($journalEntry) {

                    // Append journal note
                    $updatedNote = $journalEntry->note;
                    if (!empty($note)) {
                        $updatedNote = trim(($journalEntry->note ?? '') . ' | ' . $note);
                    }

                    // Update journal entry
                    DB::table('journal_entries')->where('id', $journalEntry->id)->update([
                        'amount'       => $amount,
                        'currency_id'  => $currency_id,
                        'dollar_price' => $dollar_price,
                        'note'         => $updatedNote,
                        'updated_at'   => now(),
                    ]);

                    $journalEntryId = $journalEntry->id;

                    // Existing lines
                    $existingLines = DB::table('journal_entries_lines')
                        ->where('journal_entry_id', $journalEntryId)
                        ->get()
                        ->keyBy('id');

                    foreach ($lines as $line) {

                        // Update existing line
                        if (!empty($line['id']) && isset($existingLines[$line['id']])) {

                            $old = $existingLines[$line['id']];

                            // Append line note
                            $newLineNote = $old->note;
                            if (!empty($line['note'])) {
                                $newLineNote = trim(($old->note ?? '') . ' | ' . $line['note']);
                            }

                            // Update only changed fields
                            DB::table('journal_entries_lines')
                                ->where('id', $line['id'])
                                ->update([
                                    'account_id' => $line['account_id'],
                                    'debit'      => $line['debit'],
                                    'credit'     => $line['credit'],
                                    'note'       => $newLineNote,
                                    'updated_at' => now(),
                                ]);

                        } else {
                            // Insert new line
                            DB::table('journal_entries_lines')->insert([
                                'journal_entry_id' => $journalEntryId,
                                'account_id'       => $line['account_id'],
                                'debit'            => $line['debit'],
                                'credit'           => $line['credit'],
                                'note'             => $line['note'] ?? null,
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);
                        }
                    }

                } else {
                    // Create new journal entry
                    $journalEntryId = DB::table('journal_entries')->insertGetId([
                        'invoice_id'         => $invoiceId,
                        'invoice_table_name' => $invoiceTableName,
                        'amount'             => $amount,
                        'currency_id'        => $currency_id,
                        'dollar_price'       => $dollar_price,
                        'note'               => $note,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);

                    // Insert lines
                    foreach ($lines as $line) {
                        DB::table('journal_entries_lines')->insert([
                            'journal_entry_id' => $journalEntryId,
                            'account_id'       => $line['account_id'],
                            'debit'            => $line['debit'],
                            'credit'           => $line['credit'],
                            'note'             => $line['note'] ?? null,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }

                return $journalEntryId;
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }
}



//update journal entry
//don't delete lines, just update them
if (!function_exists('updateJournalEntryForDeleteInvoice')) {
    function updateJournalEntryForDeleteInvoice($invoiceId, $tableName,$note)
    {
            try {
                return DB::transaction(function () use ($invoiceId, $tableName,$note) {

                    $journalEntry = DB::table('journal_entries')
                        ->where('invoice_id', $invoiceId)
                        ->where('invoice_table_name', $tableName)
                        ->first();
               DB::table('journal_entries')
                    ->where('id', $journalEntry->id)
                    ->update([
                        'amount'      => 0,
                        'note'        => $journalEntry->note . ' | ' . $note,
                        'updated_at'  => now(),
                    ]);

                DB::table('journal_entries_lines')->where('journal_entry_id', $journalEntry->id)->update([
                        'debit'      => 0,
                        'credit'     => 0,
                        'note'       => 'Delete Invoice #' . $invoiceId. ' from '. $tableName,
                        'updated_at' => now(),
                    ]);

            });
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

