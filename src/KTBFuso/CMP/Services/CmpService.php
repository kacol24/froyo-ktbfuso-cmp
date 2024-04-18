<?php

namespace KTBFuso\CMP\Services;

use Illuminate\Support\Facades\Http;
use KTBFuso\CMP\DataObjects\CMP\EntryDto;
use KTBFuso\CMP\DataObjects\CMP\GenerateConsentPayload;
use KTBFuso\CMP\Models\CmpLog;
use KTBFuso\CMP\Repositories\EntryRepository;

class CmpService{
    protected $baseUrl = 'https://asia-southeast2-gp-prod-shared-cmp.cloudfunctions.net/gcshrfncq001/ConsentService';
    protected $tennantCode = 'ktb';
    protected $applicationCode = 'ktbweb';

    public function __construct() {
        if ( ! $this->applicationCode ) {
            throw new \Exception( 'Parameter applicationCode must be set.' );
        }
    }

    public function generateConsent( EntryDto $entryDto ) {
        $endpoint = 'v1/Generate';

        $payload  = GenerateConsentPayload::fromDto( $entryDto )
                                          ->toPayload( $this->applicationCode );
        $log      = CmpLog::create( [
            'url'     => $endpoint,
            'host'    => $this->baseUrl,
            'method'  => 'POST',
            'request' => $payload,
        ] );
        $response = Http::baseUrl( $this->baseUrl )
                        ->acceptJson()
                        ->post( $endpoint, $payload );
        $log->update( [
            'status'       => $response->status(),
            'response'     => $response->json(),
            'should_retry' => ! $response->json( 'isSuccess' ),
        ] );

        if ( ! $response->json( 'isSuccess' ) ) {
            error_log( $response->json( 'message' ) );

            return $response->json( 'message' );
        }

        $repository = app()->make( EntryRepository::class );

        return $repository->setConsentId(
            $entryDto->id,
            $response->json( 'consent.ConsentCode' ),
            $response->json( 'consent.ConsentStatus' )
        );
    }
}
