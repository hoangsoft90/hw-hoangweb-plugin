<?php
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box_Contact.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box_Display_Feed.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box_Display_Web.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box_Optin.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box_Pool.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Meta_Box_Relatedness.php');

global $hw_yarpp;

add_meta_box(
    'hw_yarpp_pool',
    __( '"The Pool"', 'hw-yarpp' ),
    array(new HW_YARPP_Meta_Box_Pool, 'display'),
    'settings_page_hw_yarpp',
    'normal',
    'core'
);

add_meta_box(
    'hw_yarpp_relatedness',
    __( '"Relatedness" options', 'hw-yarpp' ),
    array(
        new HW_YARPP_Meta_Box_Relatedness,
        'display'
    ),
    'settings_page_hw_yarpp',
    'normal',
    'core'
);

add_meta_box(
    'hw_yarpp_display_web',
    __('Display options <small>for your website</small>', 'hw-yarpp'),
    array(
        new HW_YARPP_Meta_Box_Display_Web,
        'display'
    ),
    'settings_page_hw_yarpp',
    'normal',
    'core'
);

add_meta_box(
    'hw_yarpp_display_rss',
    __('Display options <small>for RSS</small>', 'hw-yarpp'),
    array(
        new HW_YARPP_Meta_Box_Display_Feed,
        'display'
    ),
    'settings_page_hw_yarpp',
    'normal',
    'core'
);
/*
if (!$hw_yarpp->yarppPro['active']) {
    add_meta_box(
        'hw_yarpp_display_optin',
        'Get the Most Out of YARPP',
        array(
            new HW_YARPP_Meta_Box_Optin,
            'display'
        ),
        'settings_page_hw_yarpp',
        'side',
        'core'
    );
}
*/
add_meta_box(
    'hw_yarpp_display_contact',
    __('Contact YARPP', 'hw-yarpp'),
    array(new HW_YARPP_Meta_Box_Contact, 'display'),
    'settings_page_hw_yarpp',
    'side',
    'core'
);


function hw_yarpp_make_optin_classy($classes) {
	if (!hw_yarpp_get_option('optin') )
		$classes[] = 'hw_yarpp_attention';
	return $classes;
}

add_filter(
    "postbox_classes_settings_page_hw_yarpp_yarpp_display_optin",
    'hw_yarpp_make_optin_classy'
);

/** @since 3.3: hook for registering new YARPP meta boxes */
//do_action('add_meta_boxes_settings_page_yarpp');