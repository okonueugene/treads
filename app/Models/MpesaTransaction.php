<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'merchant_request_id',
        'checkout_request_id',
        'receipt_number',
        'result_code',
        'result_desc',
        'callback_data',
        'transaction_status',
    ];

    protected $casts = [
        'callback_data' => 'array',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
