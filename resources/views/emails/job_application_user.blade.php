<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;">
        <!-- Header Section -->
        <div style="text-align: left; margin-bottom: 20px;">
            <img src="https://jobconnectusa.com/_next/image?url=%2Flogo.png&w=256&q=75" alt="Job Connect USA Logo" style="max-width: 80px;">
        </div>

        <!-- Main Content -->
        <div style="text-align: left; margin-bottom: 20px;">
            <p style="font-size: 16px; color: #555555;">Dear {{ $data['username'] }},</p>
            <p style="font-size: 16px; color: #555555;">
                Thank you for applying for the <strong>{{ $data['company_name'] }}</strong> position through Job Connect USA. We’ve received your application, and it will be reviewed based on your qualifications, skills, and experience. If selected, the admin will reach out with the next steps.
            </p>
            <p style="font-size: 16px; color: #555555;">
                Thank you again for your interest in this opportunity!
            </p>
            {{-- <p style="font-size: 16px; color: #555555;">
                <strong>Applied by:</strong> {{ $data['username'] }}
            </p> --}}

            <!-- Visit Website Button -->
            <div style="text-align: center; margin: 20px 0;">
                <a href="https://jobconnectusa.com"
                   style="display: inline-block; padding: 12px 30px; font-size: 16px; font-weight: bold; color: #ffffff; background-color: #28a745; text-decoration: none; border-radius: 5px;">
                   Visit Our Website
                </a>
            </div>
        </div>

        <!-- Footer Section -->
        <div style="text-align: left; font-size: 12px; color: #888888; margin-top: 30px;">
            <p>Best regards,<br>Job Connect USA Team</p>
            <p style="margin-top: 20px;">
                <a href="https://jobconnectusa.com/privacy-policy" style="color: #007bff; text-decoration: none;">Privacy Policy</a> |
                <a href="https://jobconnectusa.com/contact" style="color: #007bff; text-decoration: none;">Contact Support</a>
            </p>
            <p style="margin-top: 20px;">© {{ date('Y') }} Job Connect USA. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
