<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use Mpdf\Mpdf;
use PhpParser\Lexer\TokenEmulator\ReverseEmulator;

class JournalEntries extends Component
{
    public $journalEntries = [];
    public $journalEntryLines = [];
    public $journalEntriesForExport;
    public $selectedJournalEntryId;

    public $showMessage = false;
    public $message;
    public $messageType;

    public $messages = [
        'load.Error' => 'Failed to load journal entries.',
    ];

    public $filter = [
        'invoice_id'   => '',
        'currency'     => '',
        'from'         => '',
        'to'           => '',
        'amount_from'  => '',
        'amount_to'    => '',
    ];

    public $currencies = [];

    // Pagination
    public $currentPage = 1;
    public $perPage = 10;
    public $totalPages = 0;
    public $rowCount = 0;

    public function mount()
    {
        $today = Carbon::today()->format('Y-m-d');
        $this->filter['from'] = $today;
        $this->filter['to']   = $today;

        $this->loadCurrencies();
        $this->loadJournalEntries();
    }

    public function render()
    {
        return view('livewire.journal-entries');
    }

    public function resetFilters()
    {
        foreach ($this->filter as $key => $value) {
            $this->filter[$key] = '';
        }
    }

    public function getActiveFiltersCountProperty()
    {
        return collect($this->filter)->filter()->count();
    }

    public function goToPage($page)
    {
        $this->currentPage = max(1, $page);
        $this->loadJournalEntries();
    }

    public function loadJournalEntries()
    {
        try {
            $query = DB::table('journal_entries')
                ->leftJoin('currencies', 'currencies.id', '=', 'journal_entries.currency_id')
                ->select('journal_entries.*', 'currencies.code as currency_code')
                ->orderByDesc('journal_entries.id');

            // Apply filters
            if ($this->filter['invoice_id']) {
                $query->where('journal_entries.invoice_id', $this->filter['invoice_id']);
            }
            if ($this->filter['currency']) {
                $query->where('journal_entries.currency_id', $this->filter['currency']);
            }
            if ($this->filter['from']) {
                $query->whereDate('journal_entries.created_at', '>=', $this->filter['from']);
            }
            if ($this->filter['to']) {
                $query->whereDate('journal_entries.created_at', '<=', $this->filter['to']);
            }
            if ($this->filter['amount_from'] !== '') {
                $query->where('journal_entries.amount', '>=', $this->filter['amount_from']);
            }
            if ($this->filter['amount_to'] !== '') {
                $query->where('journal_entries.amount', '<=', $this->filter['amount_to']);
            }

            // Use pagination
            $this->journalEntriesForExport = $query->get()->sortBy('id')->toArray();
            $paginator = $query->paginate($this->perPage, ['*'], 'page', $this->currentPage);

            $this->journalEntries = $paginator->items();
            $this->rowCount = $paginator->total();
            $this->totalPages = ceil($this->rowCount / $this->perPage);
            $this->currentPage = $paginator->currentPage();
        } catch (\Exception $e) {
            logger()->error('Failed to load journal entries: ' . $e->getMessage());
            $this->alert('load.Error', 'danger');
            $this->journalEntries = [];
            $this->rowCount = 0;
            $this->totalPages = 0;
        }
    }

    public function loadCurrencies()
    {
        try {
            $this->currencies = DB::table('currencies')->pluck('code', 'id')->toArray();
        } catch (\Exception $e) {
            $this->currencies = [];
        }
    }

    public function loadJournalEntryLines($entryId)
    {
        $this->selectedJournalEntryId = $entryId;

        try {
            $this->journalEntryLines = DB::table('journal_entries_lines')
                ->join('accounts', 'accounts.id', '=', 'journal_entries_lines.account_id')
                ->where('journal_entries_lines.journal_entry_id', $entryId)
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

    public function setSelectedJournalEntryId($entryId)
    {
        $this->selectedJournalEntryId = $entryId;
    }



    //export functions
    public function exportPdf()
    {
        try {
            $journalEntries = collect($this->journalEntriesForExport);
            $entryIds = $journalEntries->pluck('id')->toArray();
            $lines = DB::table('journal_entries_lines')
                ->join('accounts', 'accounts.id', '=', 'journal_entries_lines.account_id')
                ->whereIn('journal_entries_lines.journal_entry_id', $entryIds)
                ->select(
                    'journal_entries_lines.journal_entry_id',
                    'journal_entries_lines.debit',
                    'journal_entries_lines.credit',
                    'accounts.name as account_name'
                )
                ->orderBy('journal_entries_lines.id')
                ->get()
                ->groupBy('journal_entry_id');

            $html = view('livewire.pdf.journal-entries', compact('journalEntries', 'lines'))->render();


            $mpdf = new Mpdf([
                'tempDir' => storage_path('app/mpdf/temp'),
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 0,
                'margin_footer' => 0,
            ]);
            $mpdf->WriteHTML($html);

            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', 'S');
            }, 'journal_entries.pdf');
        } catch (\Exception $e) {
            logger()->error('Failed to export journal entries: ' . $e->getMessage());
            $this->alert('load.Error', 'danger');
        }
    }

       public function exportExcel()
    {
         $journalEntries = collect($this->journalEntriesForExport);
            $entryIds = $journalEntries->pluck('id')->toArray();
            $lines = DB::table('journal_entries_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entries_lines.journal_entry_id')
                ->join('accounts', 'accounts.id', '=', 'journal_entries_lines.account_id')
                ->join('currencies', 'currencies.id', '=', 'journal_entries.currency_id')
                ->whereIn('journal_entries_lines.journal_entry_id', $entryIds)
                ->select(
                    'journal_entries_lines.journal_entry_id',
                    'journal_entries_lines.debit',
                    'journal_entries_lines.credit',
                    'accounts.name as account_name',
                    'currencies.code as currency_code',
                    'journal_entries.note'
                )
                ->orderBy('journal_entries_lines.journal_entry_id')
                ->get()
            ;

        $html = view('livewire.excel.journal-entries', [
            'data' => $lines->sortBy('journal_entry_id')->values()
        ])->render();

        $filename = 'journal_entry_excel_report' . now()->format('Y-m-d').'_'.time() . '.xls';
        $filepath = public_path('storage/exports/' . $filename);

        if (!file_exists(public_path('storage/exports'))) {
            mkdir(public_path('storage/exports'), 0777, true);
        }

        file_put_contents($filepath, $html);
        return response()->download($filepath)->deleteFileAfterSend(true);
    }
}
