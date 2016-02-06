<?php
//load encyptor lib
if(class_exists('HW_HOANGWEB')) {
    HW_HOANGWEB::load_class('HW_Encryptor');
}

if(!function_exists('__save_session')) {
    function __save_session($key,$val, $merge_array= false,$group = 'hoangweb'){
        HW_SESSION::__save_session($key,$val, $merge_array, $group);
    }
    function __reset_session() {
        @session_start();
        if(isset($_SESSION['hoangweb'])) unset($_SESSION['hoangweb']);
    }
}


/**
 * enqueue stuffs in admin
 */
add_action('admin_enqueue_scripts', '_hw_help_admin_enqueue_scripts');
function _hw_help_admin_enqueue_scripts(){
    wp_enqueue_style('hw-help-css', HW_HELP_URL.'/assets/style.css');
    wp_enqueue_script('hw-help-script', HW_HELP_URL.'/assets/help-js.js');

    //enqueue syntax highlighter library in admin
    if(class_exists('HW_Libraries',false)) {
        HW_Libraries::enqueue_jquery_libs('syntaxhighlighter_3.0.83');
        HW_Libraries::get('syntaxhighlighter_3.0.83')->enqueue_scripts('shBrushJScript.js','shBrushXml.js','shBrushPhp.js');
        //colorbox
        HW_Libraries::enqueue_jquery_libs('jquery-colorbox');
    }
}

/**
 * add ajax action for help popup content
 */
add_action('HW_hw_help_popup',  '_hw_help_popup_ajax',10,2);
add_action('HW_nopriv_hw_help_popup',  '_hw_help_popup_ajax',10,2);
/**
 * require user logined
 * @hw_hook action HW_hw_help_popup
 */
function _hw_help_popup_ajax($before_content_cb, $after_content_cb = null) {
    if(!isset($_REQUEST['file'])) return;
    /**
     * load new hooks in wp_head
     */
    //enqueue syntax highlighter library
    if(class_exists('HW_Libraries',false)) {
        HW_Libraries::enqueue_jquery_libs('syntaxhighlighter_3.0.83');
        HW_Libraries::get('syntaxhighlighter_3.0.83')->enqueue_scripts('shBrushJScript.js','shBrushXml.js','shBrushPhp.js');
    }
    if(is_callable($before_content_cb)) call_user_func($before_content_cb);

    echo file_get_contents( HW_Encryptor::decrypt($_REQUEST['file']));
    die();
}

/**
 * ajax  handle callback
 * @hook wp_ajax_{hw_help_popup}
 */
function hw_ajax_hw_help_popup() {
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw-module-help-nonce")) {
        exit("No naughty business please");
    }
    if(!isset($_REQUEST['file'])) return;
    HW_HOANGWEB::load_class('HW_WP');

    //enqueue syntax highlighter library
    if(class_exists('HW_Libraries',false)) {
        HW_Libraries::enqueue_jquery_libs('syntaxhighlighter_3.0.83');
        HW_Libraries::get('syntaxhighlighter_3.0.83')->enqueue_scripts('shBrushJScript.js','shBrushXml.js','shBrushPhp.js');
    }

    $file = HW_Encryptor::decrypt(urldecode($_REQUEST['file']));
    if(file_exists($file)) {
        echo file_get_contents( $file);
    }
    HW_WP::hw_clean_wp_head();
    //wp_head();
    wp_footer();
    //init SyntaxHighlighter
    echo '
        <script type="text/javascript">
    //SyntaxHighlighter
    if(typeof SyntaxHighlighter != "undefined") SyntaxHighlighter.all();
    </script>
    ';

    die();
}
add_action('wp_ajax_hw_help_popup',  'hw_ajax_hw_help_popup');