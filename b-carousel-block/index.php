<?php
/**
 * Plugin Name: Carousel Block
 * Description: Create stunning responsive carousels effortlessly.
 * Version: 1.2.3
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * Plugin URI: https://bplugins.com/products/b-carousel-block
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: b-carousel-block
 * Requires at least: 6.5
 * Tested up to: 7.0
 * Requires PHP: 7.4
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'bicb_fs' ) ) {
	bicb_fs()->set_basename( true, __FILE__ );
}else{
	define( 'BICB_VERSION', ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : '1.2.3' );
	define( 'BICB_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'BICB_DIR_PATH', plugin_dir_path( __FILE__ ) );

	require_once BICB_DIR_PATH . 'includes/fs-lite.php';
	require_once BICB_DIR_PATH . 'includes/admin/SubMenu.php';

	require_once BICB_DIR_PATH . 'includes/Patterns.php';

	if( !class_exists( 'BICBPlugin' ) ){
		/**
		 * Main plugin class for Carousel Block.
		 *
		 * Registers the block, enqueues assets, and handles admin functionality.
		 *
		 * @since 1.0.0
		 */
		class BICBPlugin{
			/**
			 * Constructor.
			 *
			 * Registers all hooks for the plugin.
			 */
			public function __construct(){
				add_action( 'init', [ $this, 'onInit' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'adminEnqueueScripts' ] );
				add_action( 'enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets'] );

				add_filter( 'plugin_action_links', [$this, 'pluginActionLinks'], 10, 2 );
				add_filter( 'default_title', [$this, 'defaultTitle'], 10, 2 );
				add_filter( 'default_content', [$this, 'defaultContent'], 10, 2 );
			}
			
			/**
			 * Filters the default post title for new pages created from the dashboard.
			 *
			 * @since 1.0.6
			 *
			 * @param string $title The default post title.
			 * @param \WP_Post $post The post object.
			 * @return string The filtered title.
			 */
			public function defaultTitle( $title, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['title'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'bicbCreatePage' ) ) {
						return sanitize_text_field( wp_unslash( $_GET['title'] ) );
					}
				}
				return $title;
			}

			/**
			 * Filters the default post content for new pages created from the dashboard.
			 *
			 * @since 1.0.6
			 *
			 * @param string $content The default post content.
			 * @param \WP_Post $post The post object.
			 * @return string The filtered content.
			 */
			public function defaultContent( $content, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['content'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'bicbCreatePage' ) ) {
						// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Content is secured by nonce verification and unslashed to preserve Gutenberg block markup.
						return wp_unslash( $_GET['content'] );
					}
				}
				return $content;
			}

			/**
			 * Adds custom action links to the plugin listing on the Plugins page.
			 *
			 * @since 1.0.0
			 *
			 * @param array $links Existing plugin action links.
			 * @param string $file Plugin file path.
			 * @return array Modified action links.
			 */
			public function pluginActionLinks( $links, $file ) {
				if( plugin_basename( __FILE__ ) === $file ) {
					$helpDemosLink = admin_url( 'tools.php?page=carousel-block#/welcome' );

					$links['help-and-demos'] = sprintf( '<a href="%s" style="%s">%s</a>', $helpDemosLink, 'color:#FF7A00;font-weight:bold', __( 'Help & Demos', 'b-carousel-block' ) );
				}
	
				return $links;
			}

			/**
			 * Registers the Carousel block type on init.
			 *
			 * @since 1.0.0
			 *
			 * @return void
			 */
			public function onInit(){
				register_block_type( __DIR__ . '/build' );
			}

			/**
			 * Enqueues admin dashboard scripts and styles.
			 *
			 * @since 1.0.0
			 *
			 * @param string $hook The current admin page hook suffix.
			 * @return void
			 */
			public function adminEnqueueScripts( $hook ) {
				if( strpos( $hook, 'carousel-block' ) ){
					wp_enqueue_style( 'bicb-admin-dashboard', BICB_DIR_URL . 'build/admin/dashboard.css', [], BICB_VERSION );

					$asset_file = include BICB_DIR_PATH . 'build/admin/dashboard.asset.php';
					wp_enqueue_script( 'bicb-admin-dashboard', BICB_DIR_URL . 'build/admin/dashboard.js', array_merge( $asset_file['dependencies'], [ 'wp-util' ] ), BICB_VERSION, true );
					wp_set_script_translations( 'bicb-admin-dashboard', 'b-carousel-block', BICB_DIR_PATH . 'languages' );
				}
			}

			/**
			 * Enqueues inline script data for the block editor.
			 *
			 * @since 1.0.0
			 *
			 * @return void
			 */
			public function enqueueBlockEditorAssets(){
				wp_add_inline_script( 'bicb-carousel-editor-script', 'const bicbpricingurl = "'. admin_url( 'tools.php?page=carousel-block#/pricing' ) .'";', 'before' );
			}

			/**
			 * Renders the admin dashboard page markup.
			 *
			 * @since 1.0.0
			 *
			 * @return void
			 */
			public static function renderDashboard(){ ?>
				<div
					id='bicbDashboard'
					data-info='<?php echo esc_attr( wp_json_encode( [
						'version' => BICB_VERSION,
						'adminUrl' => admin_url(),
						'nonce' => wp_create_nonce( 'bicbCreatePage' ),
						'licenseActiveNonce' => wp_create_nonce( 'bPlLicenseActivation' )
					] ) ); ?>'
				></div>
			<?php }
		}
		new BICBPlugin;
	}
}