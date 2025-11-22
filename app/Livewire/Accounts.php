<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;

use Livewire\Component;

class Accounts extends Component
{
    public $accounts = [];

    public $accountName;
    public $accountType;

    public $selectedAccountId;

    public $messages = [
        'add.success'    => 'The new account has been added successfully.',
        'add.error'      => 'There was an error adding the new account.',
    ];
    public $message;
    public $messageType;
    public $showMessage = false;


    public function alert($key, $type = 'success')
    {
        $this->message = $this->messages[$key] ?? '';
        $this->messageType = $type;
        $this->showMessage = true;
    }
    public function mount()
    {
        $this->loadAccounts();
    }

    public function resetInput()
    {
        $this->accountName = '';
        $this->accountType = '';
        $this->selectedAccountId = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.accounts');
    }

    public function loadAccounts()
    {
        try {
            $this->accounts = DB::table('accounts')
                ->select(
                    'accounts.*',
                )
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->accounts = [];
        }
    }

    public function addAccount()
    {
        $this->validate([
            'accountName' => 'required|string|max:255',
            'accountType' => 'required|string|max:255',
        ]);

        try {

            DB::table('accounts')->insert([
                'name' => $this->accountName,
                'account_type' => $this->accountType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->alert('add.success', 'success');
            $this->loadAccounts();
            $this->resetInput();
        } catch (\Exception $e) {
            $this->alert('add.error', 'error');
        }
    }

    public function deleteAccount()
    {
        if ($this->selectedAccountId) {
            try {
                $account = DB::table('accounts')->where('id', $this->selectedAccountId)->get()->first();
                if (!$account) {
                    $this->alert('delete.Error', 'error');
                    return;
                }

                DB::table('accounts')->where('id', $this->selectedAccountId)->delete();

                $this->alert('delete.Success', 'success');
                $this->resetInput();
                $this->loadAccounts();
            } catch (\Exception $e) {
                $this->alert('delete.Error', 'error');
            }
        }
    }
}
