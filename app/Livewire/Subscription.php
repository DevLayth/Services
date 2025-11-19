<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Subscription extends Component
{
    public $subscriptions = [];
    public function render()
    {
        return view('livewire.subscription');
    }
    public function mount()
    {
        $this->fetchSubscriptions();
    }
    private function fetchSubscriptions()
    {
        try {
       $this->subscriptions = DB::table('subscriptions')
    ->join('customers', 'customers.id', '=', 'subscriptions.customer_id')
    ->join('services', 'services.id', '=', 'subscriptions.service_id')
    ->select(
        'subscriptions.id',
        'subscriptions.start_date',
        'subscriptions.end_date',
        'customers.name as customer_name',
        'services.name as service_name'
    )
    ->orderBy('subscriptions.start_date', 'desc')
    ->get();

        } catch (\Exception $e) {
            $this->subscriptions = [];
        }
    }
}
