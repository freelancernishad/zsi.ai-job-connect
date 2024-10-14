<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
        <!-- Header Section -->
        <div style="text-align: left; margin-bottom: 20px;">
            <img src="https://jobconnectusa.com/_next/image?url=%2Flogo.png&w=256&q=75" alt="Job Connect USA Logo" style="max-width: 80px;">
        </div>

        <!-- Main Content -->
        <div style="text-align: left; margin-bottom: 20px;">
            <p style="font-size: 16px; color: #555555;">Dear {{ $user->first_name }},</p>
            <p style="font-size: 16px; color: #555555;">
                Thank you for signing up for Job Connect USA! To complete your registration, please verify your email address by clicking the button below:
            </p>

            <!-- Verify Email Button -->
            <div style="text-align: center; margin: 20px 0;">
                <a href="{{ $verify_url.'?token=' . $user->email_verification_hash }}"
                   style="display: inline-block; padding: 12px 30px; font-size: 16px; font-weight: bold; color: #ffffff; background-color: #28a745; text-decoration: none; border-radius: 5px;">
                   Verify Your Email Address
                </a>
            </div>

            <p style="font-size: 16px; color: #555555;">
                Once your email is verified, you’ll be able to access all of our features, including finding the best employees for your needs or applying for jobs that match your skills.
            </p>
            <p style="font-size: 16px; color: #555555;">
                If you didn’t sign up for this account, please disregard this email.
            </p>
        </div>

        <!-- Footer Section -->
        <div style="text-align: left; font-size: 12px; color: #888888; margin-top: 30px;">
            <p>Best regards,<br>Job Connect USA Team</p>
            <p style="margin-top: 20px;">
                <a href="https://jobconnectusa.com/privacy-policy" style="color: #007bff; text-decoration: none;">Privacy Policy</a> |
                <a href="https://jobconnectusa.com/contact" style="color: #007bff; text-decoration: none;">Contact Support</a>
            </p>
        </div>
    </div>
</body>
</html>
