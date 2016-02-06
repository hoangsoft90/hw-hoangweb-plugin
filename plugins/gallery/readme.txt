using envira-gallery-lite plugin.

// Load the main plugin class.
$gallery = Envira_Gallery_Lite::get_instance();

//$gallery_id maybe post id, slug
$data       = is_preview() ? $gallery->_get_gallery( $gallery_id ) : $gallery->get_gallery( $gallery_id );      //post id
