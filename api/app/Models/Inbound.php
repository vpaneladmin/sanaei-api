<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inbound extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'up',
        'down',
        'total',
        'remark',
        'enable',
        'expiry_time',
        'listen',
        'port',
        'protocol',
        'settings',
        'stream_settings',
        'tag',
        'sniffing',
    ];

    public $timestamps = false;

    protected $hidden = [
        'settings',
        'stream_settings',
        'sniffing',
    ];

    public function traffics()
    {
        return $this->hasMany(ClientTraffic::class);
    }
}
