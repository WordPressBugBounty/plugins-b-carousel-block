<?php

/**
 * Plugin Name: B Carousel Block
 * Description: Create stunning responsive carousels effortlessly.
 * Version: 1.1.2
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: carousel-block
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'bicb_fs' ) ) {
    register_activation_hook( __FILE__, function () {
        if ( is_plugin_active( 'b-carousel-block/index.php' ) ) {
            deactivate_plugins( 'b-carousel-block/index.php' );
        }
        if ( is_plugin_active( 'b-carousel-block-pro/index.php' ) ) {
            deactivate_plugins( 'b-carousel-block-pro/index.php' );
        }
    } );
} else {
    define( 'BICB_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.1.2' ) );
    define( 'BICB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BICB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BICB_HAS_FREE', 'b-carousel-block/index.php' === plugin_basename( __FILE__ ) );
    define( 'BICB_HAS_PRO', 'b-carousel-block-pro/index.php' === plugin_basename( __FILE__ ) );
    if ( !function_exists( 'bicb_fs' ) ) {
        function bicb_fs() {
            global $bicb_fs;
            if ( !isset( $bicb_fs ) ) {
                $fsStartPath = dirname( __FILE__ ) . '/freemius/start.php';
                $bSDKInitPath = dirname( __FILE__ ) . '/bplugins_sdk/init.php';
                if ( BICB_HAS_PRO && file_exists( $fsStartPath ) ) {
                    require_once $fsStartPath;
                } else {
                    if ( BICB_HAS_FREE && file_exists( $bSDKInitPath ) ) {
                        require_once $bSDKInitPath;
                    }
                }
                $bicbConfig = [
                    'id'                  => '15342',
                    'slug'                => 'b-carousel-block',
                    'premium_slug'        => 'b-carousel-block-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_a45f62e2b56488230717561f70db4',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => [
                        'days'               => 7,
                        'is_require_payment' => true,
                    ],
                    'menu'                => [
                        'slug'    => 'edit.php?post_type=bicb',
                        'contact' => false,
                        'support' => false,
                    ],
                ];
                $bicb_fs = ( BICB_HAS_PRO && file_exists( $fsStartPath ) ? fs_dynamic_init( $bicbConfig ) : fs_lite_dynamic_init( $bicbConfig ) );
            }
            return $bicb_fs;
        }

        bicb_fs();
        do_action( 'bicb_fs_loaded' );
    }
    function bicbIsPremium() {
        return ( BICB_HAS_PRO ? bicb_fs()->can_use_premium_code() : false );
    }

    require_once BICB_DIR_PATH . '/includes/CustomPost.php';
    require_once BICB_DIR_PATH . '/includes/pattern.php';
    require_once BICB_DIR_PATH . '/includes/HelpPage.php';
    if ( BICB_HAS_FREE ) {
        require_once BICB_DIR_PATH . '/includes/UpgradePage.php';
    }
    class BICBPlugin {
        function __construct() {
            add_action( 'init', [$this, 'onInit'] );
            add_action( 'wp_ajax_bicbPipeChecker', [$this, 'bicbPipeChecker'] );
            add_action( 'wp_ajax_nopriv_bicbPipeChecker', [$this, 'bicbPipeChecker'] );
            add_action( 'admin_init', [$this, 'registerSettings'] );
            add_action( 'rest_api_init', [$this, 'registerSettings'] );
        }

        function onInit() {
            register_block_type( __DIR__ . '/build' );
        }

        function bicbPipeChecker() {
            $nonce = $_POST['_wpnonce'] ?? null;
            if ( !wp_verify_nonce( $nonce, 'wp_ajax' ) ) {
                wp_send_json_error( 'Invalid Request' );
            }
            wp_send_json_success( [
                'isPipe' => bicbIsPremium(),
            ] );
        }

        function registerSettings() {
            register_setting( 'bicbUtils', 'bicbUtils', [
                'show_in_rest'      => [
                    'name'   => 'bicbUtils',
                    'schema' => [
                        'type' => 'string',
                    ],
                ],
                'type'              => 'string',
                'default'           => wp_json_encode( [
                    'nonce' => wp_create_nonce( 'wp_ajax' ),
                ] ),
                'sanitize_callback' => 'sanitize_text_field',
            ] );
        }

    }

    new BICBPlugin();
}