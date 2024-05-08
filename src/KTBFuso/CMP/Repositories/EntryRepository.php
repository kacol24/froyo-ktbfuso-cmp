<?php

namespace KTBFuso\CMP\Repositories;

interface EntryRepository{
    public function findById( $formId );

    public function findByConsentId( $consentId );

    public function whereInConsentIds( $consentIds );

    public function setConsentId( $formId, $consentId, $consentStatus );

    public function deleteByConsentId( $consentId );
}
