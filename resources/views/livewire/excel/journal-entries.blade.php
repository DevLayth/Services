@php
    $grouped = $data->groupBy('journal_entry_id');
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Journal Entries Report</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .right-align {
            text-align: right;
        }
    </style>
</head>
<body>
<h2>Journal Entries Report</h2>

<table>
    <thead>
        <tr>
            <th>Entry ID</th>
            <th>Account</th>
            <th>Currency</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
    </thead>

    <tbody>
        @foreach($grouped as $entryId => $rows)
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row->journal_entry_id }}</td>
                    <td>{{ $row->account_name ?? '' }}</td>
                    <td>{{ $row->currency_code ?? '' }}</td>
                    <td class="right-align">{{ number_format($row->debit ?? 0, 2) }}</td>
                    <td class="right-align">{{ number_format($row->credit ?? 0, 2) }}</td>
                </tr>
            @endforeach

            {{-- NOTE shown ONCE for each entry ID --}}
            <tr>
                <td colspan="5" style="background-color:#e8e8e8;">
                    {{ $rows->first()->note ?? '' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
