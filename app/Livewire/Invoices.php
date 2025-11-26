<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;

use Livewire\Component;

class Invoices extends Component
{
    public $invoices = [];
    public $selectedInvoiceId;
    public $showMessage = false;
    public $message;
    public $messageType;
    public $messages = [
        'delete.Success' => 'Invoice deleted successfully.',
        'delete.Error' => 'Failed to delete invoice.',
    ];

    public function render()
    {
        return view('livewire.invoices');
    }

    public function mount()
    {
        $this->loadInvoices();
    }
    public function alert($key, $type = 'success')
    {
        $this->message = $this->messages[$key] ?? '';
        $this->messageType = $type;
        $this->showMessage = true;
    }
    public function resetInput()
    {
        $this->selectedInvoiceId = null;
        $this->resetValidation();
    }
    public function loadInvoices()
    {
        try {
            $this->invoices = DB::table('paid_invoices')
                ->join('subscriptions', 'subscriptions.id', '=', 'paid_invoices.subscription_id')
                ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
                ->join('services', 'services.id', '=', 'subscriptions.service_id')
                ->join('currencies', 'currencies.id', '=', 'paid_invoices.currency_id')
                ->select(
                    'paid_invoices.*',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'services.name as service_name',
                    'currencies.code as currency_code'
                )
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->invoices = [];
        }
    }

    public function deleteInvoice()
{
    if (!$this->selectedInvoiceId) {
        return;
    }

    try {
        $invoice = DB::table('paid_invoices')
            ->where('id', $this->selectedInvoiceId)
            ->first();

        if (!$invoice) {
            $this->alert('delete.Error', 'danger');
            return;
        }

        $subscription = DB::table('subscriptions')
            ->where('id', $invoice->subscription_id)
            ->first();

        if (!$subscription) {
            $this->alert('delete.Error', 'danger');
            return;
        }

        $months = (int) $invoice->months;
        $newNextPaymentDate = \Carbon\Carbon::parse($subscription->next_payment_date)
            ->subMonths($months)
            ->format('Y-m-d');

        DB::beginTransaction();

        DB::table('subscriptions')
            ->where('id', $subscription->id)
            ->update(['next_payment_date' => $newNextPaymentDate]);

        $entryLines = DB::table('journal_entries_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entries_lines.journal_entry_id')
            ->select('journal_entries_lines.account_id', 'journal_entries_lines.debit', 'journal_entries_lines.credit')
            ->get();

        if ($entryLines->count() < 2) {
            throw new \Exception("Journal entry has insufficient lines");
        }

        $debitLine  = $entryLines->firstWhere('debit', '>', 0);
        $creditLine = $entryLines->firstWhere('credit', '>', 0);

        if (!$debitLine || !$creditLine) {
            throw new \Exception("Could not determine debit/credit accounts for reversal");
        }

        createJournalEntry(
            'Invoice Deletion - Subscription #' . $subscription->id,
            'Reversing journal entry for deleted invoice ID ' . $this->selectedInvoiceId,

            [
                [
                    'account_id' => $creditLine->account_id,
                    'debit' => $invoice->amount,
                    'credit' => 0,
                ],

                [
                    'account_id' => $debitLine->account_id,
                    'debit' => 0,
                    'credit' => $invoice->amount,
                ],
            ],$this->selectedInvoiceId,
            'paid_invoices'
        );

        DB::table('paid_invoices')
            ->where('id', $this->selectedInvoiceId)
            ->delete();

        DB::commit();

        $this->alert('delete.Success', 'success');
        $this->resetInput();
        $this->loadInvoices();

    } catch (\Exception $e) {
        DB::rollBack();
        logger()->error("Invoice deletion failed: ".$e->getMessage());
        $this->alert('delete.Error', 'danger');
        $this->resetInput();
    }
}

}
