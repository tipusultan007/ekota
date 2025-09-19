<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Account Statement - {{ $member->name }}</title>
    <style>

        body {
            font-family: 'Nikosh', sans-serif;
            font-size: 12px;
        }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        {{-- এখন বাংলা নাম সঠিকভাবে দেখাবে --}}
        <h1>একতা সমবায় সমিতি</h1>
        <p>ধানমন্ডি, ঢাকা-১২০৫</p>
        <p><strong>হিসাব বিবরণী</strong></p>
    </div>


    <hr>

    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none;"><strong>Member Name:</strong> {{ $member->name }}</td>
            <td style="border: none;" class="text-end"><strong>Member ID:</strong> {{ $member->id }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Address:</strong> {{ $member->address }}</td>
            <td style="border: none;" class="text-end"><strong>Statement Period:</strong> {{ $startDate->format('d/m/Y') }} to {{ $endDate->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th class="text-end">Deposit / Credit</th>
            <th class="text-end">Withdrawal / Debit</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                <td>{{ $transaction->description }}</td>
                <td class="text-end">{{ number_format($transaction->deposit, 2) }}</td>
                <td class="text-end">{{ number_format($transaction->withdrawal, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center;">No transactions found in this period.</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr style="background-color: #f2f2f2;">
            <td colspan="2" class="text-end"><strong>Total:</strong></td>
            <td class="text-end"><strong>{{ number_format($transactions->sum('deposit'), 2) }}</strong></td>
            <td class="text-end"><strong>{{ number_format($transactions->sum('withdrawal'), 2) }}</strong></td>
        </tr>
        </tfoot>
    </table>

    <div class="footer" style="margin-top: 50px;">
        <p>This is a computer-generated statement and does not require a signature.</p>
    </div>
</div>
</body>
</html>
