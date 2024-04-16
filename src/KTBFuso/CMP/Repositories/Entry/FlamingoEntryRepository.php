<?php

namespace KTBFuso\CMP\Repositories\Entry;

use KTBFuso\CMP\DataObjects\CMP\EntryDto;
use KTBFuso\CMP\DataObjects\CMP\GenerateConsentPayload;
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
     * @return array
     */
    public function findByConsentId( $consentId ) {
        $entryDetail = FlamingoEntryDetail::query()
                                          ->where(
                                              FlamingoEntryDetail::COLUMN_KEY,
                                              FlamingoEntryDetail::KEY_CONSENT_ID
                                          )->firstOrFail();

        $entryDto = EntryDto::fromFlamingoEntryModel( $entryDetail->entry );

        return GenerateConsentPayload::fromDto( $entryDto )->toArray();
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
            FlamingoEntryDetail::COLUMN_KEY => FlamingoEntryDetail::KEY_CONSENT_ID,
        ], [
            FlamingoEntryDetail::COLUMN_VALUE => $consentId,
        ] );

        $entry->details()->updateOrCreate( [
            FlamingoEntryDetail::COLUMN_KEY => FlamingoEntryDetail::KEY_CONSENT_STATUS,
        ], [
            FlamingoEntryDetail::COLUMN_VALUE => $consentStatus,
        ] );

        return $entry->refresh();
    }
}
