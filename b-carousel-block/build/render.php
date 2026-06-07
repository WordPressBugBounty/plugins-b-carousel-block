<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

$id = wp_unique_id( 'bicbCarousel-' );
?>
<div
	<?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped internally by get_block_wrapper_attributes(). ?>
	id='<?php echo esc_attr( $id ); ?>'
	data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ?? [] ) ); ?>'
></div>