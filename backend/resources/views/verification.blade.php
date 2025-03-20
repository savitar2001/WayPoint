<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body>
    <p>Dear {{ $toName }},</p>
    <p>Please click the following link to verify your email address:</p>
    <p><a href="{{ $url }}">{{ $url }}</a></p>
    <p>Thank you!</p>
</body>
</html>
