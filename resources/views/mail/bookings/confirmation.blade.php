<x-mail::message>
# Thank you for your booking

Hi {{ $booking->customer->name }},

Thanks for booking with {{ config('app.name') }}. We've received your hire request and our team will be in touch to confirm the details.

<x-mail::panel>
**Booking reference:** {{ $booking->reference }}

**Collection date:** {{ $booking->collection_date->format('d M Y') }}

**Return date:** {{ $booking->return_date->format('d M Y') }}
</x-mail::panel>

## What you booked

<x-mail::table>
| Item | Qty | Per item / day | Line total |
| :--- | :-: | -------------: | ---------: |
@foreach ($booking->items as $item)
| {{ $item->variant->product->name }}{{ $item->variant->label ? ' — '.$item->variant->label : '' }} | {{ $item->quantity }} | R{{ number_format((float) $item->unit_rate, 2) }} | R{{ number_format((float) $item->line_total, 2) }} |
@endforeach
</x-mail::table>

<x-mail::panel>
**Rental subtotal:** R{{ number_format((float) $booking->rental_subtotal, 2) }}

**Refundable deposit:** R{{ number_format((float) $booking->deposit_total, 2) }}

**Total:** R{{ number_format((float) $booking->grand_total, 2) }}
</x-mail::panel>

@if ($booking->notes)
## Your notes

{{ $booking->notes }}
@endif

Quote your booking reference **{{ $booking->reference }}** in any correspondence.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
