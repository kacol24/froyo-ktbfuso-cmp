<?php

namespace KTBFuso\CMP\Models;

use Illuminate\Database\Eloquent\Model;

class CmpLog extends Model{
    protected $fillable = [
        'url',
        'host',
        'method',
        'status',
        'should_retry',
        'request',
        'response',
    ];
    protected $casts = [
        'request'  => 'json',
        'response' => 'json',
    ];
}
