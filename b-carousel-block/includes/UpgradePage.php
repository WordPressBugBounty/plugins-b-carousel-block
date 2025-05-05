<?php
class BICBUpgradePage{
	public function __construct(){
		add_action( 'admin_menu', [$this, 'adminMenu'] );
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
	}

	function adminMenu(){
		add_submenu_page(
			'edit.php?post_type=bicb',
			__( 'Content Slider - Upgrade', 'carousel-block' ),
			__( 'Upgrade', 'carousel-block' ),
			'manage_options',
			'bicb-upgrade',
			[$this, 'upgradePage']
		);
	}

	function upgradePage(){ ?>
		<div id='bplUpgradePage'></div>
	<?php }

	function adminEnqueueScripts( $hook ) {
		if( strpos( $hook, 'bicb-upgrade' ) ){
			wp_enqueue_script( 'bicb-admin-upgrade', BICB_DIR_URL . 'build/admin-upgrade.js', [ 'react', 'react-dom' ], BICB_VERSION, true );
			wp_set_script_translations( 'bicb-admin-upgrade', 'carousel-block', BICB_DIR_PATH . 'languages' );
		}
	}
}
new BICBUpgradePage;