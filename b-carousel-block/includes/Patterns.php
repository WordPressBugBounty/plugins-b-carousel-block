<?php
namespace BICB;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Patterns class.
 *
 * Registers block patterns and pattern categories for the Carousel block.
 *
 * @since 1.0.6
 * @package BICB
 */
class Patterns{
	/**
	 * Constructor.
	 *
	 * Hooks pattern registration to the init action.
	 */
	public function __construct(){
		add_action( 'init', [$this, 'onInit'] );
	}

	/**
	 * Registers block pattern category and individual patterns from JSON.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function onInit(){
		$patterns = wp_json_file_decode( __DIR__ . '/patterns.json', [ 'associative' => true ] );

		// Register Pattern Category
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category( 'bicbPattern', [ 'label' => __( 'Carousel', 'b-carousel-block' ) ] );
		}

		// Register Pattern
		if ( !empty( $patterns ) ) {
			foreach ( $patterns as $pattern ) {
				if ( function_exists( 'register_block_pattern' ) ) {
					register_block_pattern( $pattern['name'], [
						'title'			=> $pattern['title'],
						'content'		=> $pattern['content'],
						'description'	=> $pattern['description'],
						'categories'	=> [ 'bicbPattern' ],
						'keywords'		=> $pattern['keywords'],
						'blockTypes'	=> $pattern['blockTypes'],
						'viewportWidth'	=> 1200
					] );
				}
			}
		}
	}
}
new Patterns();