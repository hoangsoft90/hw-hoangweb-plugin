<?php
# used by includes/website/hw-settings-implementation.php
/**
 * Class NHP_Options_theme_Frontend
 */
class NHP_Options_theme_Frontend extends NHP_Options_template{
    function __construct() {
        //filter post_class
        add_filter('post_class', array($this, '_post_class') );
        add_filter( 'body_class', array($this, '_body_class') );
        add_action('init', array($this, '_init'));
        //template switching
        add_filter( 'template_include', array($this, '_hw_get_template_include')); //template include filter
        add_filter( 'template_include', array($this, '_hw_redirect_page_template'), 99 );
    }

    /**
     * 'init' hook
     */
    public function _init() {
        $hook_bcn = hw_option('show_breadcrumb');
        if($hook_bcn) {
            add_action($hook_bcn, array($this, '_show_breadcrumb_hook'));
        }
    }
    /**
     * template switching
     * @param $template: current template file
     */
    public function _hw_redirect_page_template( $template ) {
        $item = APF_Page_Templates::get_current_context_template();

        if(empty($item)) return $template;      //default template

        //get corespond template
        if( $item['found'] == 'taxonomy' && file_exists($item['template'])) {
            $template = $item['template'];
        }
        if($item['found'] == 'single' && file_exists($item['single_template'])) {
            $template = $item['single_template'];
        }
        /*
        if(is_category('khach-san') ){

            $new_template = locate_template( array( 'category-hotel.php' ) );
            if ( '' != $new_template && file_exists($new_template)) {
                return $new_template ;
            }
        }*/
        return $template;
    }
    /**
     * found current template file
     * @param $file
     */
    public function _hw_get_template_include($file) {
        if(is_admin()) return $file;

        $GLOBALS['hw_current_theme_template'] = basename($file);  //save current theme template file
        //get templates dynamic
        $dynamic_settings = HW__Template_Condition::get_active_templates_settings();
        $setting_conditions = HW__Template_Condition::parse_template_conditions($dynamic_settings);

        $pages_condition_and = $setting_conditions['pages_condition_and'];
        $pages_condition_or = $setting_conditions['pages_condition_or'];

        //match occurence condition
        $match_occurence = hw_get_setting('match_occurence');
        //get cache result for first
        #$data = get_transient('hw_dynamic_template');
        #if(empty($data) ) $data = array();
        foreach($setting_conditions as $pages_condition)    //and, or condition
        if(isset($pages_condition) && is_array($pages_condition)) {     //get template alternate with AND relation

            foreach ($pages_condition as $temp => $meet_condition) {
                if($meet_condition['result']) {
                    #$_name = HW_Validation::valid_objname($file);
                    //get active template
                    $template = get_post_meta($meet_condition['setting']['post_ID'], 'template', true);
                    if(trim($template) != "" && file_exists(get_stylesheet_directory(). '/' .$template)) {   //check new template for exists
                        $file = locate_template($template);   //change template file
                        if($match_occurence == 'first_occurence')  break;
                    }
                }
            }
            if($match_occurence == 'first_occurence')  break;
        }

        //cache result to database
        /*if($detect_addition_sidebar == true) {
            set_transient('hw_dynamic_sidebar', $data);
        }*/

        return $file;
    }

    /**
     * display breadcrumb with hook
     */
    public function _show_breadcrumb_hook() {
        #hw_display_breadcrumb();
        if(function_exists('hw_display_breadcrumb')) {
            echo '<div class="hw-breadcrumb breadcrumbs">';
            hw_display_breadcrumb();
            echo '</div>';
        }
        else echo __('Vui lòng kích hoạt plugin hw-breadcrumb');
    }
    /**
     * adjust post_class
     * @param $classes
     */
    public function _post_class($classes){
        if(($key = array_search('page', $classes)) !== false) {     //remove default class generate by wordpress
            unset($classes[$key]);
        }
        $class_page = $this->get_nhp__content_classes('post_class');
        if($class_page) $classes[] = $class_page;
        //__save_session('classes',$classes);
        return $classes;
    }

    /**
     * get content_classes option base current context
     * @param $field field to return value
     * @return mixed
     */
    private function get_nhp__content_classes($field = 'post_class') {
        static $classes_page = null;
        if(!($classes_page)) $classes_page  = hw_option('content_classes');

        //valid
        if(!in_array($field, array('post_class', 'body_class'))) return;

        if(is_array($classes_page)) {
            foreach ($classes_page as $page => $class) {
                if(HW__Template::check_template_page($page)) {
                    return $class[$field];
                    break;
                }
            }
        }
    }


    /**
     * body class
     * @param $classes
     */
    public function _body_class($classes) {
        /*
        $background_color = get_background_color();

        if ( ! is_active_sidebar( 'sidebar-1' ) || is_page_template( 'page-templates/full-width.php' ) )
            $classes[] = 'full-width';

        if ( is_page_template( 'page-templates/front-page.php' ) ) {
            $classes[] = 'template-front-page';
            if ( has_post_thumbnail() )
                $classes[] = 'has-post-thumbnail';
            if ( is_active_sidebar( 'sidebar-2' ) && is_active_sidebar( 'sidebar-3' ) )
                $classes[] = 'two-sidebars';
        }

        if ( empty( $background_color ) )
            $classes[] = 'custom-background-empty';
        elseif ( in_array( $background_color, array( 'fff', 'ffffff' ) ) )
            $classes[] = 'custom-background-white';

        // Enable custom font class only if the font CSS is queued to load.
        if ( wp_style_is( 'hoangweb-fonts', 'queue' ) )
            $classes[] = 'custom-font-enabled';
        */
        if ( ! is_multi_author() )
            $classes[] = 'single-author';

        $class_page = $this->get_nhp__content_classes('body_class');
        if($class_page) $classes[] = $class_page;

        return $classes;
    }
}
HW_Options_Frontend::add_fragment(new NHP_Options_theme_Frontend());
