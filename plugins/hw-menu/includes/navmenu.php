<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 24/05/2015
 * Time: 09:42
 */
/**
 * Class HW_NAVMENU
 */
class HW_NAVMENU{
    /**
     * twig template
     * @var
     */
    private $twig;

    /**
     * share one instance of this class
     * @var
     */
    private static $instance;
    /**
     * save current menu args
     * @var null
     */

    private $menu_args = null;

    /**
     * construct
     */
    public function __construct(){
        if(!$this->check_already()) return;

        if(class_exists('HW_HOANGWEB')) {
            //load twig template engine
            #HW_HOANGWEB::load_class('Twig_Autoloader');    //please load what just you used, because this library use in class 'HW_Twig_Template'. For reason, php play on hosting and it make sence to read you code,
            HW_HOANGWEB::load_class('HW_Twig_Template');
        }
        $this->setup_hooks();   //init hooks
    }

    /**
     * check already class requirement
     * @return bool
     */
    public function check_already(){
        return (class_exists('HW_NavMenu_Metabox_settings'));
    }
    /**
     * seting up hooks
     */
    private function setup_hooks(){
        //list hooks for nav menu
        add_filter('wp_nav_menu_items', array($this, '_add_custom_navmenu'), 10, 2);
        add_filter( 'wp_page_menu_args', array($this, '_page_menu_args' ));
        add_filter('nav_menu_link_attributes',array($this, '_nav_menu_link_attributes'),10,4);
        add_filter('nav_menu_css_class', array($this, '_addspecial_nav_class'), 10, 2);
        add_filter('walker_nav_menu_start_el', array($this,'_walker_nav_menu_start_el'), 10, 4);
        add_filter('wp_nav_menu', array($this, '_filter_wp_nav_menu'));
        add_filter( 'wp_nav_menu_args', array($this, '_modify_nav_menu_args' ));

        //my filters
        add_filter('hw_navmenu_link_attributes', array($this, '_hw_navmenu_link_attributes'), 10, 5);
        add_filter('hw_wp_nav_menu', array($this, '_hw_wp_nav_menu'), 10);

        add_action('wp_enqueue_scripts', array($this, '_frontend_enqueue_scripts'));
    }

    /**
     * enqueue scripts/stylesheets on website
     */
    public function _frontend_enqueue_scripts() {
        wp_enqueue_style('hw-menu-style', HW_MENU_URL.'/css/hw-menu-frontend.css');
    }

    /**
     * get registered nav menus
     * @return string
     */
    public static function get_bind_locations() {
        return get_theme_mod( 'nav_menu_locations' )? get_theme_mod( 'nav_menu_locations' ) : array();
    }

    /**
     * update nav menu locations
     * @param $location
     * @param $menu_id
     */
    public static function set_menu_location($location, $menu_id) {
        // Set the menu to primary menu location
        $locations = self::get_bind_locations();
        $locations[$location] = $menu_id;
        set_theme_mod ( 'nav_menu_locations', $locations );
    }
    /**
     * get default wp_nav_menu args
     */
    public static function get_default_navmenu_args(){
        return array(
            'container' => 'div',
            'container_id' => '',
            'menu_class' => 'menu',
            'echo' => true
        );
    }

    /**
     * get menu slug
     * @param mixed $menu: menu args or menu name
     */
    public static function get_menu_name($menu = ''){
        //get current menu
        if(is_object($menu) || is_array($menu)){
            $menu = (object)$menu;  //cast to object
            if(!empty($menu->theme_location)) $menu =  $menu->theme_location;
            elseif(!empty($menu->menu) ) {
                if(is_string($menu->menu)) $menu = $menu->menu;
                else $menu = $menu->menu->name; //for custom menu widget
            }
        }

        return is_string($menu)? $menu : '';
    }

