{{-- <x-mail::message>
# Hello {{ $userName }}!

Your registration has been confirmed for the following event:

<x-mail::panel>
**Event:** {{ $eventTitle }}  
**Date:** {{ \Carbon\Carbon::parse($eventDate)->format('F j, Y, g:i a') }}  
**Location:** {{ $eventLocation }}
</x-mail::panel>

Thank you for registering. We look forward to seeing you there!

<x-mail::button :url="config('app.url')">
View Event Details
</x-mail::button>

Best regards,<br>
{{ config('app.name') }}
</x-mail::message>  --}}

<!DOCTYPE html>
<html>
<head>
    <title>Event Registration Confirmed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h1 style="color: #333;">Hello {{ $userName }}!</h1>

        <p>Your registration has been confirmed for the following event:</p>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Event:</strong> {{ $eventTitle }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($eventDate)->format('F j, Y, g:i a') }}</p>
            <p><strong>Location:</strong> {{ $eventLocation }}</p>
        </div>

        <p>Thank you for registering. We look forward to seeing you there!</p>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.url') }}" style="background: #007bff; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Event Details</a>
        </div>

        <p style="margin-top: 30px;">Best regards,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>