<!DOCTYPE html>
<html>
<head>
    <title>Event Details Updated</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h1 style="color: #333;">Event Update Notice</h1>

        <p>The following event has been updated:</p>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Event:</strong> {{ $eventTitle }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($eventDate)->format('F j, Y, g:i a') }}</p>
            <p><strong>Location:</strong> {{ $eventLocation }}</p>
        </div>

        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="color: #856404; margin-top: 0;">Changes Made:</h3>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($changes as $change)
                    <li style="color: #856404;">{{ $change }}</li>
                @endforeach
            </ul>
        </div>

        <p>Please make note of these changes in your calendar.</p>

        <p>If you have any questions or concerns, please don't hesitate to contact us.</p>

        <p style="margin-top: 30px;">Best regards,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html> 