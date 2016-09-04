<?php

/**
 * Helper function to fetch default featured iamge.
 *
 * @since 1.0.0
 *
 * @param string $size Image size to return.
 * @return string
 */
function wds_get_default_featured_image_url( $size = 'thumbnail' ) {

	$featured = new WDS_Get_Default_Featured_Image( $size );
	return $featured->url;

}
