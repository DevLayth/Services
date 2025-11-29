<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Subscription extends Component
{
    public $subscriptions = [];
    public $currencies = [];
    public $invoices = [];
    public $invoicesCount = 0;

    public $selectedSubscriptionId;
    public $amount = 0;
    public $currency_code = '';
    public $paymentMonths = 1;
    public $discount = 0;
    public $finalAmount = 0;
    public $paymentCurrency;
    public $currencyRate = 1;

    public $showMessage = false;
    public $message;
    public $messageType;
    public $messages = [
        'pay.success' => 'Payment has been processed successfully.',
        'pay.error'   => 'There was an error processing the payment.',
    ];

    public $accounts = [];
    public $selectedAccountId;
    public $paymentMethod;

    public function fetchAccounts()
    {
        try {
            $this->accounts = DB::table('accounts')->get();
        } catch (\Exception $e) {
            Log::error($e);
            $this->accounts = [];
        }
    }


    public function alert($key, $type = 'success')
    {
        $this->message = $this->messages[$key] ?? '';
        $this->messageType = $type;
        $this->showMessage = true;
    }

    public function mount()
    {
        $this->loadSubscriptions();
        $this->loadCurrencies();
        $this->fetchAccounts();
    }

    public function render()
    {
        return view('livewire.subscription');

    }


    public function loadSubscriptions()
    {
        try {
            $this->subscriptions = DB::table('subscriptions')
                ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
                ->join('services', 'services.id', '=', 'subscriptions.service_id')
                ->join('currencies', 'currencies.id', '=', 'subscriptions.currency_id')
                ->select(
                    'subscriptions.*',
                    'customers.name as customer_name',
                    'services.name as service_name',
                    'currencies.code as currency_code'
                )
                ->orderBy('subscriptions.start_date', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error($e);
            $this->subscriptions = [];
        }
    }

    public function loadCurrencies()
    {
        $currencies = DB::table('currencies')->get();
        try {
            $this->currencies = DB::table('currencies')->get();
            $this->currencyRate = $currencies->firstWhere('id', $this->paymentCurrency)->rate ?? 0;
        } catch (\Exception $e) {
            Log::error($e);
            $this->currencies = [];
        }
    }

    // Generate invoices for a subscription
    public function generateInvoices($subscriptionId)
    {
        $this->selectedSubscriptionId = $subscriptionId;

        try {
            $subscription = DB::table('subscriptions')
                ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
                ->join('services', 'services.id', '=', 'subscriptions.service_id')
                ->join('currencies', 'currencies.id', '=', 'subscriptions.currency_id')
                ->where('subscriptions.id', $subscriptionId)
                ->select(
                    'subscriptions.*',
                    'customers.name as customer_name',
                    'services.name as service_name',
                    'currencies.code as currency_code'
                )
                ->first();

            if (!$subscription) {
                $this->invoices = [];
                $this->invoicesCount = 0;
                return;
            }

            $this->amount = $subscription->price;
            $this->currency_code = $subscription->currency_code;
            $this->paymentCurrency = $subscription->currency_id;
            $this->paymentMonths = 1;
            $this->discount = 0;


            $nextPayment = Carbon::parse($subscription->next_payment_date);
            $end = $subscription->end_date ? Carbon::parse($subscription->end_date) : now();
            $billingDay = $nextPayment->day;

            $invoices = [];
            $current = $nextPayment->copy();

            while ($current->lte($end)) {
                $invoiceDate = Carbon::create(
                    $current->year,
                    $current->month,
                    min($billingDay, $current->daysInMonth)
                );

                if ($invoiceDate->gt($end)) break;

                $invoices[] = [
                    'subscription_id' => $subscription->id,
                    'customer_name'   => $subscription->customer_name,
                    'service_name'    => $subscription->service_name,
                    'price'           => $subscription->price,
                    'currency_code'   => $subscription->currency_code,
                    'invoice_date'    => $invoiceDate->toDateString(),
                    'invoice_month'   => $invoiceDate->format('Y-m'),
                ];

                $current->addMonth();
            }

            $this->invoices = $invoices;
            $this->invoicesCount = count($invoices);
        } catch (\Exception $e) {
            Log::error($e);
            $this->invoices = [];
            $this->invoicesCount = 0;
        }
    }

    public function updatedPaymentMonths($value)
    {
        $this->calculateTotal();
    }

    public function updatedDiscount($value)
    {
        $this->calculateTotal();
    }

    public function updatedPaymentCurrency($value)
    {
        $this->loadCurrencies();
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $discountFactor = $this->discount ? $this->discount / 100 : 0 / 100;
      $this->finalAmount = number_format(
    ($this->amount * $this->paymentMonths)
    * (1 - $discountFactor)
    * $this->currencyRate,
    2,
    '.',
    ''
);

    }

    public function pay()
    {
        $this->validate([
            'selectedSubscriptionId' => 'required|integer',
            'paymentMonths' => 'required|integer|min:1',
            'discount' => 'numeric|min:0|max:100',
            'selectedAccountId' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $subscription = DB::table('subscriptions')
                ->where('id', $this->selectedSubscriptionId)
                ->first();

            if (!$subscription) throw new \Exception("Subscription not found.");


            $oneMonthPrice = $subscription->price * $this->currencyRate;

            $nextPaymentDate = Carbon::parse($subscription->next_payment_date);
            $paidTo = $nextPaymentDate->copy()->addMonths((int) $this->paymentMonths);

            $invoiceId = DB::table('paid_invoices')->insertGetId([
                'subscription_id' => $subscription->id,
                'amount_one_month' => $oneMonthPrice,
                'months' => (int) $this->paymentMonths,
                'amount' => $this->finalAmount,
                'currency_id' =>  $this->paymentCurrency,
                'discount' => $this->discount,
                'paid_from' => $nextPaymentDate,
                'paid_to' => $paidTo,
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            $newNext = $nextPaymentDate->copy()->addMonths((int) $this->paymentMonths);
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update(['next_payment_date' => $newNext]);

            createJournalEntry(

                $this->finalAmount,
                $this->paymentCurrency,
                $this->currencyRate,
                [
                    [
                        'account_id' => $this->selectedAccountId,
                        'debit' => 0,
                        'credit' => $this->finalAmount,
                        'note' => 'Subscription Payment',
                    ],
                    [
                        'account_id' => $this->paymentMethod,
                        'debit' => $this->finalAmount,
                        'credit' => 0,
                        'note' => 'Subscription Payment',
                    ],
                ],
                $invoiceId,
                'paid_invoices',
                'Subscription Payment'
            );
            DB::commit();
            $this->loadSubscriptions();
            $this->alert('pay.success', 'success');
            $this->resetInputs();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            $this->alert('pay.error', 'danger');
        }
    }

    public function initPaymentCurrency($subscriptionId)
    {


            $this->paymentCurrency = collect($this->subscriptions)
                ->firstWhere('id', $subscriptionId)
                ->currency_id ?? null  ;
            $this->loadCurrencies();
            $this->calculateTotal();

    }


    public function resetInputs()
    {
        $this->resetValidation();
        $this->selectedSubscriptionId = null;
        $this->amount = 0;
        $this->currency_code = '';
        $this->paymentMonths = 1;
        $this->discount = 0;
        $this->finalAmount = 0;
        $this->selectedAccountId = null;
        $this->paymentMethod = null;
        $this->invoices = [];
        $this->invoicesCount = 0;
    }
}
