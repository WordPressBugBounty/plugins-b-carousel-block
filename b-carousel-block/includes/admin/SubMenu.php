<?php
namespace BICB\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * SubMenu class.
 *
 * Registers the Carousel Block admin submenu page under Tools.
 *
 * @since 1.0.0
 * @package BICB\Admin
 */
class SubMenu {
	/**
	 * Constructor.
	 *
	 * Hooks admin menu registration.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	/**
	 * Registers the admin submenu page under Tools.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function adminMenu(){
		add_submenu_page(
			'tools.php',
			__('Carousel Block - bPlugins', 'b-carousel-block'),
			__('Carousel Block', 'b-carousel-block'),
			'manage_options',
			'carousel-block',
			[ \BICBPlugin::class, 'renderDashboard' ]
		);
	}
}
new SubMenu();