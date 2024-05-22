<?php

namespace KTBFuso\CMP\Repositories\Entry;

use Illuminate\Support\Arr;
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
     * @param $payload
     *
     * @return FlamingoEntry
     */
    public function setConsentId( $formId, $consentId, $consentStatus, $payload = [] ) {
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

        $entry->details()->updateOrCreate( [
            FlamingoEntryDetail::COLUMN_KEY => '_field_persetujuan',
        ], [
            FlamingoEntryDetail::COLUMN_VALUE =>
                Arr::first(
                    array_values(
                        unserialize( $entry->details->firstWhere( 'meta_key', '_consent' )->meta_value )
                    )
                ),
        ] );

        $entry->details()->updateOrCreate( [
            FlamingoEntryDetail::COLUMN_KEY => '_consent',
        ], [
            FlamingoEntryDetail::COLUMN_VALUE =>
                serialize(
                    array_merge(
                        [
                            FlamingoEntryDetail::KEY_CONSENT_ID     => $consentId,
                            FlamingoEntryDetail::KEY_CONSENT_STATUS => $consentStatus,
                        ],
                        $payload
                    )
                ),
        ] );

        return $entry->refresh();
    }

    public function updateByConsentId( $consentId, $data ) {
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

        $consentStatus = $data['ConsentStatusCode'];
        $postIds       = $postMeta->pluck( 'post_id' );

        FlamingoEntryDetail::whereIn( 'post_id', $postIds )
                           ->where( FlamingoEntryDetail::COLUMN_KEY, '_consent' )
                           ->update( [
                               FlamingoEntryDetail::COLUMN_VALUE => serialize(
                                   array_merge(
                                       [
                                           FlamingoEntryDetail::KEY_CONSENT_ID     => $consentId,
                                           FlamingoEntryDetail::KEY_CONSENT_STATUS => $consentStatus,
                                       ],
                                       $data
                                   )
                               ),
                           ] );

        return FlamingoEntryDetail::whereIn( 'post_id', $postIds )
                                  ->where( FlamingoEntryDetail::COLUMN_KEY, FlamingoEntryDetail::KEY_CONSENT_STATUS )
                                  ->update( [
                                      FlamingoEntryDetail::COLUMN_VALUE => $consentStatus,
                                  ] );
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
