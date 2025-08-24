<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $sale->reference_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            margin: 20px;
        }

        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-info {
            text-align: left;
        }

        .customer-info, .payment-info {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 8px;
            text-align: right;
        }

        th:first-child, td:first-child {
            text-align: left;
        }

        .signature {
            margin-top: 60px;
            text-align: center;
        }

        .signature .line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto 10px;
        }

        .title {
            font-size: 24px;
            color: #333;
        }

    </style>
</head>
<body>

    <div class="header">
        <div class="company-info">
            <img src="{{ public_path('assets/img/logo.png') }}" alt="Logo" height="80"><br>
            <strong>{{ config('app.name', 'Brick Factory') }}</strong><br>
            {{ config('company.address', '123 Factory Road') }}<br>
            {{ config('company.city', 'Kigali, Rwanda') }}<br>
            Tel: {{ config('company.phone', '+250 123 456 789') }}
        </div>
    </div>

    <div class="title">RECEIPT</div>
    <p><strong>Receipt No:</strong> {{ $sale->reference_number }}</p>
    <p><strong>Date:</strong> {{ $sale->sale_date->format('d M Y H:i') }}</p>
    <p><strong>Sales Person:</strong> {{ $sale->employee->name }}</p>

    <div class="customer-info">
        <strong>Customer Information</strong><br>
        Name: {{ $sale->customer->name }}<br>
        Phone: {{ $sale->customer->phone ?? 'N/A' }}<br>
        Email: {{ $sale->customer->email ?? 'N/A' }}
    </div>

    <div class="payment-info">
        <strong>Payment Information</strong><br>
        Method: {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}<br>
        Total: {{ number_format($sale->total_price, 2) }} RWF<br>
        @if($sale->payment_method === 'credit')
            Balance: <span style="color: {{ $sale->balance > 0 ? 'red' : 'green' }}">{{ number_format($sale->balance, 2) }} RWF</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total (RWF)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $sale->stockType->name }} Bricks</td>
                <td>{{ number_format($sale->quantity) }}</td>
                <td>{{ number_format($sale->unit_price, 2) }}</td>
                <td>{{ number_format($sale->total_price, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th>{{ number_format($sale->total_price, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="signature">
        <div class="line"></div>
        Authorized Signature
    </div>

    <div class="footer">
        <p>Thank you for your business!</p>
    </div>

</body>
</html>
