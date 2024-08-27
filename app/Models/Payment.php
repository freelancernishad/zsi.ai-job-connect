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
        'userid',
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
}
