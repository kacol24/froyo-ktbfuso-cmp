<?php

namespace KTBFuso\CMP\Repositories;

interface EntryRepository{
    public function findById( $formId );

    public function findByConsentId( $consentId );

    public function setConsentId( $formId, $consentId, $consentStatus );
}
