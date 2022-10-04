<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'digest',
        'total_price',
        'currency',
        'email',
        'salt',
        'products',
        'prices',
        'username',
        'uid',
        'txn_id',
        'payment_type',
        'payment_status',
        'create_time',
    ];
}
