<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Currencies extends Component
{
    public $currencyId;
    public $name;
    public $code;
    public $symbol;
    public $rate;
    public $currencies = [];

    public $editMode = false;


    public $messages = [
        'add.success'    => 'The new currency has been added successfully.',
        'add.error'      => 'There was an error adding the new currency.',
        'update.success' => 'The currency has been updated successfully.',
        'update.error'   => 'There was an error updating the currency.',
        'delete.success' => 'The currency has been deleted successfully.',
        'delete.error'   => 'There was an error deleting the currency.',
    ];

    public $message;
    public $messageType;
    public $showMessage = false;



    public function render()
    {
        return view('livewire.currencies');
    }

    public function mount()
    {
        $this->fetchCurrencies();
    }

    public function addCurrency()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'symbol' => 'required|string|max:10',
            'rate' => 'required|numeric',
        ]);

        try {

        DB::table('currencies')->insert([
            'name' => $this->name,
            'code' => $this->code,
            'symbol' => $this->symbol,
            'rate' => $this->rate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->resetInput();
        $this->fetchCurrencies();
        $this->alert('success', 'add');
        } catch (\Exception $e) {
            $this->alert('error', 'add');
        }

    }

    public function resetInput()
    {
        $this->name = '';
        $this->code = '';
        $this->symbol = '';
        $this->rate = 0;
        $this->currencyId = null;
        $this->editMode = false;

        $this->resetValidation();
    }

    public function fetchCurrencies()
    {
        try {
            $this->currencies = DB::table('currencies')->get()->toArray();
        } catch (\Exception $e) {
            $this->currencies = [];
        }
    }

    public function editCurrency($id)
    {
        try {
             $currency = DB::table('currencies')->where('id', $id)->first();
        if ($currency) {
            $this->currencyId = $currency->id;
            $this->name = $currency->name;
            $this->code = $currency->code;
            $this->symbol = $currency->symbol;
            $this->rate = $currency->rate;
            $this->editMode = true;
        }
        } catch (\Exception $e) {
            $this->alert('error', 'update');
        }

    }


    public function updateCurrency()
    {
        $this->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10',
            'symbol' => 'required|string|max:10',
        ]);

        try {
        DB::table('currencies')->where('id', $this->currencyId)->update([
            'name' => $this->name,
            'code' => $this->code,
            'symbol' => $this->symbol,
            'rate' => $this->rate,
            'updated_at' => now(),
        ]);

        $this->resetInput();
        $this->editMode = false;
        $this->fetchCurrencies();
        $this->alert('success', 'update');
        } catch (\Exception $e) {
            $this->alert('error', 'update');
        }
    }


    public function deleteCurrency()
    {
        try {
            DB::table('currencies')->where('id', $this->currencyId)->delete();
            $this->fetchCurrencies();
            $this->alert('success', 'delete');
        } catch (\Exception $e) {
            $this->alert('error', 'delete');
        }
    }


    public function alert($type, $action)
    {
        $this->messageType = $type;
        $this->message = $this->messages["{$action}.{$type}"];
        $this->showMessage = true;
    }


    public function setCurrencyId($id , $name)
    {
        $this->currencyId = $id;
        $this->name = $name;
    }

}
