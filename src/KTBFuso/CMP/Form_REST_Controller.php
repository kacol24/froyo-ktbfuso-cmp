<?php

namespace KTBFuso\CMP;

use Illuminate\Support\Arr;
use KTBFuso\CMP\Models\Entry;
use KTBFuso\CMP\Models\FormType;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;

class Form_REST_Controller extends WP_REST_Controller{
    public function __construct() {
        $this->namespace     = 'cmp/v1';
        $this->rest_base = 'forms';
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            $this->rest_base,
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                    'args'                => [
                        'form_type_id' => [
                            'required'    => true,
                            'type'        => 'integer',
                            'description' => 'Form type ID (available form_type_ids can be fetched from /form_types)',
                        ],
                    ],
                ],
                [
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_items' ],
                    'permission_callback' => [ $this, 'delete_items_permissions_check' ],
                    'args'                => [
                        'form_ids' => [
                            'required' => true,
                            'type'        => 'array',
                            'description' => 'Array of form_ids to be deleted.',
                        ],
                    ],
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            $this->rest_base . '/(?P<id>[\d]+)',
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

        $entries = Entry::where( 'form_id', 'cf_' . $formTypeId )
                        ->join( 'posts', function ( $join ) use ( $formTypeId ) {
                            $join->where( 'posts.ID', $formTypeId );
                        } )
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
        $id    = (int) $request['id'];
        $entry = Entry::query()
                      ->find( $id );

        if ( empty( $entry ) ) {
            return new WP_Error( 'ktbfuso_cmp_rest_invalid_form_id', 'Invalid form ID', [ 'status' => 404 ] );
        }

        return $this->prepare_item_for_response( $entry, $request );
    }

    public function prepare_item_for_response( $item, $request ) {
        $formType   = optional( $item )->post_title;
        $formTypeId = optional( $item )->ID;

        if ( ! $formTypeId ) {
            $formTypeId = Arr::last( explode( '_', $item->form_id ) );
            $formType   = FormType::find( $formTypeId )->post_title;
        }

        $mapped = [
            'id'           => $item->id,
            'form_type_id' => $formTypeId,
            'form_type'    => $formType,
        ];

        foreach ( $item->details as $detail ) {
            $mapped[ $detail->name ] = $detail->value;
        }

        $mapped['created_at'] = $item->{Entry::CREATED_AT};
        $mapped['updated_at'] = $item->{Entry::UPDATED_AT};

        return rest_ensure_response( $mapped );
    }

    public function delete_item_permissions_check( $request ) {
        return get_current_user_id() > 0;
    }

    public function delete_item( $request ) {
        $id    = (int) $request['id'];
        $entry = Entry::query()
                      ->find( $id );

        if ( is_wp_error( $entry ) ) {
            return $entry;
        }

        if ( empty( $entry ) ) {
            return new WP_Error( 'ktbfuso_cmp_rest_invalid_form_id', 'Invalid form ID', [ 'status' => 404 ] );
        }

        $entry->delete();

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
        $ids = $request['form_ids'];

        $entries = Entry::query()
                        ->whereIn( 'id', $ids )
                        ->get();

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

        Entry::query()
             ->whereIn( 'id', $ids )
             ->delete();

        $response = new WP_REST_Response();
        $response->set_data( [
            'deleted'  => true,
            'previous' => $previous,
        ] );

        return $response;
    }
}
