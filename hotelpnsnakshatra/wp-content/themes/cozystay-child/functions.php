<?php
	add_action( 'wp_enqueue_scripts', 'cozystay_child_enqueue_scripts' );
	function cozystay_child_enqueue_scripts(){
		wp_enqueue_style( 'cozystay-child-theme-style', get_stylesheet_uri(), array( 'cozystay-theme-style' ) );
	}

	add_filter( 'cozystay_front_inline_styles_handler', 'cozystay_child_inline_style_handler', 999 );
	function cozystay_child_inline_style_handler( $handler ) {
		return 'cozystay-child-theme-style';
	}
