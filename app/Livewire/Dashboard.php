<?php

namespace App\Livewire;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $customers=[
        'totalCustomers',
        'todayCustomers',
        'todayPercent',
    ];

    public $services = [
        'activeServices',
        'todayServices',
        'todayPercent',
    ];

    public $subscriptions = [
        'totalSubscriptions',
        'todaySubscriptions',
        'todayPercent',
    ];

    public $invoices = [
        'totalInvoices',
        'todayInvoices',
        'todayPercent',
    ];


    public function render()
    {
        return view('livewire.dashboard');
    }

    public function mount()
    {
        $this->customers['totalCustomers'] = DB::table('customers')->count();
        $this->customers['todayCustomers'] = DB::table('customers')->whereDate('created_at', today())->count();
        $this->customers['todayPercent'] = $this->customers['totalCustomers'] > 0 ? ($this->customers['todayCustomers'] / $this->customers['totalCustomers']) * 100 : 0;

        $this->services['activeServices'] = DB::table('services')->count();
        $this->services['todayServices'] = DB::table('services')->whereDate('created_at', today())->count();
        $this->services['todayPercent'] = $this->services['activeServices'] > 0 ? ($this->services['todayServices'] / $this->services['activeServices']) * 100 : 0;

        $this->subscriptions['totalSubscriptions'] = DB::table('subscriptions')->count();
        $this->subscriptions['todaySubscriptions'] = DB::table('subscriptions')->whereDate('created_at', today())->count();
        $this->subscriptions['todayPercent'] = $this->subscriptions['totalSubscriptions'] > 0 ? ($this->subscriptions['todaySubscriptions'] / $this->subscriptions['totalSubscriptions']) * 100 : 0;

        $this->invoices['totalInvoices'] = DB::table('paid_invoices')->count();
        $this->invoices['todayInvoices'] = DB::table('paid_invoices')->whereDate('created_at', today())->count();
        $this->invoices['todayPercent'] = $this->invoices['totalInvoices'] > 0 ? ($this->invoices['todayInvoices'] / $this->invoices['totalInvoices']) * 100 : 0;
    }

}
