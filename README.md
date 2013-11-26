CPT_Core
=========

A tool to make custom post type registration just a bit simpler. Automatically registers post type labels and messages, and provides helpful methods.

Future version will include a method for registering the custom icons for wp-admin (vs the default pin icon).

#### Example Usage:
```php
<?php

/**
 * Load CPT_Core.
 */
require_once 'CPT_Core/CPT_Core.php';

/**
 * Will register a 'Q & A' CPT
 */
register_via_cpt_core( 'Q & A' );

/**
 * OR create a CPT child class for utilizing built-in methods, like CPT_Core::columns, and CPT_Core::columns_display
 */
class Slider_CPT extends CPT_Core {

	/**
	 * Register Custom Post Types. See documentation in CPT_Core, and in wp-includes/post.php
	 */
	public function __construct() {

		// Register this cpt
		parent::__construct( 'Slider', array(
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		) );

	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 * @since  0.1.0
	 * @param  array  $columns Array of registered column names/labels
	 * @return array           Modified array
	 */
	public function columns( $columns ) {
		$new_column = array(
			'slide_img' => sprintf( __( '%s Image', '_s' ), $this->post_type( 'singular' ) ),
		);
		return array_merge( $new_column, $columns );
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 * @since  0.1.0
	 * @param  array  $column Array of registered column names
	 */
	public function columns_display( $column ) {
		switch ( $column ) {
			case 'slide_img':
				the_post_thumbnail();
				break;
		}
	}

}
new Slider_CPT();
```
