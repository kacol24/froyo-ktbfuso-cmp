<?php

namespace KTBFuso\CMP\DataObjects\CMP;

use KTBFuso\CMP\Models\FlamingoEntry;

class EntryDto{
    public $id;
    public $name;
    public $address;
    public $phone;
    public $email;
    public $createdAt;

    public function __construct(
        $id,
        $name,
        $address,
        $phone,
        $email,
        $createdAt
    ) {
        $this->id        = $id;
        $this->name      = $name;
        $this->address   = $address;
        $this->phone     = $phone;
        $this->email     = $email;
        $this->createdAt = $createdAt;
    }

    public static function fromFlamingoEntryModel( FlamingoEntry $model ) {
        return new self(
            $model->ID,
            $model->name,
            implode( ', ', [ $model->address, $model->city ] ),
            $model->phone,
            $model->email,
            $model->post_date
        );
    }
}
