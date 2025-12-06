<!DOCTYPE html>
<html>
<head>
    <title>Journal Entries</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .lines-table { margin-top: 5px; margin-bottom: 15px; }
        .lines-table th, .lines-table td { font-size: 11px; }
    </style>
</head>
<body>
    <h2>Journal Entries Report</h2>
    @foreach($journalEntries as $entry)
        <table>
            <tr>
                <th>ID</th>
                <th>Invoice ID</th>
                <th>Currency</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            <tr>
                <td>{{ $entry->id }}</td>
                <td>{{ $entry->invoice_id }}</td>
                <td>{{ $entry->currency_code }}</td>
                <td>{{ $entry->amount }}</td>
                <td>{{ \Carbon\Carbon::parse($entry->created_at)->format('Y-m-d') }}</td>
            </tr>
        </table>

        @if(isset($lines[$entry->id]))
            <table class="lines-table">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lines[$entry->id] as $line)
                        <tr>
                            <td>{{ $line->account_name }}</td>
                            <td>{{ $line->debit }}</td>
                            <td>{{ $line->credit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>
