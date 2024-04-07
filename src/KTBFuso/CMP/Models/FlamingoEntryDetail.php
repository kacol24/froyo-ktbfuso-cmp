<?php

namespace KTBFuso\CMP\Models;

use Illuminate\Database\Eloquent\Model;

class FlamingoEntryDetail extends Model{
    protected $table = 'postmeta';
    protected $primaryKey = 'meta_id';
    protected $fillable = [
        self::COLUMN_KEY,
        self::COLUMN_VALUE,
    ];
    public $timestamps = false;

    const COLUMN_KEY = 'meta_key';

    const COLUMN_VALUE = 'meta_value';

    const KEY_CONSENT_ID = 'cmp_consent_id';

    const KEY_CONSENT_STATUS = 'cmp_consent_status';
}
