<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
        }

        .content {
            padding: 20px;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
            margin: 10px 0;
        }

        .content strong {
            color: #333;
        }

        .footer {
            background-color: #f1f1f1;
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #777;
        }

        /* Button styles */
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }

        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 0;
            }

            .content p {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Job Application</h1>
        </div>
        <div class="content">
            <p><strong>Title:</strong> {{ $data['title'] }}</p>
            <p><strong>Service:</strong> {{ $data['service'] }}</p>
            <p><strong>Location:</strong> {{ $data['location'] }}</p>
            <p><strong>Employment Type:</strong> {{ implode(', ', $data['employment_type']) }}</p>
            <p><strong>Hourly Rate Min:</strong> ${{ number_format($data['hourly_rate_min'], 2) }}</p>
            <p><strong>Hourly Rate Max:</strong> ${{ number_format($data['hourly_rate_max'], 2) }}</p>
            <p><strong>Note:</strong> {{ $data['note'] }}</p>
            <p><strong>Applied by:</strong> {{ $data['username'] }}</p>
            <a style="color:white" href="https://jobconnectusa.com" class="button">Visit Our Website</a>
        </div>
        <div class="footer">
            <p>Thank you for considering this application.</p>
            <p>© {{ date('Y') }} Job Connect USA. All Rights Reserved.</p>
        </div>
    </div>
</body>

</html>
