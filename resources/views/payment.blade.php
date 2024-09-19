<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Page</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
        }
        .card-element {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .card-errors {
            color: #fa755a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Payment Details</h2>
        <form id="payment-form">
            <div class="form-group">
                <label for="amount">Amount (in USD):</label>
                <input type="number" id="amount" class="form-control" placeholder="Amount" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" id="description" class="form-control" placeholder="Description">
            </div>
            <div class="form-group">
                <label for="card-number">Card Number:</label>
                <div id="card-number" class="card-element"></div>
                <div id="card-number-errors" class="card-errors mt-2"></div>
            </div>
            <div class="form-group">
                <label for="card-expiry">Expiration Date:</label>
                <div id="card-expiry" class="card-element"></div>
                <div id="card-expiry-errors" class="card-errors mt-2"></div>
            </div>
            <div class="form-group">
                <label for="card-cvc">CVC:</label>
                <div id="card-cvc" class="card-element"></div>
                <div id="card-cvc-errors" class="card-errors mt-2"></div>
            </div>
            <button type="submit" class="btn btn-primary">Pay</button>
        </form>
    </div>

    <script>
        // Initialize Stripe
        const stripe = Stripe(@json(config('services.stripe.key'))); // Get the key from the config
        const elements = stripe.elements();

        // Create individual elements for card number, expiration, and CVC
        const cardNumber = elements.create('cardNumber');
        const cardExpiry = elements.create('cardExpiry');
        const cardCvc = elements.create('cardCvc');

        // Mount individual elements to the DOM
        cardNumber.mount('#card-number');
        cardExpiry.mount('#card-expiry');
        cardCvc.mount('#card-cvc');

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const amount = document.getElementById('amount').value;
            const description = document.getElementById('description').value;

            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardNumber,
                billing_details: {
                    // Include optional billing details here
                }
            });

            if (error) {
                document.getElementById('card-number-errors').innerText = error.message;
            } else {
                // Send paymentMethod.id to your server
                fetch('/api/stripe/create/payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        payment_method_id: paymentMethod.id,
                        amount: amount * 100, // Convert to cents
                        currency: 'usd',
                        description: description,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Payment successful');
                        // Optionally, redirect to a success page or display a success message
                    } else {
                        document.getElementById('card-number-errors').innerText = data.message;
                    }
                });
            }
        });
    </script>
</body>
</html>
