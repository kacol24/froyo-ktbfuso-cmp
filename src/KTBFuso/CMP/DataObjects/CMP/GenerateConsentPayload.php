<?php

namespace KTBFuso\CMP\DataObjects\CMP;

use Illuminate\Support\Carbon;

class GenerateConsentPayload{
    public $applicationCode;
    public $name;
    public $address;
    public $phone;
    public $email;
    public $createdBy;
    public $createdTime;
    public string $entityType = 'individual';

    public function __construct(
        $name,
        $address,
        $phone,
        $email,
        $createdBy,
        $createdTime,
        $entityType = 'individual'
    ) {
        $this->name        = $name;
        $this->address     = $address;
        $this->phone       = $phone;
        $this->email       = $email;
        $this->createdBy   = $createdBy;
        $this->createdTime = $createdTime;
        $this->entityType  = $entityType;
    }

    public static function fromDto( EntryDto $entry ) {
        return new self(
            $entry->name,
            $entry->address,
            $entry->phone,
            $entry->email,
            $entry->name,
            $entry->createdAt
        );
    }

    public function toPayload( $applicationCode ) {
        return [
            'consent' => [
                'ApplicationCode' => $applicationCode,
                'Name'            => $this->name,
                'Address'         => $this->address, // nullable
                'Phone'           => $this->phone,
                'Email'           => $this->email, // nullable
                'EntityType'      => 'individual',
                'CreatedBy'       => $this->createdBy,
                'CreatedTime'     => Carbon::parse( $this->createdTime )->toDateTimeLocalString(),
                'ConsentStatus'   => 'approved',
            ],
        ];
    }

    public function toArray() {
        return [
            'Name'          => $this->name,
            'Address'       => $this->address, // nullable
            'Phone'         => $this->phone,
            'Email'         => $this->email, // nullable
            'EntityType'    => 'individual',
            'CreatedBy'     => $this->createdBy,
            'CreatedTime'   => Carbon::parse( $this->createdTime )->toDateTimeLocalString(),
            'ConsentStatus' => 'approved',
        ];
    }
}
