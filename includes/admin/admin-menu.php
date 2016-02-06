<?php
#includes/hoangweb-core.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 04/12/2015
 * Time: 00:36
 */
/**
 * Class HW_Custom_Admin_Menu
 */
class HW_Custom_Admin_Menu {
    /** @var array The default WordPress menu, before display-specific filtering. */
    protected $default_wp_menu = null;

    /** @var array The default WordPress submenu. */
    protected $default_wp_submenu = null;

    /**
     * We also keep track of the final, ready-for-display version of the default WP menu
     * and submenu. These values are captured *just* before the admin menu HTML is output
     * by _wp_menu_output() in /wp-admin/menu-header.php, and are restored afterwards.
     */
    private $old_wp_menu = null;
    private $old_wp_submenu = null;

    /**
     * @var array The custom menu in WP-compatible format (top-level).
     */
    private $custom_wp_menu = null;

    /**
     * @var array The custom menu in WP-compatible format (sub-menu).
     */
    private $custom_wp_submenu = null;

    private $cached_custom_menu = null; //Cached, non-merged version of the custom menu. Used by load_custom_menu().

    /**
     * main class constructor
     */
    function __construct() {
        $this->magic_hook_priority = PHP_INT_MAX - 10;
        $this->setup_actions();
        //Set some plugin-specific options
        if ( empty($this->option_name) ){
            $this->option_name = 'hw_menu_editor';
        }
    }
    /**
     * Load the current custom menu for this site, if any.
     *
     * @return array|null Either a menu in the internal format, or NULL if there is no custom menu available.
     */
    private function load_custom_menu() {
        if ( $this->cached_custom_menu !== null ) {
            return $this->cached_custom_menu;
        }

        if ( empty($this->options['custom_menu']) ) {
            return null;
        }
        $this->cached_custom_menu = $this->options['custom_menu'];

        return $this->cached_custom_menu;
    }
    /**
     * Set and save a new custom menu for the current site.
     *
     * @param array|null $custom_menu
     */
    function set_custom_menu() {

        //update_option($this->option_name, $custom_menu);

        $this->cached_custom_menu = null;
    }
    /**
     * Restore the default WordPress menu that was replaced using replace_wp_menu().
     *
     * @return void
     */
    public function restore_wp_menu() {
        global $menu, $submenu;
        $menu = $this->old_wp_menu;
        $submenu = $this->old_wp_submenu;
    }
    /**
     * init actions & filters
     */
    protected function setup_actions() {
        //modify admin menus
        add_action('admin_menu', array(&$this, 'admin_menu'), $this->magic_hook_priority);    //

    }
    /**
     * @param array $tree
     */
    public static function build_custom_wp_menu($tree) {
        global $menu, $submenu ;
        $root = &$tree['root'];
        $root_wf = &$tree['root_widget_features'];
        $root_hoangweb = &$tree['root_hoangweb'];

        if(isset($tree['menu'])) $menu = &$tree['menu'];
        if(isset($tree['submenu'])) $submenu = &$tree['submenu'];
        //$modules_submenus = HW_HOANGWEB::get_wp_option('other_modules_submenus', array());
        //HW_HOANGWEB::del_wp_option('other_modules_submenus');
        do_action('hw_build_custom_wp_menu', $tree);
        $modules_submenus =  array();

        //build custom wp menu under hoangweb settings
        foreach(HW_HOANGWEB_Settings::get_menus_data('menus') as $name=> $menu_arg) {
            #if(isset($data['menus']))
            #foreach($data['menus'] as $menu_arg) {
                $root_hoangweb[] = $menu_arg;
            #}
        }
        //build custom wp menu for widget features page
        foreach(HW_Widget_Features_Setting::get_menus_data() as $name=> $data) {
            foreach($data['features_tab_menus'] as $menu_arg) {
                $menu_arg = HW_Widget_Features_Setting::valid_custom_submenu($menu_arg);
                $root_wf[] = $menu_arg;
            }
        }
        //for modules settings page
        foreach(HW_Module_Settings_page::get_menus_data() as $module => $data) {
            #HW_Module_Settings_page::get_module_data($module, 'menus')
            //main tab setting page for the module
            if(!empty($data['modules_tab_menus'])) {
                foreach($data['modules_tab_menus'] as $menu_arg) {
                    $menu_arg[0] = ':: '. $menu_arg[0];  //dishtingish to other
                    $menu_arg = HW_Module_Settings_page::valid_custom_submenu($menu_arg);
                    $root[] = $menu_arg;
                }
            }
            if(!empty($data['other_menus']['menus'])) {
                foreach($data['other_menus']['menus'] as $menu_slug) {
                    $slug = is_array($menu_slug)? $menu_slug['slug'] : $menu_slug;  //get menu slug

                    $_menu = new HW_Array_List_items_path($menu, $slug);
                    $moving_menu = $_menu->get_search_item(1);  #_print($moving_menu);#exit;
                    if(!$moving_menu) continue;

                    //move submenu module under main settings menu
                    $moving_menu = HW_Module_Settings_page::valid_custom_submenu($moving_menu);
                    //delete old menu
                    $_menu->remove_search_item(1,$menu);
                    $root[] = $moving_menu;

                    //find it's submenus
                    $_submenu = new HW_Array_List_items_path($submenu, $slug);
                    $submenus_list = $_submenu->get_search_item(1);
                    if(!$submenus_list) {
                        $submenus_list = $moving_menu;  //get parent menu as submenu
                    }

                    if(!hwArray::is_multi($submenus_list)) {    //make more menus in list
                        $submenus_list = array($submenus_list);
                    }
                    //adding new data item menu for post type
                    if(!$_submenu->get_search_item(1) && strpos($moving_menu[2], 'edit.php?post_type=') !==false ) {
                        $add_new_menuitem = array(
                            'Thêm mới',
                            'edit_posts',
                            str_replace('edit.php?', 'post-new.php?', $moving_menu[2])
                        );
                        $submenus_list[] = HW_Module_Settings_page::valid_custom_submenu($add_new_menuitem);
                    }
                    if(empty($modules_submenus[$module])) {
                        $modules_submenus[$module] = array('sub'=> $submenus_list, 'parent' => $moving_menu);
                    }
                    elseif(!empty($modules_submenus[$module]['sub'])) {
                        $modules_submenus[$module]['sub'] = array_merge($modules_submenus[$module]['sub'], $submenus_list);
                    }

                }
            }
            //other menus created by module
            if(!empty($data['other_menus']['sub_menus'])) {
                foreach ($data['other_menus']['sub_menus'] as $menu_slug) {
                    $slug = is_array($menu_slug)? $menu_slug['slug'] : $menu_slug;  //get menu slug
                    $_menu = new HW_Array_List_items_path($submenu, $slug);
                    $moving_menu = $_menu->get_search_item();

                    $moving_menu = HW_Module_Settings_page::valid_custom_submenu($moving_menu);
                    if(!$moving_menu) continue;
                    //delete old position
                    $_menu->remove_search_item(0,$submenu);
                    $root[] = $moving_menu;

                    //also add submenu to module boxes menu list
                    $submenus_list = array($moving_menu);

                    if(empty($modules_submenus[$module])) {
                        $modules_submenus[$module] = array('sub'=> $submenus_list, 'parent' => $moving_menu);
                    }
                    elseif(!empty($modules_submenus[$module]['sub'])) {
                        $modules_submenus[$module]['sub'] = array_merge($modules_submenus[$module]['sub'], $submenus_list);
                    }
                }
            }
        }
        //save to db options
        if(count($modules_submenus)) {
            HW_HOANGWEB::add_wp_option('other_modules_submenus', $modules_submenus );
        }
        if($menu) HW_HOANGWEB::add_wp_option('hw_custom_wp_menu', $menu);
        if($submenu) HW_HOANGWEB::add_wp_option('hw_custom_wp_submenu', $submenu);
    }

