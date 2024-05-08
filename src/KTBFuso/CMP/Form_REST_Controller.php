<?php

namespace KTBFuso\CMP;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use KTBFuso\CMP\Models\Entry;
use KTBFuso\CMP\Models\FlamingoEntry;
use KTBFuso\CMP\Repositories\EntryRepository;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;

class Form_REST_Controller extends WP_REST_Controller{
    public function __construct() {
        $this->namespace = 'cmp/v1';
        $this->rest_base = 'forms';
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            $this->rest_base,
            [
                //[
                //    'methods'             => \WP_REST_Server::READABLE,
                //    'callback'            => [ $this, 'get_items' ],
                //    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                //    'args'                => [
                //        'form_type_id' => [
                //            'required'    => true,
                //            'type'        => 'integer',
                //            'description' => 'Form type ID (available form_type_ids can be fetched from /form_types)',
                //        ],
                //        'limit'        => [
                //            'default'     => 10,
                //            'type'        => 'integer',
                //            'description' => 'Limits the number of form entries returned.',
                //        ],
                //        'last_form_id' => [
                //            'type'        => 'integer',
                //            'description' => 'Form entries are ordered by ID. So if last_form_id is provided, only form entries with ID greater than provided will be returned.',
                //        ],
                //    ],
                //],
                [
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_items' ],
                    'permission_callback' => [ $this, 'delete_items_permissions_check' ],
                    'args'                => [
                        'consent_ids' => [
                            'required'    => true,
                            'type'        => 'array',
                            'description' => 'Array of consent_ids to be deleted.',
                        ],
                    ],
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            $this->rest_base . '/(?P<consent_id>\w+)',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'get_item_permissions_check' ],
                ],
                [
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                ],
            ],
        );
    }

    public function get_items_permissions_check( $request ) {
        return get_current_user_id() > 0;
    }

    public function get_items( $request ) {
        $formTypeId = $request->get_param( 'form_type_id' );
        $limit      = $request->get_param( 'limit' );
        $lastFormId = $request->get_param( 'last_form_id' );

        $entries = Entry::where( 'form_id', 'cf_' . $formTypeId )
                        ->when( $lastFormId > 0, function ( $query ) use ( $lastFormId ) {
                            return $query->where( 'vxcf_leads.id', '>', $lastFormId );
                        } )
                        ->join( 'posts', function ( $join ) use ( $formTypeId ) {
                            $join->where( 'posts.ID', $formTypeId );
                        } )
                        ->limit( $limit )
                        ->get();

        $data = [];

        if ( empty( $entries ) ) {
            return rest_ensure_response( $data );
        }

        foreach ( $entries as $entry ) {
            $response = $this->prepare_item_for_response( $entry, $request );
            $data[]   = $this->prepare_response_for_collection( $response );
        }

        return rest_ensure_response( $data );
    }

    public function get_item_permissions_check( $request ) {
        return get_current_user_id() > 0;
    }

    public function get_item( $request ) {
        $id = $request['consent_id'];

        if ( ! $id ) {
            return new WP_Error( 'ktbfuso_cmp_rest_consent_id_required', 'Consent ID is not provided',
                [ 'status' => 400 ] );
        }

        $repo = app()->make( EntryRepository::class );

        try {
            $entry = $repo->findByConsentId( $id );
        } catch ( ModelNotFoundException $e ) {
            return new WP_Error( 'ktbfuso_cmp_rest_invalid_consent_id', 'Consent ID not found', [ 'status' => 404 ] );
        }

        return $this->prepare_item_for_response( $entry, $request );
    }

    public function prepare_item_for_response( $item, $request ) {
        return rest_ensure_response( $item );
    }

    public function delete_item_permissions_check( $request ) {
        return get_current_user_id() > 0;
    }

    public function delete_item( $request ) {
        $id = $request['consent_id'];

        if ( ! $id ) {
            return new WP_Error( 'ktbfuso_cmp_rest_consent_id_required', 'Consent ID is not provided',
                [ 'status' => 400 ] );
        }

        $repo = app()->make( EntryRepository::class );

        try {
            $entry = $repo->findByConsentId( $id );
        } catch ( ModelNotFoundException $e ) {
            return new WP_Error( 'ktbfuso_cmp_rest_invalid_consent_id', 'Consent ID not found', [ 'status' => 404 ] );
        }

        $repo->deleteByConsentId( $id );

        $response = new WP_REST_Response();
        $response->set_data( [
            'deleted'  => true,
            'previous' => $this->prepare_item_for_response( $entry, $request ),
        ] );

        return $response;
    }

    public function delete_items_permissions_check( $request ) {
        return get_current_user_id() > 0;
    }

    public function delete_items( $request ) {
        $ids = $request['consent_ids'];

        $repo = app()->make( EntryRepository::class );
        $entries = $repo->whereInConsentIds($ids);

        if ( is_wp_error( $entries ) ) {
            return $entries;
        }

        if ( ! count( $entries ) ) {
            return new WP_Error( 'ktbfuso_cmp_rest_invalid_form_id', 'Invalid form IDs', [ 'status' => 404 ] );
        }

        $previous = [];
        foreach ( $entries as $entry ) {
            $previous[] = $this->prepare_item_for_response( $entry, $request );
        }

        foreach ( $ids as $consentId ) {
            $repo->deleteByConsentId( $consentId );
        }

        $response = new WP_REST_Response();
        $response->set_data( [
            'deleted'  => true,
            'previous' => $previous,
        ] );

        return $response;
    }
}
