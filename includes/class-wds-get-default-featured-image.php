<?php

/**
 * Class WDS_Get_Default_Featured_Image
 *
 * @since 1.0.0
 */
class WDS_Get_Default_Featured_Image {

	/**
	 * Image size.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $image_size = '';

	/**
	 * Post ID.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $post_id = 0;

	/**
	 * Image width, in pixels.
	 *
	 * @var int|mixed
	 * @since 1.0.0
	 */
	public $width = 0;

	/**
	 * Image height, in pixels.
	 *
	 * @var int|mixed
	 * @since 1.0.0
	 */
	public $height = 0;

	/**
	 * Site property.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $site = '';

	/**
	 * Image ID.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $image_id = 0;

	/**
	 * Image URL.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $url = '';

	/**
	 * HTML output.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $html = '';

	/**
	 * WDS_Get_Default_Featured_Image constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $image_size Image size to retrieve.
	 */
	public function __construct( $image_size = 'thumbnail' ) {

		$option = get_option( 'wds_default_featured_images' );

		$this->image_size = $image_size;

		$size         = $this->get_image_sizes( $this->image_size );
		$this->width  = $size['width'];
		$this->height = $size['height'];

		$this->site     = isset( $option['site'] ) ? $option['site'] : '';
		$this->image_id = isset( $option['image_id'] ) ? $option['image_id'] : 0;

		$this->url  = $this->get_url();
		$this->html = $this->get_html();

	}

	/**
	 * Return the image URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_url() {

		if ( ! $this->image_id ) {
			return $this->get_placeholder_site_url( $this->site, $this->width, $this->height );
		}

		$image = wp_get_attachment_image_src( $this->image_id, $this->image_size );
		return $image[0];

	}

	/**
	 * Return the image HTML.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_html() {

		if ( ! $this->image_id ) {

			$url = $this->get_placeholder_site_url( $this->site, $this->width, $this->height );

			$html = '';
			if ( $url ) {
				$hwstring   = image_hwstring( $this->width, $this->height );
				$size_class = $this->image_size;
				if ( is_array( $size_class ) ) {
					$size_class = join( 'x', $size_class );
				}

				$attr = array(
					'src'   => esc_url( $url ),
					'class' => "attachment-$size_class",
					'alt'   => __( 'This is just a placeholder image', 'wds-default-featured-images' ),
				);

				/**
				 * Filter the list of attachment image attributes.
				 *
				 * @since 2.8.0
				 *
				 * @param array        $attr       Attributes for the image markup.
				 * @param WP_Post      $attachment Image attachment post.
				 * @param string|array $size       Requested size.
				 */
				$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, null, $this->image_size );
				$attr = array_map( 'esc_attr', $attr );
				$html = rtrim("<img $hwstring");
				foreach ( $attr as $name => $value ) {
					$html .= " $name=" . '"' . $value . '"';
				}
				$html .= ' />';
			}

		} else {
			$html = wp_get_attachment_image( $this->image_id, $this->image_size );
		}

		return $html;

	}

	/**
	 * Return the URL for the default placeholder.
	 *
	 * @since 1.0.0
	 *
	 * @param string $site   Default placeholder site name.
	 * @param int    $width  Default width in pixels.
	 * @param int    $height Default height in pixels.
	 * @return string
	 */
	public function get_placeholder_site_url( $site = 'placeholdit', $width = 150, $height = 150 ) {
		return get_stylesheet_directory_uri() . '/images/placeholder.png';
	}

	/**
	 * Return image sizes including height and width.
	 *
	 * @since 1.0.0
	 *
	 * @param string $size Thumbnail size.
	 * @return array $sizes Array of sizes.
	 */
	function get_image_sizes( $size = '' ) {

		global $_wp_additional_image_sizes;

		$sizes = array();
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info.
		foreach( $get_intermediate_image_sizes as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

				$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
				$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
				$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

				$sizes[ $_size ] = array(
					'width' => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
				);

			}

		}

		// Get only 1 size if found.
		if ( $size ) {

			if( isset( $sizes[ $size ] ) ) {
				return $sizes[ $size ];
			} else {
				return false;
			}

		}

		return $sizes;
	}

}
