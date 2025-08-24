<!-- resources/views/emails/sales/receipt.blade.php -->
@component('mail::message')
# {{ config('app.name') }} - Sale Receipt

**Receipt #{{ $sale->reference_number }}**  
**Date:** {{ $sale->sale_date->format('d M Y') }}  
**Customer:** {{ $sale->customer->name }}  

@if($customMessage)
{{ $customMessage }}
@endif

@component('mail::panel')
## Sale Details
- **Product:** {{ $sale->stockType->name }} Bricks  
- **Quantity:** {{ number_format($sale->quantity) }}  
- **Unit Price:** {{ number_format($sale->unit_price, 2) }} RWF  
- **Total:** {{ number_format($sale->total_price, 2) }} RWF  
- **Payment Method:** {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}  
@endcomponent

@component('mail::button', ['url' => route('sales.receipt', $sale)])
View Full Receipt
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent