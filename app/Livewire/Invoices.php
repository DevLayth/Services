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
    public $invoicesForExport;
    public $currencies  = [];
    public $selectedInvoiceId;
    public $showMessage = false;
    public $message;
    public $messageType;
    public $messages = [
        'delete.Success' => 'Invoice deleted successfully.',
        'delete.Error' => 'Failed to delete invoice.',
        'update.Success' => 'Invoice updated successfully.',
        'update.Error' => 'Failed to update invoice.',
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

    public $currentPage = 1;
    public $perPage = 10;
    public $totalInvoices = 0;
    public $totalPages = 0;
    public $rowCount = 0;
    public function goToPage($page)
    {
        $this->currentPage = max(1, $page);
        $this->loadInvoices();
    }

    public $sortField = 'paid_invoices.id';
    public $sortDirection = 'desc';
    public $readyToLoad = false;
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
                    'currencies.code as currency_code',

                    DB::raw(
                        "CASE
                            WHEN paid_invoices.currency_id = 1 THEN paid_invoices.amount
                            WHEN paid_invoices.currency_id = 2 THEN paid_invoices.amount / 1420
                            ELSE 0
                         END AS amount_usd"
                    ),
                    DB::raw(
                        "CASE
                            WHEN paid_invoices.currency_id = 2 THEN paid_invoices.amount
                            WHEN paid_invoices.currency_id = 1 THEN paid_invoices.amount * 1460
                            ELSE 0
                         END AS amount_iqd"
                    ),

                )->orderBy('paid_invoices.id', 'desc');

            if ($this->filter['customer']) {
                $invoicesForExport->where('customers.name', 'like', '%' . $this->filter['customer'] . '%');
            }
            if ($this->filter['service']) {
                $invoicesForExport->where('services.name', 'like', '%' . $this->filter['service'] . '%');
            }
            if ($this->filter['from']) {
                $invoicesForExport->whereDate('paid_invoices.updated_at', '>=', $this->filter['from']);
            }
            if ($this->filter['to']) {
                $invoicesForExport->whereDate('paid_invoices.updated_at', '<=', $this->filter['to']);
            }
            if ($this->filter['currency']) {
                $invoicesForExport->where('currencies.code', $this->filter['currency']);
            }
            if ($this->filter['amount_from']) {
                $invoicesForExport->where('paid_invoices.amount', '>=', $this->filter['amount_from']);
            }
            if ($this->filter['amount_to']) {
                $invoicesForExport->where('paid_invoices.amount', '<=', $this->filter['amount_to']);
            }

            $this->invoicesForExport = $invoicesForExport->get();
            $this->rowCount = $this->invoicesForExport->count();
            $this->totalPages = ceil($this->rowCount / $this->perPage);
            $this->currentPage = min($this->currentPage, $this->totalPages) ?: 1;
            $this->invoices = $this->invoicesForExport->forPage($this->currentPage, $this->perPage);
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
                ->subMonthNoOverflow($months);

            DB::beginTransaction();

            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update(['next_payment_date' => $newNextPaymentDate]);

            updateJournalEntryForDeleteInvoice(
                $this->selectedInvoiceId,
                'paid_invoices',
                'Reversal for deleted Invoice #' . $this->selectedInvoiceId
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
            logger()->error("Invoice deletion failed: " . $e->getMessage());
            $this->alert('delete.Error', 'danger');
            $this->resetInput();
        }
    }


    public $editedAmount;
    public $editedCurrencyId;
    public $editedMonths;
    public $editedDiscount;
    public $oneMonthPrice;
    public $editedDollarPrice;
    public $currencyRate;

    public function updatedEditedMonths()
    {
        $this->calculateEditedAmount();
    }
    public function updatedEditedDiscount()
    {
        $this->calculateEditedAmount();
    }

    public function updatedEditedCurrencyId()
{
    try {
        $this->currencyRate = DB::table('currencies')->where('id', $this->editedCurrencyId)->value('rate') ?? 1;
    } catch (\Exception $e) {
        $this->currencyRate = 1;
    }
    $this->calculateEditedAmount();
}
public function calculateEditedAmount()
{
    $discountFactor = (100 - $this->editedDiscount) / 100;

    // Base amount without currency
    $baseAmount = $this->oneMonthPrice * $this->editedMonths * $discountFactor;

    // Apply currency rate if available
    $this->editedAmount = $this->currencyRate ? $baseAmount * $this->currencyRate : $baseAmount;

    // Calculate dollar price
    if ($this->editedCurrencyId == 1) { // assume 1 = USD
        $this->editedDollarPrice = $this->editedAmount;
    } elseif ($this->editedCurrencyId == 2) { // assume 2 = IQD
        $this->editedDollarPrice = $this->editedAmount / 1420; // or use actual rate
    } else {
        $this->editedDollarPrice = $this->currencyRate ? $this->editedAmount / $this->currencyRate : $this->editedAmount;
    }
}



    public function editInvoice()
    {
        $invoice = DB::table('paid_invoices')->where('id', $this->selectedInvoiceId)->first();

        if ($invoice) {
            $this->selectedInvoiceId = $invoice->id;
            $this->editedMonths = $invoice->months;
            $this->oneMonthPrice = $invoice->amount / $invoice->months;
            $this->editedAmount = $this->oneMonthPrice * $this->editedMonths;
            $this->editedCurrencyId = $invoice->currency_id;
            $this->editedDiscount = $invoice->discount;

            $this->currencyRate = DB::table('currencies')->where('id', $this->editedCurrencyId)->value('rate');
            $this->editedDollarPrice = $this->editedAmount / ($this->currencyRate ?: 1);
        } else {
            $this->alert('update.Error', 'danger');
        }
    }



    //update invoice
    public function updateInvoice()
    {
        if (!$this->selectedInvoiceId) {
            $this->alert('update.Error', 'danger');
            return;
        }

        try {
            DB::beginTransaction();

            $invoice = DB::table('paid_invoices')->where('id', $this->selectedInvoiceId)->first();
            if (!$invoice) {
                throw new \Exception('Invoice not found.');
            }
            $oldMonths = $invoice->months;

            $discountFactor = (100 - $this->editedDiscount) / 100;
            $this->editedAmount = $this->oneMonthPrice * $this->editedMonths * $discountFactor;
            $this->currencyRate = DB::table('currencies')->where('id', $this->editedCurrencyId)->value('rate') ?: 1;
            $this->editedDollarPrice = $this->editedAmount / $this->currencyRate;

            DB::table('paid_invoices')->where('id', $this->selectedInvoiceId)->update([
                'amount_one_month' => $this->oneMonthPrice* $this->currencyRate,
                'amount' => $this->editedAmount * $this->currencyRate,
                'currency_id' => $this->editedCurrencyId,
                'months' => $this->editedMonths,
                'updated_at' => now(),
            ]);

            $journalEntry = DB::table('journal_entries')
                ->where('invoice_id', $this->selectedInvoiceId)
                ->where('invoice_table_name', 'paid_invoices')
                ->first();

            if ($journalEntry) {
                $newNote = ($journalEntry->note ?? '') . '|Updated entry for Invoice #' . $this->selectedInvoiceId;

                DB::table('journal_entries')->where('id', $journalEntry->id)->update([
                    'amount' => $this->editedAmount*$this->currencyRate,
                    'currency_id' => $this->editedCurrencyId,
                    'updated_at' => now(),
                    'note' => $newNote,
                ]);

                $lines = DB::table('journal_entries_lines')
                    ->where('journal_entry_id', $journalEntry->id)
                    ->get();

                $totalDebit = $lines->sum('debit');
                $totalCredit = $lines->sum('credit');

                if ($totalDebit > 0 && $totalCredit > 0) {
                    foreach ($lines as $line) {
                        $debitProportion = $line->debit / $totalDebit;
                        $creditProportion = $line->credit / $totalCredit;

                        DB::table('journal_entries_lines')->where('id', $line->id)->update([
                            'debit' => $this->editedAmount * $debitProportion * $this->currencyRate,
                            'credit' => $this->editedAmount * $creditProportion * $this->currencyRate,
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            $subscription = DB::table('subscriptions')->where('id', $invoice->subscription_id)->first();
            if ($subscription) {
                $newNextPaymentDate = Carbon::parse($subscription->next_payment_date)
                    ->addMonthNoOverflow($this->editedMonths - $oldMonths);

                DB::table('subscriptions')->where('id', $subscription->id)->update([
                    'next_payment_date' => $newNextPaymentDate,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $this->alert('update.Success', 'success');
            $this->resetInput();
            $this->loadInvoices();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('update.Error', 'danger');
        }
    }


    // public function ExportPdf()
    // {
    //     try {
    //         $data = $this->invoicesForExport ?? collect([]);
    //         $filename = 'invoices_pdf_report_' . date('Y-m-d_H-i-s') . '.pdf';

    //         $html = view('reports.pdf_reports_accounting.invoices_pdf_report', [
    //             'data' => $data
    //         ])->render();
    //         $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

    //         $mpdf = new Mpdf([
    //             'mode' => 'utf-8',
    //             'format' => 'A4-L',
    //             'default_font' => 'dejavusans',
    //             'autoScriptToLang' => true,
    //             'autoLangToFont' => true,
    //             'margin_top' => 15,
    //             'margin_bottom' => 10,
    //             'margin_left' => 10,
    //             'margin_right' => 10,
    //         ]);

    //         // Write HTML body
    //         $mpdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

    //         return $mpdf->Output($filename, 'D');
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Failed to generate invoices PDF',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
