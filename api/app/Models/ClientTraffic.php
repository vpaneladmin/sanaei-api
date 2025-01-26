<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTraffic extends Model
{
    use HasFactory;

    protected $fillable = [
        'inbound_id',
        'enable',
        'email',
        'up',
        'down',
        'expiry_time',
        'total',
        'reset',
    ];

    public $timestamps = false;

    public $table = 'client_traffics';

    protected $hidden = [
        'id',
        'enable',
        'expiry_time',
    ];
}
