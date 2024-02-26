<?php

namespace KTBFuso\CMP\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FormType extends Model{
    protected $table = 'posts';
    protected $primaryKey = 'ID';

    const CREATED_AT = 'post_date';

    const UPDATED_AT = 'post_modified';

    protected static function booted() {
        static::addGlobalScope( 'contact_form', function (Builder $builder) {
            $builder->where( 'post_type', 'wpcf7_contact_form' );
        } );
    }
}
