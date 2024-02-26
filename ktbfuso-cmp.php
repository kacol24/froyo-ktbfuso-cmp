<?php

/**
 * Plugin Name:   CMP REST API
 * Version:       0.0.1
 * Author:        Froyo
 */

use KTBFuso\CMP\Form_REST_Controller;
use KTBFuso\CMP\Form_Type_REST_Controller;

require_once __DIR__ . '/vendor/autoload.php';

Roots\add_actions( [ 'after_setup_theme', 'rest_api_init' ], 'Roots\bootloader', 5 );

function ktbfuso_cmp_register_my_rest_routes() {
    $formController = new Form_REST_Controller();
    $formController->register_routes();

    $formTypeController = new Form_Type_REST_Controller();
    $formTypeController->register_routes();
}

add_action( 'rest_api_init', 'ktbfuso_cmp_register_my_rest_routes' );
