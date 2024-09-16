<?php

use App\Models\User;
use App\Models\Payment;
use App\Models\BrowsingHistory;
use Illuminate\Support\Facades\Auth;

function int_en_to_bn($number)
{

    $bn_digits = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
    $en_digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    return str_replace($en_digits, $bn_digits, $number);
}
function int_bn_to_en($number)
{

    $bn_digits = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
    $en_digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');

    return str_replace($bn_digits, $en_digits, $number);
}

function month_number_en_to_bn_text($number)
{
    $en = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
    $bn = array('জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'অগাস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর');

    // Adjust the number to be within 1-12 range
    $number = max(1, min(12, $number));

    return str_replace($en, $bn, $number);
}

function month_name_en_to_bn_text($name)
{
    $en = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
    $bn = array('জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'অগাস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর');
    return str_replace($en, $bn, $name);
}

 function extractUrlFromIframe($iframe)
{
    $dom = new \DOMDocument();
    @$dom->loadHTML($iframe);

    $iframes = $dom->getElementsByTagName('iframe');
    if ($iframes->length > 0) {
        $src = $iframes->item(0)->getAttribute('src');
        return $src;
    }

    return $iframe;
}


function routeUsesMiddleware($route, $middlewareName)
{
   return $middlewares = $route->gatherMiddleware();

    foreach ($middlewares as $middleware) {
        if (preg_match("/^$middlewareName:/", $middleware)) {
            return true;
        }
    }

    return false;
}



 function createPayment($amount=1,$payment_method='cash',$type='activation')
{

    $user = Auth::user();

    if($payment_method=='cash'){
        $method ='cash';
    }else{
        $method ='online';

    }

        // Create a payment record
        $payment = Payment::create([
            'union' => 'initial', // Assuming user has a 'union' attribute
            'trxId' => generateTransactionId(), // Implement this method to generate unique transaction IDs
            'userid' => $user->id,
            'type' => $type, // Set type from request
            'amount' => $amount,
            'applicant_mobile' => $user->phone_number,
            'status' => 'pending',
            'date' => now()->format('Y-m-d'),
            'month' => now()->format('m'),
            'year' => now()->format('Y'),
            'paymentUrl' => 'initial',
            'ipnResponse' => null,
            'method' => $method, // Or any method you use
            'payment_type' => 'initial',
            'balance' => 0,
            'payment_method' => $payment_method, // Default to 'cash' if not provided
        ]);

        // Update user step
        // $user->activateUser();

        return [
            'success' => true,
            'message' => 'Payment has been successfully created.',
            'payment' => $payment,
        ];



}

// Example method to generate unique transaction IDs
 function generateTransactionId()
{
    return 'TRX-' . strtoupper(uniqid());
}


function logBrowsingHistory($viewedUserId)
{
    BrowsingHistory::create([
        'user_id' => auth()->id(), // The current user (who is browsing)
        'viewed_user_id' => $viewedUserId, // The user being viewed
        'viewed_at' => now(),
    ]);
}


function getRandomActiveUsers()
{
    // Fetch 4 random users where status is 'active'
    $randomActiveUsers = User::where('status', 'active')
        ->where('role', 'EMPLOYEE')  // Assuming you want to get random EMPLOYEEs
        ->inRandomOrder()  // Randomize the order
        ->take(4)  // Limit to 4 users
        ->get();

    // Return the random active users or an empty array if no users are found
    return $randomActiveUsers->isNotEmpty() ? $randomActiveUsers : [];
}
