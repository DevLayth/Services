<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Invoices extends Component
{
     use WithPagination;
    public $invoices;
    public $currencies  = [];
    public $selectedInvoiceId;
    public $showMessage = false;
    public $message;
    public $messageType;
    public $messages = [
        'delete.Success' => 'Invoice deleted successfully.',
        'delete.Error' => 'Failed to delete invoice.',
    ];

    public $totals = [
        'totalAmountUSD',
        'totalAmountIQD',
    ];

public $filter = [
    'customer'     => '',
    'service'      => '',
    'from'         => '',
    'to'           => '',
    'currency'     => '',
    'amount_from'  => '',
    'amount_to'    => '',
];
public function getActiveFiltersCountProperty()
{
    return collect($this->filter)->filter()->count();
}

    public function resetFilters()
    {
        $this->filter = [
            'customer' => '',
            'service' => '',
            'from' => '',
            'to' => '',
            'currency' => '',
            'amount_from' => '',
            'amount_to' => '',
        ];
    }
    public function render()
    {
        return view('livewire.invoices');
    }

    public function mount()
    {
        $this->filter['from'] = Carbon::today()->format('Y-m-d');
        $this->filter['to'] = Carbon::today()->format('Y-m-d');
        $this->loadInvoices();
        $this->loadCurrencies();
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
            $invoicesForExport = DB::table('paid_invoices')
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
                )->orderBy('paid_invoices.id', 'desc');

            if ($this->filter['customer']) {
                $invoicesForExport->where('customers.name', 'like', '%' . $this->filter['customer'] . '%');
            }
            if ($this->filter['service']) {
                $invoicesForExport->where('services.name', 'like', '%' . $this->filter['service'] . '%');
            }
            if ($this->filter['from']) {
                $invoicesForExport->whereDate('paid_invoices.paid_at', '>=', $this->filter['from']);
            }
            if ($this->filter['to']) {
                $invoicesForExport->whereDate('paid_invoices.paid_at', '<=', $this->filter['to']);
            }
            if ($this->filter['currency']) {
                $invoicesForExport->where('currencies.code', $this->filter['currency']);
            }
            if ($this->filter['amount_from']) {
                $invoicesForExport ->where('paid_invoices.amount', '>=', $this->filter['amount_from']);
            }
            if ($this->filter['amount_to']) {
                $invoicesForExport->where('paid_invoices.amount', '<=', $this->filter['amount_to']);
            }

            $this->invoices = $invoicesForExport->get();

            $this->totals['totalAmountUSD'] = $this->invoices->where('currency_code', 'USD')->sum('amount');
            $this->totals['totalAmountIQD'] = $this->invoices->where('currency_code', 'IQD')->sum('amount');
        } catch (\Exception $e) {
            $this->invoices = collect([]);
        }
    }

    public function loadCurrencies()
    {
        try {
            $this->currencies = DB::table('currencies')->get()->toArray();
        } catch (\Exception $e) {
            $this->currencies = [];
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
