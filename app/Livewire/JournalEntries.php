<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;

use Livewire\Component;

class JournalEntries extends Component
{
    public $journalEntries = [];

    public $selectedJournalEntryId;


public function setSelectedJournalEntry($id)
{
    $this->selectedJournalEntryId = $id;
}

    public $journalEntryLines = [];
    public $showMessage = false;
    public $message;
    public $messageType;
    public $messages = [
        'load.Error' => 'Failed to load journal entries.',
    ];
    public function render()
    {
        return view('livewire.journal-entries');
    }

    public function mount()
    {
        $this->loadCurrencies();
        $this->loadJournalEntries();
    }


    public function loadJournalEntries()
    {
        try {
            $this->journalEntries = DB::table('journal_entries')
                ->select(
                    'journal_entries.*',

                )
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->journalEntries = [];
        }
    }

    public $currencies = [];
    public function loadCurrencies()
    {
        try {
            $currencies = DB::table('currencies')->get();
            $this->currencies = $currencies->pluck('code', 'id')->toArray();
        } catch (\Exception $e) {
            $this->currencies = [];
        }
    }


    public function loadJournalEntryLines($selectedJournalEntryId)
    {
        $this->selectedJournalEntryId = $selectedJournalEntryId;
        try {
            $this->journalEntryLines = DB::table('journal_entries_lines')
                ->join('accounts', 'accounts.id', '=', 'journal_entries_lines.account_id')
                ->where('journal_entries_lines.journal_entry_id', $selectedJournalEntryId)
                ->select(
                    'journal_entries_lines.id',
                    'journal_entries_lines.debit',
                    'journal_entries_lines.credit',
                    'accounts.id as account_id',
                    'accounts.name as account_name'
                )
                ->orderBy('journal_entries_lines.id')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->journalEntryLines = [];
        }
    }

    public function resetInput()
    {
        $this->selectedJournalEntryId = null;
        $this->journalEntryLines = [];
        $this->resetValidation();
    }

    public function alert($key, $type = 'success')
    {
        $this->message = $this->messages[$key] ?? '';
        $this->messageType = $type;
        $this->showMessage = true;
    }
}
