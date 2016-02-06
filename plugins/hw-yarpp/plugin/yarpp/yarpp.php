<?php

if(!defined('WP_CONTENT_URL')) define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if(!defined('WP_CONTENT_DIR')){
    $tr = get_theme_root();
    define('WP_CONTENT_DIR', substr($tr,0,strrpos($tr,'/')));
}

define('HW_YARPP_VERSION', '4.2.5');
define('HW_YARPP_DIR', dirname(__FILE__));
define('HW_YARPP_URL', plugins_url('',__FILE__));
define('HW_YARPP_NO_RELATED', ':(');
define('HW_YARPP_RELATED', ':)');
define('HW_YARPP_NOT_CACHED', ':/');
define('HW_YARPP_DONT_RUN', 'X(');

/*----------------------------------------------------------------------------------------------------------------------
Sice v3.2: YARPP uses it own cache engine, which uses custom db tables by default.
Use postmeta instead to avoid custom tables by un-commenting postmeta line and comment out the tables one.
----------------------------------------------------------------------------------------------------------------------*/
/* Enable postmeta cache: */
//if(!defined('HW_YARPP_CACHE_TYPE')) define('HW_YARPP_CACHE_TYPE', 'postmeta');

/* Enable Yarpp cache engine - Default: */
if(!defined('HW_YARPP_CACHE_TYPE')) define('HW_YARPP_CACHE_TYPE', 'tables');

/* Load proper cache constants */
switch(HW_YARPP_CACHE_TYPE){
    case 'tables':
        define('HW_YARPP_TABLES_RELATED_TABLE', 'hw_yarpp_related_cache');
        break;
    case 'postmeta':
        define('HW_YARPP_POSTMETA_KEYWORDS_KEY', '_hw_yarpp_keywords');
        define('HW_YARPP_POSTMETA_RELATED_KEY',  '_hw_yarpp_related');
        break;
}

/* New in 3.5: Set YARPP extra weight multiplier */
if(!defined('HW_YARPP_EXTRA_WEIGHT')) define('HW_YARPP_EXTRA_WEIGHT', 3);

/* Includes ----------------------------------------------------------------------------------------------------------*/
include_once(HW_YARPP_DIR.'/includes/init_functions.php');
include_once(HW_YARPP_DIR.'/includes/related_functions.php');
include_once(HW_YARPP_DIR.'/includes/template_functions.php');

include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Core.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Widget.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Cache.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Cache_Bypass.php');
include_once(HW_YARPP_DIR.'/classes/HW_YARPP_Cache_'.ucfirst(HW_YARPP_CACHE_TYPE).'.php');

/* WP hooks ----------------------------------------------------------------------------------------------------------*/
add_action('init', 'hw_yarpp_init');
add_action('activate_'.plugin_basename(__FILE__), 'hw_yarpp_plugin_activate', 10, 1);
