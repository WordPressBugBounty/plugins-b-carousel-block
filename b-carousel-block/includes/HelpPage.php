<?php
class BICBHelpPage{
	public function __construct(){
		add_action( 'admin_menu', [$this, 'adminMenu'] );
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
	}

	function adminMenu(){
		add_submenu_page(
			'edit.php?post_type=bicb',
			__( 'Content Slider - Help', 'carousel-block' ),
			__( 'Help', 'carousel-block' ),
			'manage_options',
			'bicb-help',
			[$this, 'helpPage']
		);
	}

	function helpPage(){ ?>
		<div id='bplAdminHelpPage'></div>
	<?php }

	function adminEnqueueScripts( $hook ) {
		if( strpos( $hook, 'bicb-help' ) ){
			wp_enqueue_style( 'bicb-admin-help', BICB_DIR_URL . 'build/admin-help.css', [], BICB_VERSION );
			wp_enqueue_script( 'bicb-admin-help', BICB_DIR_URL . 'build/admin-help.js', [ 'react', 'react-dom' ], BICB_VERSION, true );
			wp_set_script_translations( 'bicb-admin-help', 'carousel-block', BICB_DIR_PATH . 'languages' );
		}
	}
}
new BICBHelpPage;