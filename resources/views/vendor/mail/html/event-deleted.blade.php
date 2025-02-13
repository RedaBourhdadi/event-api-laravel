<!DOCTYPE html>
<html>
<head>
    <title>Event Cancelled</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h1 style="color: #333;">Important Notice: Event Cancelled</h1>

        <p>We regret to inform you that the following event has been cancelled:</p>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Event:</strong> {{ $eventTitle }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($eventDate)->format('F j, Y, g:i a') }}</p>
            <p><strong>Location:</strong> {{ $eventLocation }}</p>
        </div>

        <p>We apologize for any inconvenience this may have caused.</p>

        <p>If you have any questions or concerns, please don't hesitate to contact us.</p>

        <p style="margin-top: 30px;">Best regards,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
