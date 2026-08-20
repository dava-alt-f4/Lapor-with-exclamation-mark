<!DOCTYPE html>
<html>
<head>
    <title>OTP Code</title>
</head>
<body>
    <h2>Hello!</h2>
    <p>You have requested to log in to our application with OTP.</p>
    <p>Here is your 6-digit OTP code:</p>
    <h3 style="background-color: #f3f4f6; padding: 12px; text-align: center; letter-spacing: 4px; font-size: 24px;">
        {{ $otpCode }}
    </h3>
    <p>This code will <strong>expire in 5 minutes</strong>. Do not give this code to anyone.</p>
    <p>Thank you.</p>
</body>
</html>
