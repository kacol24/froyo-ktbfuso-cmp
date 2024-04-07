<?php

namespace KTBFuso\CMP\Models;

use Illuminate\Database\Eloquent\Model;

class FlamingoEntry extends Model{
    protected $table = 'posts';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'post_date';

    const UPDATED_AT = 'post_modified';

    protected $with = [
        'details',
    ];

    public function details() {
        return $this->hasMany( FlamingoEntryDetail::class, 'post_id' );
    }

    public function getNameAttribute() {
        return optional( $this->details->firstWhere( FlamingoEntryDetail::COLUMN_KEY, '_field_nama' ) )->{FlamingoEntryDetail::COLUMN_VALUE};
    }

    public function getEmailAttribute() {
        return optional( $this->details->firstWhere( FlamingoEntryDetail::COLUMN_KEY, '_field_email' ) )->{FlamingoEntryDetail::COLUMN_VALUE};
    }

    public function getCityAttribute() {
        return optional( $this->details->firstWhere( FlamingoEntryDetail::COLUMN_KEY, '_field_kota' ) )->{FlamingoEntryDetail::COLUMN_VALUE};
    }

    public function getPhoneAttribute() {
        return optional( $this->details->firstWhere( FlamingoEntryDetail::COLUMN_KEY, '_field_telepon' ) )->{FlamingoEntryDetail::COLUMN_VALUE};
    }

    public function getAddressAttribute() {
        return optional( $this->details->firstWhere( FlamingoEntryDetail::COLUMN_KEY, '_field_alamat' ) )->{FlamingoEntryDetail::COLUMN_VALUE};
    }

    public function getMessageAttribute() {
        return optional( $this->details->firstWhere( FlamingoEntryDetail::COLUMN_KEY, '_field_pesan' ) )->{FlamingoEntryDetail::COLUMN_VALUE};
    }
}
