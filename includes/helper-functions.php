<?php
function wds_get_default_featured_image_url( $size = 'thumbnail' ) {

	$featured = new WDS_Get_Default_Featured_Image( $size );
	return $featured->url;

}