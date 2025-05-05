<?php
class BICBCustomPost{
	public $post_type = 'bicb';

	public function __construct(){
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
		add_action( 'init', [$this, 'onInit'] );
		add_shortcode( 'bicb', [$this, 'onAddShortcode'] );
		add_filter( 'manage_bicb_posts_columns', [$this, 'manageBICBPostsColumns'], 10 );
		add_action( 'manage_bicb_posts_custom_column', [$this, 'manageBICBPostsCustomColumns'], 10, 2 );
		add_action( 'use_block_editor_for_post', [$this, 'useBlockEditorForPost'], 999, 2 );
	}

	function adminEnqueueScripts( $hook ){
		if( 'edit.php' === $hook || 'post.php' === $hook ){
			wp_enqueue_style( 'bicb-admin-post', BICB_DIR_URL . 'build/admin-post.css', [], BICB_VERSION );
			wp_enqueue_script( 'bicb-admin-post', BICB_DIR_URL . 'build/admin-post.js', [], BICB_VERSION, true );
			wp_set_script_translations( 'bicb-admin-post', 'carousel-block', BICB_DIR_PATH . 'languages' );
		}
	}

	function onInit(){
		$menuIcon = "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='#fff'><path d='M4 19h2c0 1.103.897 2 2 2h8c1.103 0 2-.897 2-2h2c1.103 0 2-.897 2-2V7c0-1.103-.897-2-2-2h-2c0-1.103-.897-2-2-2H8c-1.103 0-2 .897-2 2H4c-1.103 0-2 .897-2 2v10c0 1.103.897 2 2 2zM20 7v10h-2V7h2zM8 5h8l.001 14H8V5zM4 7h2v10H4V7z' /></svg>";

		register_post_type( 'bicb', [
			'labels'				=> [
				'name'			=> __( 'B Carousel', 'carousel-block'),
				'singular_name'	=> __( 'B Carousel', 'carousel-block' ),
				'add_new'		=> __( 'Add New', 'carousel-block' ),
				'add_new_item'	=> __( 'Add New', 'carousel-block' ),
				'edit_item'		=> __( 'Edit', 'carousel-block' ),
				'new_item'		=> __( 'New', 'carousel-block' ),
				'view_item'		=> __( 'View', 'carousel-block' ),
				'search_items'	=> __( 'Search', 'carousel-block'),
				'not_found'		=> __( 'Sorry, we couldn\'t find the that you are looking for.', 'carousel-block' )
			],
			'public'				=> false,
			'show_ui'				=> true, 		
			'show_in_rest'			=> true,							
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'menu_position'			=> 14,
			'menu_icon'				=> 'data:image/svg+xml;base64,' . base64_encode( $menuIcon ),		
			'has_archive'			=> false,
			'hierarchical'			=> false,
			'capability_type'		=> 'page',
			'rewrite'				=> [ 'slug' => 'bicb' ],
			'supports'				=> [ 'title', 'editor' ],
			'template'				=> [ ['bicb/carousel'] ],
			'template_lock'			=> 'all'
		]); // Register Post Type
	}

	function onAddShortcode( $atts ) {
		$post_id = $atts['id'];
		$post = get_post( $post_id );

		if ( !$post ) {
			return '';
		}

		if ( post_password_required( $post ) ) {
			return get_the_password_form( $post );
		}

		switch ( $post->post_status ) {
			case 'publish':
				return $this->displayContent( $post );

			case 'private':
				if (current_user_can('read_private_posts')) {
					return $this->displayContent( $post );
				}
				return '';

			case 'draft':
			case 'pending':
			case 'future':
				if ( current_user_can( 'edit_post', $post_id ) ) {
					return $this->displayContent( $post );
				}
				return '';

			default:
				return '';
		}
	}

	function displayContent( $post ){
		$blocks = parse_blocks( $post->post_content );

		global $allowedposttags;
		$allowed_html = wp_parse_args( ['style' => [] ], $allowedposttags );

		return wp_kses( render_block( $blocks[0] ), $allowed_html );
	}

	function manageBICBPostsColumns( $defaults ) {
		unset( $defaults['date'] );
		$defaults['shortcode'] = 'ShortCode';
		$defaults['date'] = 'Date';
		return $defaults;
	}

	function manageBICBPostsCustomColumns( $column_name, $post_ID ) {
		if ( $column_name == 'shortcode' ) {
			echo '<div class="bPlAdminShortcode" id="bPlAdminShortcode-' . esc_attr( $post_ID ) . '">
				<input value="[bicb id=' . esc_attr( $post_ID ) . ']" onclick="copyBPlAdminShortcode(\'' . esc_attr( $post_ID ) . '\')">
				<span class="tooltip">' . esc_html__( 'Copy To Clipboard' ) . '</span>
			</div>';
		}
	}

	function useBlockEditorForPost($use, $post){
		if ( $this->post_type === $post->post_type ) {
			return true;
		}
		return $use;
	}

	function orderSubMenu( $menu_ord ){
		global $submenu;

		$sMenu = $submenu['edit.php?post_type=bicb'] ?? [];
		$arr = [];
		if( bicbIsPremium() ){
			if( isset( $sMenu[5] ) ){
				$arr[] = $sMenu[5]; // Carousel
			}
			if( isset( $sMenu[10] ) ){
				$arr[] = $sMenu[10]; // Add New
			}
		}
		if( isset( $sMenu[11] ) ){
			$arr[] = $sMenu[11]; // Help
		}
		if( ( !bicbIsPremium() || BICB_HAS_PRO ) && isset( $sMenu[12] ) ){
			$arr[] = $sMenu[12]; // Upgrade || Pricing || Account
		}
		$submenu['edit.php?post_type=bicb'] = $arr;
	
		return $menu_ord;
	}
}
new BICBCustomPost();