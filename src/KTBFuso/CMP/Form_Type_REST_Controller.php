<?php

namespace KTBFuso\CMP;

use KTBFuso\CMP\Models\FormType;
use WP_REST_Controller;

class Form_Type_REST_Controller extends WP_REST_Controller{
    public function __construct() {
        $this->namespace     = 'cmp/v1';
        $this->rest_base = 'form_types';
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            $this->rest_base,
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'get_items_permissions_check' ],
            ]
        );
    }

    public function get_items_permissions_check( $request ) {
        return get_current_user_id() > 0;
    }

    public function get_items( $request ) {
        $formTypes = FormType::get();

        $data = [];

        if ( empty( $formTypes ) ) {
            return rest_ensure_response( $data );
        }

        foreach ( $formTypes as $formType ) {
            $response = $this->prepare_item_for_response( $formType, $request );
            $data[]   = $this->prepare_response_for_collection( $response );
        }

        return rest_ensure_response( $data );
    }

    public function prepare_item_for_response( $item, $request ) {
        $mapped = [
            'id'         => $item->ID,
            'type'       => $item->post_title,
            'created_at' => $item->{FormType::CREATED_AT},
            'updated_at' => $item->{FormType::UPDATED_AT},
        ];

        return rest_ensure_response( $mapped );
    }
}
