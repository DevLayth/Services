<?php

namespace App\Livewire;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public $customers=[
        'totalCustomers',
        'todayCustomers',
    ];

    public $services = [
        'activeServices',
        'todayServices',
    ];


    public function render()
    {
        return view('livewire.dashboard');
    }

    public function mount()
    {
        $this->customers['totalCustomers'] = DB::table('customers')->count();
        $this->customers['todayCustomers'] = DB::table('customers')->whereDate('created_at', today())->count();
        $this->services['activeServices'] = DB::table('services')->count();
        $this->services['todayServices'] = DB::table('services')->whereDate('created_at', today())->count();
    }
}
