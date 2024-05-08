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
                                          )
                                          ->where(
                                              FlamingoEntryDetail::COLUMN_VALUE,
                                              $consentId
                                          )
                                          ->firstOrFail();

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

    public function deleteByConsentId( $consentId ) {
        $postMeta = FlamingoEntryDetail::query()
                                       ->where(
                                           FlamingoEntryDetail::COLUMN_KEY,
                                           FlamingoEntryDetail::KEY_CONSENT_ID
                                       )
                                       ->where(
                                           FlamingoEntryDetail::COLUMN_VALUE,
                                           $consentId
                                       )
                                       ->get();

        $postIds = $postMeta->pluck( 'post_id' );
        FlamingoEntryDetail::whereIn( 'post_id', $postIds )->delete();
        FlamingoEntry::whereIn( 'ID', $postIds )->delete();
    }

    public function whereInConsentIds( $consentIds ) {
        $entries = FlamingoEntryDetail::query()
                                      ->where(
                                          FlamingoEntryDetail::COLUMN_KEY,
                                          FlamingoEntryDetail::KEY_CONSENT_ID
                                      )
                                      ->whereIn(
                                          FlamingoEntryDetail::COLUMN_VALUE,
                                          $consentIds
                                      )
                                      ->get();

        $response = [];
        foreach ( $entries as $entryDetail ) {
            $entryDto   = EntryDto::fromFlamingoEntryModel( $entryDetail->entry );
            $response[] = GenerateConsentPayload::fromDto( $entryDto )->toArray();
        }

        return $response;
    }
}
