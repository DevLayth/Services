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
        if ($this->selectedInvoiceId) {
            try {
                $invoice=DB::table('paid_invoices')->where('id', $this->selectedInvoiceId)->get()->first();
                if (!$invoice) {
                    $this->alert('delete.Error', 'error');
                    return;
                }

                $subscription=DB::table('subscriptions')->where('id', $invoice->subscription_id)->get()->first();
                if (!$subscription) {
                    $this->alert('delete.Error', 'error');
                    return;
                }
                $months = (int) $invoice->months;
                $newNextPaymentDate = \Carbon\Carbon::parse($subscription->next_payment_date)->subMonths($months);

               DB::beginTransaction();
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update(['next_payment_date' => $newNextPaymentDate]);

                createJournalEntry(
                    'Invoice Deletion - Subscription #' . $subscription->id,
                    'Reversing journal entry for deleted invoice ID ' . $this->selectedInvoiceId,
                    $this->selectedInvoiceId,
                    'paid_invoices',
                    [
                        [
                            'account_id' => 1,
                            'debit' => $invoice->amount,
                            'credit' => 0,
                        ],
                        [
                            'account_id' => 5,
                            'debit' => 0,
                            'credit' => $invoice->amount,
                        ],
                    ]
                );

                DB::table('paid_invoices')->where('id', $this->selectedInvoiceId)->delete();

                DB::commit();
                $this->alert('delete.Success', 'success');
                $this->resetInput();
                $this->loadInvoices();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->resetInput();
                $this->alert('delete.Error', 'error');
            }
        }
    }
}
