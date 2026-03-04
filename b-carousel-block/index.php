<?php
/**
 * Plugin Name: Carousel Block
 * Description: Create stunning responsive carousels effortlessly.
 * Version: 1.2.2
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: carousel-block
   */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'bicb_fs' ) ) {
	bicb_fs()->set_basename( false, __FILE__ );
}else{
	define( 'BICB_VERSION', isset( $_SERVER['HTTP_HOST'] ) && ( 'localhost' === $_SERVER['HTTP_HOST'] || 'plugins.local' === $_SERVER['HTTP_HOST'] ) ? time() : '1.2.2' );
	define( 'BICB_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'BICB_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'BICB_HAS_PRO', file_exists( BICB_DIR_PATH . 'vendor/freemius/start.php' ) );

	if ( BICB_HAS_PRO ) {
		require_once BICB_DIR_PATH . 'includes/fs.php';
		require_once BICB_DIR_PATH . 'includes/admin/CPT.php';
		require_once BICB_DIR_PATH . 'includes/LicenseActivation.php';
	}else{
		require_once BICB_DIR_PATH . 'includes/fs-lite.php';
		require_once BICB_DIR_PATH . 'includes/admin/SubMenu.php';
	}

	require_once BICB_DIR_PATH . 'includes/Patterns.php';

	function bicbIsPremium(){
		return BICB_HAS_PRO ? bicb_fs()->can_use_premium_code() : false;
	}

	if( !class_exists( 'BICBPlugin' ) ){
		class BICBPlugin{
			function __construct(){
				add_filter( 'plugin_row_meta', [$this, 'pluginRowMeta'], 10, 2 );
				add_action( 'init', [ $this, 'onInit' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] );
				add_action( 'enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets'] );

				add_filter( 'plugin_action_links', [$this, 'pluginActionLinks'], 10, 2 );
				add_filter( 'default_title', [$this, 'defaultTitle'], 10, 2 );
				add_filter( 'default_content', [$this, 'defaultContent'], 10, 2 );
			}
			
			function defaultTitle( $title, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['title'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'bicbCreatePage' ) ) {
						return sanitize_text_field( wp_unslash( $_GET['title'] ) );
					}
				}
				return $title;
			}

			function defaultContent( $content, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['content'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'bicbCreatePage' ) ) {
						// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Content is secured by nonce verification and unslashed to preserve Gutenberg block markup.
						return wp_unslash( $_GET['content'] );
					}
				}
				return $content;
			}

			function pluginActionLinks( $links, $file ) {
				if( plugin_basename( __FILE__ ) === $file ) {
					$helpDemosLink = admin_url( BICB_HAS_PRO ? 'edit.php?post_type=bicb&page=carousel-block#/welcome' : 'tools.php?page=carousel-block#/welcome' );

					$links['help-and-demos'] = sprintf( '<a href="%s" style="%s">%s</a>', $helpDemosLink, 'color:#FF7A00;font-weight:bold', __( 'Help & Demos', 'carousel-block' ) );
				}
	
				return $links;
			}

			function pluginRowMeta( $plugin_meta, $plugin_file ) {
				if ( strpos( $plugin_file, 'b-carousel-block' ) !== false && time() < strtotime( '2025-12-06' ) ) {
					$new_links = array(
						'deal' => "<a href='https://bplugins.com/coupons/?from=plugins.php&plugin=b-carousel-block' target='_blank' style='font-weight: 600; color: #146ef5;'>🎉 Black Friday Sale - Get up to 80% OFF Now!</a>"
					);

					$plugin_meta = array_merge( $plugin_meta, $new_links );
				}

				return $plugin_meta;
			}

			function onInit(){
				register_block_type( __DIR__ . '/build' );
			}

			function adminEnqueueScripts( $hook ) {
				if( strpos( $hook, 'carousel-block' ) ){
					wp_enqueue_style( 'bicb-admin-dashboard', BICB_DIR_URL . 'build/admin/dashboard.css', [], BICB_VERSION );

					$asset_file = include BICB_DIR_PATH . 'build/admin/dashboard.asset.php';
					wp_enqueue_script( 'bicb-admin-dashboard', BICB_DIR_URL . 'build/admin/dashboard.js', array_merge( $asset_file['dependencies'], [ 'wp-util' ] ), BICB_VERSION, true );
					wp_set_script_translations( 'bicb-admin-dashboard', 'carousel-block', BICB_DIR_PATH . 'languages' );
				}
			}

			function enqueueBlockEditorAssets(){
				wp_add_inline_script( 'bicb-carousel-editor-script', 'const bicbpipecheck = ' . wp_json_encode( bicbIsPremium() ) .'; const bicbpricingurl = "'. admin_url( BICB_HAS_PRO ? 'edit.php?post_type=bicb&page=carousel-block#/pricing' : 'tools.php?page=carousel-block#/pricing' ) .'";', 'before' );
			}

			static function renderDashboard(){ ?>
				<div
					id='bicbDashboard'
					data-info='<?php echo esc_attr( wp_json_encode( [
						'version' => BICB_VERSION,
						'isPremium' => bicbIsPremium(),
						'hasPro' => BICB_HAS_PRO,
						'nonce' => wp_create_nonce( 'bicbCreatePage' ),
						'licenseActiveNonce' => wp_create_nonce( 'bPlLicenseActivation' )
					] ) ); ?>'
				></div>
			<?php }
		}
		new BICBPlugin;
	}
}