<?php
/**
 * Plugin Name: WDS Default Featured Images
 * Plugin URI:  http://webdevstudios.com
 * Description: Allows you to select a default image as all featured images where none is set.
 * Version:     1.0.0
 * Author:      WebDevStudios
 * Author URI:  http://webdevstudios.com
 * Donate link: http://webdevstudios.com
 * License:     GPLv2
 * Text Domain: wds-default-featured-images
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */

/**
 * Main initiation class
 *
 * @since  1.0.0
 * @var  string $version  Plugin version
 * @var  string $basename Plugin basename
 * @var  string $url      Plugin URL
 * @var  string $path     Plugin Path
 */
class WDS_Default_Featured_Images {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  1.0.0
	 */
	const VERSION = '1.0.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $url      = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $path     = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var WDS_Default_Featured_Images
	 * @since  1.0.0
	 */
	protected static $single_instance = null;

	/**
	 * Option key, and option page slug
	 * @var string
	 */
	private $key = 'wds_default_featured_images';

	/**
	 * Options page metabox id
	 * @var string
	 */
	private $metabox_id = 'wds_default_featured_images_mb';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  1.0.0
	 * @return WDS_Default_Featured_Images A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  1.0.0
	 * @return  null
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->includes();
		$this->hooks();
	}

	public function includes() {
		require_once( $this->path . 'includes/class-wds-get-default-featured-image.php' );
		require_once( $this->path . 'includes/helper-functions.php' );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 1.0.0
	 * @return null
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_filter( 'post_thumbnail_html', array( $this, 'post_thumbnail' ), 10, 5 );
		add_action( 'cmb2_init', array( $this, 'add_options_page_metabox' ) );
	}

	/**
	 * Init hooks
	 *
	 * @since  1.0.0
	 * @return null
	 */
	public function init() {
		load_plugin_textdomain( 'wds-default-featured-images', false, dirname( $this->basename ) . '/languages/' );
	}

	/**
	 * Register our settings on admin_init
	 */
	public function admin_init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add the options page for our settings
	 */
	public function add_options_page() {

		$this->options_page = add_options_page( __( 'Default Featured Image', 'wds-default-featured-images' ), __( 'Default Featured Image', 'wds-default-featured-images' ), 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		// Include CMB CSS in the head to avoid FOUT
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );

	}

	/**
	 * Markup to display on our options page
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key, array( 'cmb_styles' => false ) ); ?>
		</div>
	<?php
	}

	/**
	 * Add CMB2 Metaboxes
	 */
	function add_options_page_metabox() {
		$cmb = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
		// Set our CMB2 fields
		$cmb->add_field( array(
			'name' => __( 'Select Default Image', 'wds-default-featured-images' ),
			'id'   => 'image',
			'type' => 'file',
		) );

		$cmb->add_field( array(
			'name'    => __( 'Use Placeholder Image Site', 'wds-default-featured-images' ),
			'desc'    => __( 'Select the site you wish to use and we will grab an image from there! If an image above is selected, that will always override this.', 'wds-default-featured-images' ),
			'id'      => 'site',
			'type'    => 'radio',
			'show_option_none' => 'None',
			'options' => array(
				'placeimg'    => __( 'PlaceIMG', 'wds-default-featured-images' ),
				'placeholdit' => __( 'PlaceHold.it', 'wds-default-featured-images' ),
				'lorempixel'  => __( 'lorempixel', 'wds-default-featured-images' ),
				'placekitten' => __( 'placekitten', 'wds-default-featured-images' ),
				'fillmurray'  => __( 'Fill Murray', 'wds-default-featured-images' ),
				'nicenicejpg' => __( 'NiceNiceJPG', 'wds-default-featured-images' ),
				'baconmockup' => __( 'baconmockup', 'wds-default-featured-images' ),
				'placecage'   => __( 'PlaceCage', 'wds-default-featured-images' ),
			),
		) );
		
	}

	/**
	 * Hook in and add images for nonexistant post thumbnails.
	 */
	public function post_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {

		if ( ! $html ) {

			$featured_image = new WDS_Get_Default_Featured_Image( $size );
			$html = $featured_image->html;

		}

		return $html;
	}



}

/**
 * Grab the WDS_Default_Featured_Images object and return it.
 * Wrapper for WDS_Default_Featured_Images::get_instance()
 *
 * @since  1.0.0
 * @return WDS_Default_Featured_Images  Singleton instance of plugin class.
 */
function wds_default_featured_images() {
	return WDS_Default_Featured_Images::get_instance();
}

// Kick it off
wds_default_featured_images();