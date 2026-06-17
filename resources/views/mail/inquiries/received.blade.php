<x-mail::message>
# New website inquiry

A new inquiry has come through from the website.

<x-mail::panel>
**Topic:** {{ $inquiry['inquiry_topic'] }}

**Name:** {{ $inquiry['name'] }}

**Email:** {{ $inquiry['email'] }}

**Phone:** {{ $inquiry['phone'] ?: 'Not provided' }}

**Hire dates:** {{ $inquiry['hire_start_date'] ?: 'Not provided' }}{{ $inquiry['hire_end_date'] ? ' to '.$inquiry['hire_end_date'] : '' }}

**Area:** {{ $inquiry['location'] ?: 'Not provided' }}
</x-mail::panel>

## Message

{{ $inquiry['message'] }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
