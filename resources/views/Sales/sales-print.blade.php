<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Printable Sales Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        h2, .summary { margin-top: 0; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <h2>Sales Report</h2>

    <button class="no-print" onclick="window.print()">🖨️ Print / Save as PDF</button>

    <p><strong>Date Exported:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>

    <div class="summary">
        <p><strong>Total Quantity:</strong> {{ $summary['total_quantity'] }} units</p>
        <p><strong>Total Sales:</strong> ${{ number_format($summary['total_sales'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Employee</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->customer->name }}</td>
                    <td>{{ $sale->stockType->name }}</td>
                    <td>{{ $sale->quantity }}</td>
                    <td>${{ number_format($sale->unit_price, 2) }}</td>
                    <td>${{ number_format($sale->total_price, 2) }}</td>
                    <td>{{ ucfirst($sale->payment_method) }}</td>
                    <td>{{ $sale->employee->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>