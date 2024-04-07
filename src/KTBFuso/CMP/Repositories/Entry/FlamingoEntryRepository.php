<?php

namespace KTBFuso\CMP\Repositories\Entry;

use KTBFuso\CMP\Models\FlamingoEntry;
use KTBFuso\CMP\Models\FlamingoEntryDetail;
use KTBFuso\CMP\Repositories\EntryRepository;

/**
 *
 */
class FlamingoEntryRepository implements EntryRepository{
    /**
     * @param $formId
     *
     * @return mixed
     */
    public function findById( $formId ) {
        return FlamingoEntry::findOrFail( $formId );
    }

    /**
     * @param $consentId
     *
     * @return void
     */
    public function findByConsentId( $consentId ) {
        // TODO: Implement findByConsentId() method.
    }

    /**
     * @param $formId
     * @param $consentId
     * @param $consentStatus
     *
     * @return FlamingoEntry
     */
    public function setConsentId( $formId, $consentId, $consentStatus ) {
        $entry = FlamingoEntry::find( $formId );

        $entry->details()->updateOrCreate( [
            FlamingoEntryDetail::COLUMN_KEY   => FlamingoEntryDetail::KEY_CONSENT_ID,
            FlamingoEntryDetail::COLUMN_VALUE => $consentId,
        ] );

        $entry->details()->updateOrCreate( [
            FlamingoEntryDetail::COLUMN_KEY   => FlamingoEntryDetail::KEY_CONSENT_STATUS,
            FlamingoEntryDetail::COLUMN_VALUE => $consentStatus,
        ] );

        $entry->details()->updateOrCreate( [
            FlamingoEntryDetail::COLUMN_KEY   => FlamingoEntryDetail::KEY_CONSENT_ID,
            FlamingoEntryDetail::COLUMN_VALUE => $consentId,
        ] );

        return $entry->refresh();
    }
}
