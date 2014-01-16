<?php
/**
 * Plugin class for generating Custom Post Types.
 * @version 0.2.0
 * @todo    Fix cpt_icons method
 * @author  Justin Sternberg
 */
class CPT_Core {

	/**
	 * Singlur CPT label
	 * @var string
	 */
	private $singular;

	/**
	 * Plural CPT label
	 * @var string
	 */
	private $plural;

	/**
	 * Registered CPT name/slug
	 * @var string
	 */
	private $post_type;

	/**
	 * Optional argument overrides passed in from the constructor.
	 * @var array
	 */
	private $arg_overrides = array();

	/**
	 * All CPT registration arguments
	 * @var array
	 */
	private $cpt_args = array();

	/**
	 * An array of each CPT_Core object registered with this class
	 * @var array
	 */
	private static $custom_post_types = array();

	/**
	 * Constructor. Builds our CPT.
	 * @since 0.1.0
	 * @param mixed  $cpt           Singular CPT name, or array with Singular, Plural, and Registered
	 * @param array  $arg_overrides CPT registration override arguments
	 */
	public function __construct( $cpt, $arg_overrides = array() ) {

		if( ! $cpt ) // If they passed in false or something odd
			wp_die( 'Post type required for the first parameter in CPT_Core.' );

		if ( is_string( $cpt ) ) {
			$this->singular  = $cpt;
			$this->plural    = $cpt .'s';
			$this->post_type = sanitize_title( $this->plural );
		} elseif ( is_array( $cpt ) && $cpt[0] ) {
			$this->singular  = $cpt[0];
			$this->plural    = !isset( $cpt[1] ) || !is_string( $cpt[1] ) ? $cpt[0] .'s' : $cpt[1];
			$this->post_type = !isset( $cpt[2] ) || !is_string( $cpt[2] ) ? sanitize_title( $this->plural ) : $cpt[2];
		} else {
			// Something went wrong.
			wp_die( 'There was an error with the custom post type in CPT_Core.' );
		}

		$this->arg_overrides = (array) $arg_overrides;

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
		add_filter( 'manage_edit-'. $this->post_type .'_columns', array( $this, 'columns' ) );
		// Different column registration for pages/posts
		$h = isset( $arg_overrides['hierarchical'] ) && $arg_overrides['hierarchical'] ? 'pages' : 'posts';
		add_action( "manage_{$h}_custom_column", array( $this, 'columns_display' ) );
		add_filter( 'enter_title_here', array( $this, 'title' ) );
		// add_action( 'admin_head', array( $this, 'cpt_icons' ) );
	}

	/**
	 * Gets the passed in arguments combined with our defaults.
	 * @since  0.2.0
	 * @return array  CPT arguments array
	 */
	public function get_args() {
		if ( ! empty( $this->cpt_args ) )
			return $this->cpt_args;

		// Generate CPT labels
		$labels = array(
			'name'               => $this->plural,
			'singular_name'      => $this->singular,
			'add_new'            => sprintf( __( 'Add New %s' ), $this->singular ),
			'add_new_item'       => sprintf( __( 'Add New %s' ), $this->singular ),
			'edit_item'          => sprintf( __( 'Edit %s' ), $this->singular ),
			'new_item'           => sprintf( __( 'New %s' ), $this->singular ),
			'all_items'          => sprintf( __( 'All %s' ), $this->plural ),
			'view_item'          => sprintf( __( 'View %s' ), $this->singular ),
			'search_items'       => sprintf( __( 'Search %s' ), $this->plural ),
			'not_found'          => sprintf( __( 'No %s' ), $this->plural ),
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash' ), $this->plural ),
			'parent_item_colon'  => isset( $this->arg_overrides['hierarchical'] ) && $this->arg_overrides['hierarchical'] ? sprintf( __( 'Parent %s:' ), $this->singular ) : null,
			'menu_name'          => $this->plural,
		);

		// Set default CPT parameters
		$defaults = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'has_archive'        => true,
			'supports'           => array( 'title', 'editor', 'excerpt' ),
		);

