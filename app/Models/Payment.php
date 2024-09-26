<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'union',
        'trxId',
        'checkout_session_id',
        'userid',
        'hiring_request_id',
        'type',
        'amount',
        'applicant_mobile',
        'status',
        'date',
        'month',
        'year',
        'paymentUrl',
        'ipnResponse',
        'method',
        'payment_type',
        'balance',

    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function hiringRequest()
    {
        return $this->belongsTo(HiringRequest::class, 'hiring_request_id');
    }

}
