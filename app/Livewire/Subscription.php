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

    public $paymentMethod = '';

    public function mount()
    {
        $this->loadSubscriptions();
        $this->loadCurrencies();

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
        try {
            $this->currencies = DB::table('currencies')->get();
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
            $this->paymentMonths = 1;
            $this->discount = 0;

            $this->calculateTotal();

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

// Called automatically when paymentMonths changes
public function updatedPaymentMonths($value)
{
    $this->calculateTotal();
}

// Called automatically when discount changes
public function updatedDiscount($value)
{
    $this->calculateTotal();
}

    public function calculateTotal()
    {
        $discountFactor = $this->discount / 100;
        $this->finalAmount = round($this->amount * $this->paymentMonths * (1 - $discountFactor), 2);
    }

    // Process payment
    public function pay()
    {
        $this->validate([
            'selectedSubscriptionId' => 'required|integer',
            'paymentMonths' => 'required|integer|min:1',
            'discount' => 'numeric|min:0|max:100',
            'paymentMethod' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $subscription = DB::table('subscriptions')
                ->where('id', $this->selectedSubscriptionId)
                ->first();

            if (!$subscription) throw new \Exception("Subscription not found.");

            $oneMonthPrice = $subscription->price;
            $discountFactor = $this->discount / 100;
            $finalAmount = $oneMonthPrice * $this->paymentMonths * (1 - $discountFactor);
            $nextPaymentDate = Carbon::parse($subscription->next_payment_date);

            // Insert paid invoice
            DB::table('paid_invoices')->insert([
                'subscription_id' => $subscription->id,
                'amount_one_month' => $oneMonthPrice,
                'amount' => $finalAmount,
                'currency_id' => $subscription->currency_id,
                'discount' => $discountFactor,
                'paid_from' => $nextPaymentDate,
                'paid_to' => $nextPaymentDate->copy()->addMonths($this->paymentMonths),
                'paid_at' => now(),
                'payment_method' => $this->paymentMethod,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update subscription next payment
            $newNext = $nextPaymentDate->copy()->addMonths($this->paymentMonths);
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update(['next_payment_date' => $newNext]);

            DB::commit();
            $this->loadSubscriptions();
            $this->resetInputs();
            //alert or notify success

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            //alert or notify error
        }
    }


    public function resetInputs()
    {
        $this->selectedSubscriptionId = null;
        $this->amount = 0;
        $this->currency_code = '';
        $this->paymentMonths = 1;
        $this->discount = 0;
        $this->finalAmount = 0;
        $this->paymentMethod = '';
        $this->invoices = [];
        $this->invoicesCount = 0;
    }
}
