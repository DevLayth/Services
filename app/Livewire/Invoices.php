<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

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
        $this->currentPage = $page;
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
                ->subMonths($months);

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


    public function editInvoice()
{
    $invoice = $this->invoices->firstWhere('id', $this->selectedInvoiceId);

    if ($invoice) {
        $this->selectedInvoiceId = $invoice->id;
        $this->editedAmount = $invoice->amount;
        $this->editedCurrencyId = $invoice->currency_id;
        $this->editedDiscount = $invoice->discount;
        $this->editedMonths = $invoice->months;
    }
}
//continue here

    //update invoice
    public function updateInvoice()
    {
        try {
            DB::beginTransaction();
            DB::transaction(function () {
                DB::table('paid_invoices')->where('id', $this->selectedInvoiceId)->update([
                    'amount' => $this->editedAmount,
                    'currency_id' => $this->editedCurrencyId,
                    'dollar_price' => $this->editedDollarPrice,
                    'months' => $this->editedMonths,
                    'updated_at' => now(),
                ]);
            });


            DB::commit();
            $this->alert('update.Success', 'success');
            $this->resetInput();
            $this->loadInvoices();
        } catch (\Exception $e) {
            // Handle exception
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