    /**
     * check to see if file exists from twig
     * @param $file
     * @param $twig
     * @return bool
     */
    public static function twig_asset_exists($file, $twig){
        try{
            $twig->loadTemplate($file);
            return true;
        }
        catch(Exception $e){
            return false;
        }
    }
    /**
     * filter wp_nav_menu output
     * @param $output: output of wp_nav_menu function
     */
    public function _filter_wp_nav_menu($output){
        $args = $this->menu_args;   //get current menu args
        /**
         * nav item class
         */
        $first_nav_item_class = !empty($args->first_menu_item_class)? $args->first_menu_item_class.' ' : '';
        $last_nav_item_class = !empty($args->last_menu_item_class)? $args->last_menu_item_class.' ' : '';

        //add first class & last class to nav menu item
        $output = preg_replace( '/class="menu-item/', 'class="'.$first_nav_item_class.'menu-item ', $output, 1 );
        $output = substr_replace( $output, 'class="'.$last_nav_item_class.' menu-item', strripos( $output, 'class="menu-item' ), strlen( 'class="menu-item' ) );

        //valid
        if(!isset($args->container)) $args->container = 'div';
        if(!isset($args->allow_tags_nav_menu)) $args->allow_tags_nav_menu = '';

        /**
         * remove ul,li tags surround menu output
         */
        if(isset($args->only_anchor_tag_nav_menu)) {
            $args->allow_tags_nav_menu .= '<a>';
        }
        $args->allow_tags_nav_menu .= "<{$args->container}>";   //allow container tag

        if(isset($args->only_anchor_tag_nav_menu) && $args->only_anchor_tag_nav_menu && !empty($args->allow_tags_nav_menu)) {

            $output = strip_tags($output,$args->allow_tags_nav_menu);
        };
        return $output;
    }
    /**
     * modify nav menu link anchor tag
     * note: if this callback of the hook that mean you not link specific menu to wp_nav_menu
     * @param $attributes
     * @param $output
     * @param $item
     * @param $depth
     * @param $args
     * @return mixed
     */
    public function _hw_navmenu_link_attributes($attributes, $output, $item, $depth, $args){

        return $attributes;
    }

