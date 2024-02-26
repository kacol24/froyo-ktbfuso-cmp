<?php

namespace KTBFuso\CMP\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model{
    protected $table = 'vxcf_leads';

    const CREATED_AT = 'created';

    const UPDATED_AT = 'updated';

    protected $with = [
        'details'
    ];

    public function details() {
        return $this->hasMany( EntryDetail::class, 'lead_id' );
    }
}
