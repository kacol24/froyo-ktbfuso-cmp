<?php

/**
 * Plugin Name:         CMP REST API
 * Version:             1.1.0
 * Author:              Froyo
 * Description:         REST API Endpoints for Consent Management Platform to consume, get and manage form submission entries.
 * GitHub Plugin URI:   https://github.com/kacol24/froyo-ktbfuso-cmp
 */

use KTBFuso\CMP\DataObjects\CMP\EntryDto;
use KTBFuso\CMP\Form_REST_Controller;
use KTBFuso\CMP\Form_Type_REST_Controller;
use KTBFuso\CMP\Repositories\Entry\FlamingoEntryRepository;
use KTBFuso\CMP\Repositories\EntryRepository;
use KTBFuso\CMP\Services\CmpService;

require_once __DIR__ . '/vendor/autoload.php';

Roots\add_actions( [ 'after_setup_theme', 'rest_api_init' ], 'Roots\bootloader', 5 );

add_action( 'rest_api_init', 'register_bindings' );
add_action( 'rest_api_init', 'ktbfuso_cmp_register_my_rest_routes' );
add_action( 'wpcf7_after_flamingo', 'send_entry_data_to_cmp' );
register_activation_hook( __FILE__, 'activate' );

function activate() {
    global $wpdb;
    $wpdb->hide_errors();

    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

    $charset_collate = '';
    if ( $wpdb->has_cap( 'collation' ) ) {
        $charset_collate = $wpdb->get_charset_collate();
    }

    $table_name = $wpdb->prefix . 'cmp_logs';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
              `id` bigint unsigned NOT NULL AUTO_INCREMENT,
              `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
              `status` int DEFAULT NULL,
              `should_retry` tinyint(1) DEFAULT '0',
              `request` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `{$table_name}_url_index` (`url`)
)$charset_collate";

    dbDelta( $sql );
}

function register_bindings() {
    app()->bind( EntryRepository::class, FlamingoEntryRepository::class );
}

function ktbfuso_cmp_register_my_rest_routes() {
    $restControllers = [
        new Form_REST_Controller(),
        new Form_Type_REST_Controller(),
    ];

    foreach ( $restControllers as $restController ) {
        $restController->register_routes();
    }
}

function send_entry_data_to_cmp( $formData ) {
    $formEntryId = $formData['flamingo_inbound_id'];

    $repository = app()->make( EntryRepository::class );
    $cmpService = new CmpService();
    $formEntry  = $repository->findById( $formEntryId );
    $entryDto   = EntryDto::fromFlamingoEntryModel( $formEntry );

    $cmpService->generateConsent( $entryDto );
}
