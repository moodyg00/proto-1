<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $report->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        h1 { margin-bottom: 4px; font-size: 24px; }
        p { margin: 0 0 8px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #d1d5db; text-align: left; }
        th { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; }
        .summary { margin-top: 18px; }
        .summary td { border: 0; padding: 4px 0; }
        .amount { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $report->name }}</p>
    <p>Period: {{ $periodLabel }}</p>
    <p>Generated: {{ $generatedAt->format('M j, Y g:i A') }}</p>

    <table class="summary">
        @foreach ($summary as $metric)
            <tr>
                <td>{{ $metric['label'] }}</td>
                <td class="amount">${{ number_format((float) $metric['value'], 2) }}</td>
            </tr>
        @endforeach
    </table>

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Party</th>
                <th>Date</th>
                <th>Status</th>
                <th>Details</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['reference'] }}</td>
                    <td>{{ $row['party'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td>{{ $row['meta'] }}</td>
                    <td class="amount">${{ number_format((float) $row['amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No data matched the saved filters for this report.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>