    /**
     * Generate WP-compatible $menu and $submenu arrays from a custom menu tree.
     */
    private function _build_custom_wp_menu() {
        $new_menu = array();
        $new_submenu = array();

        global $menu, $submenu;

        $cache_submenu = HW_HOANGWEB::get_wp_option('hw_custom_wp_submenu');
        $cache_modules_submenus = HW_HOANGWEB::get_wp_option('other_modules_submenus');
        if(/*1||*/ empty($cache_submenu) || empty($cache_modules_submenus)){
            #$root = new HW_Array_List_items_path($submenu, __CLASS__);
            $parent = &HW_Module_Settings_page::get_root_wp_menu_data($submenu);
            $parent1 = &HW_Widget_Features_Setting::get_root_wp_menu_data($submenu);
            $root_hoangweb = &HW_HOANGWEB_Settings::get_root_wp_menu_data($submenu);
            /*#eval('$parent = &$submenu'. $parent.';');
            $parent[] = Array
            (
                'abc',
                'manage_options',
                'http://localhost/wp1/wp-admin/admin.php?tab=gallery&amp;page=hw_modules_settings&amp;module=gallery&amp;tgmpa-tab-nonce=2ad45f5013&amp;_wpnonce=922a2d6727',

            );
            set_transient('hw-replace-admin-submenu', $submenu);*/
            self::build_custom_wp_menu(array(
                'menu' => &$menu,'submenu' => &$submenu,
                'root' => &$parent,
                'root_widget_features' => &$parent1,
                'root_hoangweb' => &$root_hoangweb
            ));
            HW_SESSION::save_session('submenu', $submenu);
            HW_SESSION::save_session('menu', $menu);

            $this->custom_wp_menu = $menu;
            $this->custom_wp_submenu = $submenu;
        }

    }
    /**
     * modify admin menus
     * @wp_hook admin_menu
     */
    public function admin_menu() {
        global $menu;
        global $submenu;

        //Store the "original" menus for later use in the editor
        $this->default_wp_menu = $menu;
        $this->default_wp_submenu = $submenu;

        $this->_build_custom_wp_menu();

        add_action('in_admin_header', array($this, 'replace_wp_menu'), 100);
        add_filter('parent_file', array($this, 'replace_wp_menu') );
    }

    /**
     * @hook in_admin_header
     */
    public function replace_wp_menu() {
        global $menu, $submenu;
        $new_submenu = HW_HOANGWEB::get_wp_option('hw_custom_wp_submenu');    //get_transient('hw-replace-admin-submenu');
        $new_menu = HW_HOANGWEB::get_wp_option('hw_custom_wp_menu');   //get_transient('hw-replace-admin-menu');

        if($new_submenu) $submenu = $new_submenu;
        if($new_menu) $menu = $new_menu;
    }
}
if(is_admin() || class_exists('HW_CLI_Command', false)) {
    new HW_Custom_Admin_Menu;
}