		$this->cpt_args = wp_parse_args( $this->arg_overrides, $defaults );
		return $this->cpt_args;
	}

	/**
	 * Actually registers our CPT with the merged arguments
	 * @since  0.1.0
	 */
	public function register_post_type() {
		// Register our CPT
		$args = register_post_type( $this->post_type, $this->get_args() );
		// If error, yell about it.
		if ( is_wp_error( $args ) )
			wp_die( $args->get_error_message() );

		// Success. Set args to what WP returns
		$this->cpt_args = $args;

		// Add this post type to our custom_post_types array
		self::$custom_post_types[ $this->post_type ] = $this;
	}

	/**
	 * Modies CPT based messages to include our CPT labels
	 * @since  0.1.0
	 * @param  array  $messages Array of messages
	 * @return array            Modied messages array
	 */
	public function messages( $messages ) {
		global $post, $post_ID;

		$messages[$this->singular] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( '%1$s updated. <a href="%2$s">View %1$s</a>' ), $this->singular, esc_url( get_permalink( $post_ID ) ) ),
			2 => __( 'Custom field updated.' ),
			3 => __( 'Custom field deleted.' ),
			4 => sprintf( __( '%1$s updated.' ), $this->singular ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s' ), $this->singular , wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%1$s published. <a href="%2$s">View %1$s</a>' ), $this->singular, esc_url( get_permalink( $post_ID ) ) ),
			7 => sprintf( __( '%1$s saved.' ), $this->singular ),
			8 => sprintf( __( '%1$s submitted. <a target="_blank" href="%2$s">Preview %1$s</a>' ), $this->singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			9 => sprintf( __( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %1$s</a>' ), $this->singular,
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			10 => sprintf( __( '%1$s draft updated. <a target="_blank" href="%2$s">Preview %1$s</a>' ), $this->singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);
		return $messages;

	}

	/**
	 * Registers admin columns to display. To be overridden by an extended class.
	 * @since  0.1.0
	 * @param  array  $columns Array of registered column names/labels
	 * @return array           Modified array
	 */
	public function columns( $columns ) {
		// placeholder
		return $columns;
	}

	/**
	 * Handles admin column display. To be overridden by an extended class.
	 * @since  0.1.0
	 * @param  array  $column Array of registered column names
	 */
	public function columns_display( $column ) {
		// placeholder
	}

	/**
	 * Filter CPT title entry placeholder text
	 * @since  0.1.0
	 * @param  string $title Original placeholder text
	 * @return string        Modifed placeholder text
	 */
	public function title( $title ){

		$screen = get_current_screen();
		if ( isset( $screen->post_type ) && $screen->post_type == $this->post_type )
			return sprintf( __( '%s Title' ), $this->singular );

		return $title;
	}

	/**
	 * @todo Create a good method for finding & adding CPT images.
	 * Maybe using WP 3.8 icon font?
	 */
	function cpt_icons() {
		$screen = get_current_screen()->id;
		$file   = 'lib/css/'. $this->post_type .'.png';
		$path   = 'lib/css/'. $this->post_type .'.png';
		$img    = file_exists( $file ) ? $path : null;

?>
<style type="text/css">
	#adminmenu li.menu-top:hover .wp-menu-image img,
	#adminmenu li.wp-has-current-submenu .wp-menu-image img {
		top: 0;
	}
	<?php

	if ( $screen == 'edit-'. $this->post_type || $screen == $this->post_type ) {
		$file = 'lib/css/'. $this->post_type .'32.png';
		$path = 'lib/css/'. $this->post_type .'32.png';
		if ( file_exists( $file ) ) {
	?>#icon-edit.icon32.icon32-posts-<?php echo $this->post_type; ?> {
		background-position: 0 0;
		background-image: url('<?php echo $path; ?>');
	}
	<?php

		}

	}
	?>#menu-posts-<?php $this->post_type; ?> .wp-menu-image a {
		overflow: hidden;
	}
	#adminmenu .menu-icon-<?php echo $this->post_type; ?> div.wp-menu-image {
		overflow: hidden;
	}
	#menu-posts-<?php $this->post_type; ?> .wp-menu-image img {
		opacity: 1;
		filter: alpha(opacity=100);
		position: relative;
		top: -24px;
	}
</style>
<?php
	}

	/**
	 * Provides access to private class properties.
	 * @since  0.2.0
	 * @param  boolean $key Specific CPT parameter to return
	 * @return mixed        Specific CPT parameter or array of singular, plural and registered name
	 */
	public function post_type( $key = 'post_type' ) {

		return isset( $this->$key ) ? $this->$key : array(
			'singular'  => $this->singular,
			'plural'    => $this->plural,
			'post_type' => $this->post_type,
		);
	}

	/**
	 * Provides access to all CPT_Core taxonomy objects registered via this class.
	 * @since  0.1.0
	 * @param  string $post_type Specific CPT_Core object to return, or 'true' to specify only names.
	 * @return mixed             Specific CPT_Core object or array of all
	 */
	public function custom_post_types( $post_type = '' ) {
		if ( $post_type === true && ! empty( self::$custom_post_types ) ) {
			return array_keys( self::$custom_post_types );
		}
		return isset( self::$custom_post_types[ $post_type ] ) ? self::$custom_post_types[ $post_type ] : self::$custom_post_types;
	}

	/**
	 * Magic method that echos the CPT registered name when treated like a string
	 * @since  0.2.0
	 * @return string CPT registered name
	 */
	public function __toString() {
		return $this->post_type();
	}
}

if ( !function_exists( 'register_via_cpt_core' ) ) {
	/**
	 * Helper function to register a CPT via the CPT_Core class. An extended class is preferred.
	 * @since 0.2.0
	 * @param mixed     $cpt           Singular CPT name, or array with Singular, Plural, and Registered
	 * @param array     $arg_overrides CPT registration override arguments
	 * @return CPT_Core                An instance of the class.
	 */
	function register_via_cpt_core( $cpt, $arg_overrides = array() ) {
		return new CPT_Core( $cpt, $arg_overrides );
	}
}
