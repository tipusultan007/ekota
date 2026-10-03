<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.savings_collection_withdraw_report') }} - {{ $officer->name }}</title>
    <style>
        @page { size: portrait; margin: 0; }
        body { font-family: 'Inter', 'Roboto', 'SolaimanLipi', sans-serif; color: #333; line-height: 1.2; margin: 0; padding: 0; background: #f0f2f5; }
        
        .page-container { 
            background: white; 
            width: 210mm;
            min-height: 297mm; 
            padding: 10mm; 
            margin: 10mm auto; 
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header { text-align: center; margin-bottom: 15px; border-bottom: 1px solid #05c173; padding-bottom: 5px; }
        .header h1 { margin: 0; color: #05c173; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #666; font-size: 11px; }
        
        .report-meta { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 11px; }
        .report-meta div { flex: 1; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; table-layout: auto; }
        th, td { border: 1px solid #ccc; padding: 4px 8px; text-align: left; font-size: 11px; }
        th { background-color: #f1f5f9; font-weight: 700; color: #334155; }
        .text-right { text-align: right; }
        .text-success { color: #059669; font-weight: 600; }
        .text-danger { color: #dc2626; font-weight: 600; }
        
        .subtotal-row { background-color: #f8fafc; font-weight: 700; }
        
        .page-info { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); font-size: 10px; color: #94a3b8; }

        @media print {
            .no-print { display: none; }
            body { background: none; }
            .page-container { 
                margin: 0; 
                padding: 10mm;
                box-shadow: none; 
                width: 210mm;
                min-height: 297mm;
                height: 297mm;
                position: relative;
                page-break-after: always;
            }
            .page-container:last-child { page-break-after: auto; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000; background: white; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        <button onclick="window.print()" style="padding: 6px 12px; background: #05c173; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Print</button>
        <button onclick="window.close()" style="padding: 6px 12px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px;">Close</button>
    </div>

    @php 
        $grandTotalDeposit = 0;
        $grandTotalWithdraw = 0;
        $rowLimit = 35;
        $chunks = $collections->chunk($rowLimit);
        $totalChunks = count($chunks);
    @endphp

    @foreach($chunks as $pageIndex => $chunk)
        <div class="page-container">
            <div class="header">
                <h1>পদ্মা শ্রমজীবী সমবায় সমিতি লিঃ</h1>
                <p>{{ __('messages.address_placeholder') }}</p>
                <p>{{ __('messages.phone') }}: +880 1234 567890</p>
            </div>

            <div class="report-meta">
                <div>
                    <strong>{{ __('messages.collector') }}:</strong> {{ $officer->name }}<br>
                    <strong>{{ __('messages.report_type') }}:</strong> {{ __('messages.savings_collection_withdraw_report') }}
                </div>
                <div class="text-right">
                    <strong>{{ __('messages.period') }}:</strong> {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}<br>
                    <strong>{{ __('messages.print_date') }}:</strong> {{ now()->format('d/m/Y h:i A') }}
                </div>
            </div>

            <div style="flex-grow: 1;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th style="width: 80px;">{{ __('messages.date') }}</th>
                            <th>{{ __('messages.member') }}</th>
                            <th style="width: 80px;">{{ __('messages.account_no') }}</th>
                            <th class="text-right" style="width: 80px;">{{ __('messages.deposit') }}</th>
                            <th class="text-right" style="width: 80px;">{{ __('messages.withdraw') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $pageDepositTotal = 0; 
                            $pageWithdrawTotal = 0;
                        @endphp
                        @foreach($chunk as $i => $item)
                            @php 
                                $pageDepositTotal += $item->amount;
                                $pageWithdrawTotal += $item->withdraw_amount;
                                $grandTotalDeposit += $item->amount;
                                $grandTotalWithdraw += $item->withdraw_amount;
                                $serial = ($pageIndex * $rowLimit) + ($loop->iteration);
                            @endphp
                            <tr>
                                <td style="text-align: center;">{{ $serial }}</td>
                                <td>{{ $item->collection_date->format('d/m/Y') }}</td>
                                <td style="font-weight: 600;">{{ $item->member->name }}</td>
                                <td>{{ $item->savingsAccount->account_no }}</td>
                                <td class="text-right text-success">{{ number_format($item->amount, 0) }}</td>
                                <td class="text-right text-danger">{{ number_format($item->withdraw_amount, 0) }}</td>
                            </tr>
                        @endforeach
                        
                        <tr class="subtotal-row">
                            <td colspan="4" class="text-right">{{ __('messages.total') }} ({{ __('messages.page') }} {{ $pageIndex + 1 }})</td>
                            <td class="text-right text-success">{{ number_format($pageDepositTotal, 0) }}</td>
                            <td class="text-right text-danger">{{ number_format($pageWithdrawTotal, 0) }}</td>
                        </tr>

                        @if($pageIndex + 1 == $totalChunks)
                        @php $grandNet = $grandTotalDeposit - $grandTotalWithdraw; @endphp
                        <tr class="subtotal-row" style="border-top: 2px solid #333;">
                            <td colspan="4" class="text-right"><strong>{{ __('messages.grand_total') }}</strong></td>
                            <td class="text-right text-success"><strong>{{ number_format($grandTotalDeposit, 0) }}</strong></td>
                            <td class="text-right text-danger"><strong>{{ number_format($grandTotalWithdraw, 0) }}</strong></td>
                        </tr>
                        <tr class="subtotal-row" style="background: #e2e8f0;">
                            <td colspan="4" class="text-right"><strong>{{ __('messages.net_amount') }}</strong></td>
                            <td colspan="2" class="text-right"><strong> {{ number_format($grandNet, 0) }}</strong></td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="page-info">
                {{ __('messages.page') }} {{ $pageIndex + 1 }} / {{ $totalChunks }}
            </div>
        </div>
    @endforeach
</body>
</html>