    /**
     * filter menu output
     * @hook hw_wp_nav_menu
     * @param $menus
     */
    public function _hw_wp_nav_menu($menus) {
        $args = $this->menu_args;   //get current menu args

        if(isset($args->show_lang_buttons_outside) && isset($args->langs_switcher)
            && ($args->show_lang_buttons_outside == 'on' || $args->show_lang_buttons_outside)
            /*&& $depth==0*/) {
            $menus .= '<div class="hw-langs-switcher">'. $args->langs_switcher . '</div>';
        }
        return $menus;
    }
    /**
     * if this callback of the hook that mean you not link specific menu to wp_nav_menu
     * @hook filter 'wp_nav_menu_args'
     * @param $args
     */
    public function _modify_nav_menu_args($args){
        //get current menu name
        $menu = self::get_menu_name($args);
        /**
         * get menu skin
         * first entry for through out menu filters
         */
        $skin = HW_NavMenu_Metabox_settings::get_menu_setting('skin', $menu);
        $enable_skin = HW_NavMenu_Metabox_settings::get_menu_setting('enable_skin', $menu);
        $enable_filter_menu = HW_NavMenu_Metabox_settings::get_menu_setting('enable_filter_menu', $menu);
        //add other menu setting to menu args
        $addition_menu_args =  array(
            'show_searchbox', 'remove_ul_wrap', 'only_anchor_tag_nav_menu',
            'show_home_menu','allow_tags_nav_menu','show_icon'
        );
        foreach($addition_menu_args as $option) {
            $args[$option] = HW_NavMenu_Metabox_settings::get_menu_setting($option, $menu);
        }

        if($enable_filter_menu && $enable_skin && $skin){
            //parse SKIN object
            $this->skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($skin); //parse into HW_SKIN object & saved as property
            $this->skin->file = ($this->skin->instance->get_skin_file($this->skin->hash_skin));
            $options_config = $this->skin->instance->get_file_skin_options($this->skin->hash_skin); //theme options configuration
            $theme_setting = $this->skin->instance->get_file_skin_setting(); //theme setting file

            //register twig loader
            if( class_exists('HW_Twig_Template')){
                //Twig_Autoloader::register();
                //$loader = new Twig_Loader_Filesystem($this->skin->instance->get_file_skin_resource('tpl'));
                $this->twig = HW_Twig_Template::create($this->skin->instance->get_file_skin_resource('tpl'));
                #$this->twig = new Twig_Environment($loader);
                $args['twig'] = $this->twig->get();    //reference twig object
            }

            if(file_exists($this->skin->file)){
                $theme = array();
                $theme['styles'] = array(); //init
                $theme['scripts'] = array();
                $theme['filters'] = array();    //allow filters
                //keep this info (ex: menu) with filter that bind to callback to compare from current filter args
                $theme['menu'] = $menu;
                $user_options= array(); //skin options

                if(file_exists($theme_setting)) include ($theme_setting);   //theme setting
                if(file_exists($options_config)) include($options_config);  //options file

                if(isset($theme_options)){  //$theme_options variable already exists in skin options
                    //$skin_options_config = hwskin_parse_theme_options($theme_options);

                    //get addition skin options value
                    $skin_options = isset($skin['skin_options'])? $skin['skin_options'] : array();
                    if(empty($skin_options)) $skin_options = array();   //please go menu setting page & press on save button

                    $exclude_options = array('menu');
                    foreach($exclude_options as $opt){
                        if(isset($skin_options[$opt])) unset($skin_options[$opt]);
                    }
                    $user_options = HW_SKIN::get_skin_options($skin_options,$theme['args'], $theme_options);
                    $args = array_merge($args, $user_options);
                    //sync skin options with $args
                    //hope $args share to all remain menu filters in order to render final output menu to user
                    /*foreach($skin_options as $arg => $value){
                        $field_setting = $skin_options_config[$arg];
                        if(!isset($args[$arg])) $args[$arg] = '';   //set menu args from skin
                        //append if exists setting
                        if(isset($field_setting['method']) && $field_setting['method'] == 'append' && !empty($value)){
                            if(!in_array($value, preg_split('#[\s]+#',$args[$arg]))){
                                $args[$arg] .= (!empty($args[$arg])? ' ':'').trim($value);
                            }
                        }
                        //override setting if not exists
                        if(isset($field_setting['method']) && $field_setting['method'] == 'override' && !empty($value)){
                            $args[$arg] = $value;
                        }
                    }*/

                }
                //make sure have no ouput at here
                HW_SKIN::include_skin_file($this->skin->file);
                //extract wp_nav_menu_args from skin file
                /*if(isset($theme['args']) && is_array($theme['args'])){
                    //$args = array_merge($args, $theme['args']);
                    foreach($theme['args'] as $arg => $val){
                        if(isset($args[$arg])) $args[$arg] .= (!empty($args[$arg])? ' ':'').$val;     //append
                        else $args[$arg] = $val;    //create if not exists
                    }
                }*/
                $this->skin->instance->do_filters($theme['filters'], array($theme, $user_options));   //do filters & actions that defined in skin

                //$this->skin->instance->enqueue_files_from_skin(null/*$theme['styles']*/, $theme['scripts']);    //put stuff from skin (note: css enqueue before)
                //new way for enqueue stuff from skin
                HW_SKIN::enqueue_skin_assets(array(
                    'instance' => $this->skin->instance,
                    'hash_skin' => $this->skin->hash_skin,
                    'skin_file' => $this->skin->file,
                    'theme_settings' => $theme,
                    'theme_options' => $args
                ));

                //languages selector on the website
                $show_langs_switcher = HW_NavMenu_Metabox_settings::get_menu_setting('show_langs_switcher', $menu);   //show search form in nav menu

                if($show_langs_switcher ) {
                    //get langs switcher output
                    $args['langs_switcher'] = hw_get_qtrans_switcher();
                }

                #$args = array_merge($args, $skin_options); //filtered values in above
                //if(isset($args['show_items_separator'])) ;

            }
            $args['walker'] = new HW_Nav_Menu_Walker();
        }

        $this->menu_args = (object)$args;   //save current menu args
        /*if( 'primary' == $args['theme_location'] )
        {
            $args['depth'] = -1;
            $args['container_id'] = 'my_primary_menu';
        }
        if('menu1' == $args['menu']){    #maybe old wp version
            $args['walker'] = new custom_walker();
        }
        //for custom menu widget
        if(isset($args['menu']) && isset($args['menu']->name) && $args['menu']->name == 'menu-header')
        {

        }*/
        return $args;

    }

