<x-mail::message>
# New Property Viewing Schedule

A new viewing schedule has been created for property: {{ $schedule->property->title }}

**Date:** {{ $schedule->date }}  
**Time:** {{ $schedule->time }}  
**From:** {{ $schedule->user->name }}  
**Message:** {{ $schedule->message }}

<x-mail::button :url="config('app.url') . '/admin/properties/' . $schedule->property_id">
View Property
</x-mail::button>

Thanks,<br>
 