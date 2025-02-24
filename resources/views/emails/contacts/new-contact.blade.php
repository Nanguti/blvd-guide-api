@component('mail::message')
# New Contact Form Submission

You have received a new contact form submission:

**Name:** {{ $contact->name }}  
**Email:** {{ $contact->email }}  
@if($contact->phone)
**Phone:** {{ $contact->phone }}  
@endif

**Message:**  
{{ $contact->message }}

@component('mail::button', ['url' => config('app.url').'/admin/contacts/'.$contact->id])
View Contact
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent 