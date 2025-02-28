<x-mail::message>
# New Property Inquiry

A new inquiry has been received for property: {{ $inquiry->property->title }}

**From:** {{ $inquiry->user->name }}  
**Email:** {{ $inquiry->user->email }}  
**Message:** {{ $inquiry->message }}

<x-mail::button :url="config('app.url') . '/admin/properties/' . $inquiry->property_id">
View Property
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message> 