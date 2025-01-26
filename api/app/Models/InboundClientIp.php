<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundClientIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_email',
        'ips',
    ];

    public $timestamps = false;
}
