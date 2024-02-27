<?php

/**
 * Plugin Name:         CMP REST API
 * Version:             1.1.0
 * Author:              Froyo
 * Description:         REST API Endpoints for Consent Management Platform to consume, get and manage form submission entries.
 * GitHub Plugin URI:   https://github.com/kacol24/froyo-ktbfuso-cmp
 */

use KTBFuso\CMP\Form_REST_Controller;
use KTBFuso\CMP\Form_Type_REST_Controller;

require_once __DIR__ . '/vendor/autoload.php';

Roots\add_actions( [ 'after_setup_theme', 'rest_api_init' ], 'Roots\bootloader', 5 );

function ktbfuso_cmp_register_my_rest_routes() {
    $restControllers = [
        new Form_REST_Controller(),
        new Form_Type_REST_Controller(),
    ];

    foreach ( $restControllers as $restController ) {
        $restController->register_routes();
    }
}

add_action( 'rest_api_init', 'ktbfuso_cmp_register_my_rest_routes' );