    /**
     * callback for action 'nav_menu_link_attributes'
     * note: if this callback of the hook that mean you not link specific menu to wp_nav_menu
     * @param $atts
     * @param $item
     * @param $args
     * @return mixed
     */
    public function _nav_menu_link_attributes($atts, $item, $args, $walker){
        //for qtranslate plugin
        // Integration with qTranslate Plugin
        if($item->url=='/' && function_exists('qtrans_convertURL')){    //home item
            $atts['href'] = qtrans_convertURL($item->url);
        }

        //anchor class name
        if(isset($args->anchor_class) && $args->anchor_class){
            #if(!isset($atts['class'])) $atts['class'] = '';
            $atts['class'][] = (!empty($atts['class'])?' ':'').$args->anchor_class;  //add class to anchor tag
        }

        return $atts;
    }
    /**
     * filter nav menu item
     * note: if this callback of the hook that mean you not link specific menu to wp_nav_menu
     * @param $output
     * @param $item
     * @param $depth
     * @param $args
     */
    //no longer use
    public function _walker_nav_menu_start_el($output, $item, $depth, $args){
        $data['args'] = $args;  //extend args data
        $args = (object) $args;     //cast to object
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';

        $attributes .=!empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';

        $attributes .=!empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

        // Integration with qTranslate Plugin
        /*if(function_exists('qtrans_convertURL')){
            $item->url = qtrans_convertURL($item->url);
        }*/

        $attributes .=!empty($item->url) ? ' href="' . esc_attr( $item->url ) . '"' : '';

        $output = $args->before;
        $attributes .= apply_filters('hw_navmenu_link_attributes', $attributes, $output, $item, $depth, $args); //filter link attributes for wp theme
        $data['attributes'] = $attributes;
        $data['title'] = apply_filters('the_title', $item->title, $item->ID);

        if(isset($args->twig) && self::twig_asset_exists('start_el_anchor.twig', $args->twig)) {
            $link = $args->twig->loadTemplate('start_el_anchor.twig');
            $output = $link->render($data);
        }
        else{
            $output .= '<a' . $attributes . '>';

            $output .= $args->link_before . $data['title'] . $args->link_after;

            $output .= '</a>';

            $output .= $args->after;

        }
        return $output;
    }
    /**
     * customize menu css
     * @param $classes: classes for current menu item
     * @param $item: nav item object
     */
    public function _addspecial_nav_class($classes, $item){
        //valid
        //if(empty($item->classes) || !is_array($item->classes))
        //menu_item_class_focus;
        if(in_array('current-menu-item',(array)$item->classes)){    //current item
            $classes[]='hw-menu-item-focus';
            if(!empty($this->menu_args->menu_item_class_focus)) {
                $classes[] = $this->menu_args->menu_item_class_focus;
            }
        }
        else {
            $classes[]='hw-menu-item-normal';
            if(!empty($this->menu_args->menu_item_class)) {
                $classes[] = $this->menu_args->menu_item_class;
            }
        }
        /*other of purpose
        preg_match('/[^\/]+$/', trim($item->url, '/'), $r); //get page

        if (is_page() && is_page($r[0]))
            $classes[]='active';
*/
        return $classes;
    }

    /**
     * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
     *
     * @since hoangweb 1.0
     */
    public function _page_menu_args( $args ) {
        //get current menu
        $current_menu = self::get_menu_name($args);

        $enable_filter_menu = HW_NavMenu_Metabox_settings::get_menu_setting('enable_filter_menu', $current_menu);   //enable override menu setting
        $show_home = HW_NavMenu_Metabox_settings::get_menu_setting('show_home_menu', $current_menu);    //show home item in the navmenu?

        if($enable_filter_menu  && $show_home){
            if ( ! isset( $args['show_home'] ) )
                $args['show_home'] = true;
        }
        return $args;
    }
    /**
     * add extra content to specific navmenu
     * @param $items
     * @param $args
     * @return string
     */
    public function _add_custom_navmenu($items, $args){
        //get current menu
        $current_menu = self::get_menu_name($args);
        $enable_filter_menu = HW_NavMenu_Metabox_settings::get_menu_setting('enable_filter_menu', $current_menu);   //enable override menu setting
        $show_searchbox = HW_NavMenu_Metabox_settings::get_menu_setting('show_searchbox', $current_menu);   //show search form in nav menu
        $show_langs_switcher = HW_NavMenu_Metabox_settings::get_menu_setting('show_langs_switcher', $current_menu);   //show search form in nav menu
        $show_home = HW_NavMenu_Metabox_settings::get_menu_setting('show_home', $current_menu); //show home item

        if(!$enable_filter_menu) return $items; //disable customize navmenu

        if($enable_filter_menu && $show_home){
            //you should drag & drop home link from menu edit page
        }
        if ($enable_filter_menu && $show_searchbox ){
            $search_form = get_search_form(false); //get search form
            $search_form = apply_filters('hw_navmenu_get_search_form', $search_form);   //to extending
            $items .= '<li class="menu-search-form">'.$search_form.'</li>';
        }
        if($enable_filter_menu && $show_langs_switcher
        /*&& function_exists('qtrans_generateLanguageSelectCode')*/ //don't limit for mqtranslate
        && (!isset($args->show_lang_buttons_outside) || !$args->show_lang_buttons_outside ) && isset($args->langs_switcher))
        {
            $items .= '<li class="hw-langs-switcher">'.$args->langs_switcher.'</li>';
        }

        return $items;
    }
    /**
     * return this instance of the class
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }
}

/**
 * menu walker for advanced rendering
 * Nav menu walker class
 */
include_once('navmenu_walker.php');