<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iplist extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'ip',
        'times',
    ];
}
