<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0ea5e9;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .original-message {
            background-color: #f0f0f0;
            padding: 15px;
            margin-top: 20px;
            border-left: 3px solid #0ea5e9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('app.name') }}</h2>
    </div>
    
    <div class="content">
        <p>Dear {{ $originalMessage->first_name }},</p>
        
        <p>Thank you for contacting us. We have received your message and are pleased to respond.</p>
        
        <div style="margin: 20px 0;">
            {!! nl2br(e($replyMessage)) !!}
        </div>
        
        <p>If you have any further questions or concerns, please don't hesitate to reach out to us again.</p>
        
        <p>Best regards,<br>
        The {{ config('app.name') }} Team</p>
        
        <div class="original-message">
            <p><strong>Your Original Message:</strong></p>
            <p><strong>Subject:</strong> {{ $originalMessage->subject }}</p>
            <p><strong>Message:</strong><br>
            {!! nl2br(e($originalMessage->message)) !!}</p>
        </div>
    </div>
    
    <div class="footer">
        <p>This email was sent in response to your contact form submission on {{ config('app.url') }}</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>