<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 28/10/2015
 * Time: 10:31
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Class HWAWC_SaveWidgets_options
 * doc: http://admin-page-framework.michaeluno.jp/en/v2/classes/AdminPageFramework.html
 */
if(class_exists('AdminPageFramework')):
    class HW_Module_Settings_page extends HW_AdminPageFramework{
        /**
         * store module instances
         * @var array
         */
        private static $modules = array();
        /**
         * menu boxes
         * @var array
         */
        public  static $menus = array(

        );

        /**
         * page slug constant
         */
        const PAGE_SLUG = 'hw_modules_settings';

        /**
         * main class constructor
         */
        public function __construct() {
            parent::__construct();
            //add screen option
            $this->add_screen_options();

            //init hooks


            add_action( 'load_' . self::PAGE_SLUG . '_' . self::valid_tab_slug('modules-config'), array( &$this, '_list_modules_menu_page' ) );
            add_action('do_before_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug('modules-config'), array(&$this, '_do_before_tab_hook'));
            //receives the output of the CSS rules applied to the tab page of the slug.
            #add_filter('style_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug('modules-config'), array($this, 'style_tab_filter'));

        }

        /**
         * @hook plugins_loaded
         */
        private function add_screen_options() {
            HW_HOANGWEB::load_class('HW_Screen_Option');
            if(class_exists('HW_HELP', false)) {
                $page_slug = HW_HELP::load_settings_page_hook_slug(self::PAGE_SLUG,'');
                $screen = HW_Screen_Option::get_instance(
                    'hw_module_setting_page',
                    $page_slug
                );
                #HW_Screen_Option::get('modules-manager')->get_option('xx');
                $screen->add_options(array(
                    'per_page' => array('label'=>'Số lượng/trang', 'default'=>'10'),
                    // create more options http://w-shadow.com/blog/2010/06/29/adding-stuff-to-wordpress-screen-options/
                    //'abc' => array('label'=>'ABC',  'default'=>'65' )
                ));
                $screen->addition_text('Dành cho trang cấu hình Module Hoangweb.');
            }

        }
        /**
         * add module
         * @param $name
         * @param $inst
         */
        public static function add_module($name, $inst) {
            self::$modules[$name] = $inst;
            $info = $inst->option('module_info');
            $capability = 'manage_options';
            //add submenu under global settings menu
            if($inst->option('enable_tab_settings')) {
                self::add_module_tab_submenu_page($name, array(
                    empty($info)? $name : $info['name'],
                    $capability,
                    self::get_module_setting_page($name)
                ));
            }

        }

        /**
         * get all modules or specific module by slug or class
         * @param $name
         * @return array
         */
        public static  function get_modules($name='') {
            //if(!HW_Module::is_active($name)) return NULL; //for module slug
            if($name && isset(self::$modules[$name])) { //get module by slug
                return self::$modules[$name];
            }
            foreach(self::$modules as $module) {
                if(is_object($module) && get_class($module) == $name) { //get module by class name
                    return $module;
                }
            }
        }
        //test
        static function test(){return array_keys(self::$modules);}
        /**
         * return module data
         * @param $name
         * @return array
         */
        public static function get_module_data($name, $item='') {
            $data['instance'] = self::get_modules($name);
            if(isset(self::$menus[$name])) {
                $data['menus'][$name] = self::$menus[$name];
            }
            if($item) return isset($data[$item])? $data[$item] : null;
            return (object)$data;
        }

        /**
         * get modules boxes
         * @return array
         */
        public static function &get_modules_menu_boxes() {
            $parent_menus = array();
            foreach(self::$menus as $module => &$menus) {
                if(isset($menus['menus']))
                foreach($menus['menus'] as $id=> &$args) {
                    if($args['parent_id']==0 && !isset($parent_menus[$module])) {
                        $parent_menus[$module] = &$args;
                    }
                }
            }
            return $parent_menus;
        }
        /**
         * add module menu box
         * @param string $module
         * @param array $args
         * @param $parent
         * @return array
         */
        public static function add_menu_box($module, $args = array(), $parent=0) {
            static $count = 0;
            if(!isset(self::$menus[$module])) { //validation
                self::$menus[$module] = array('menus' => array(), 'parent_menus' => array());
            }
            if(!isset(self::$menus[$module]['menus'])) {
                self::$menus[$module]['menus']= array();
            }
            if(!isset(self::$menus[$module]['parent_menus'])) {
                self::$menus[$module]['parent_menus']= array();
            }
            $parents = &self::$menus[$module]['parent_menus'];
            $module_inst = self::get_modules($module);   //get module object

            if( $module_inst && !empty($args)) {
                if(empty($parents[$parent])) {
                    $parents[$parent] = array();
                }
                $args['module'] = $module_inst;   //reference module object
                if(!isset($args['id'])) {
                    $args['id'] = md5($module_inst->option('module_name'). $parent. count($parents,COUNT_RECURSIVE));
                }
                $args['parent_id'] = $parent;
                //add main & parent menu data
                self::$menus[$module]['menus'][$args['id']] = $args;
                $parents[$parent][] = $args;

                return $args['id'];//self::$menus[$module]['menus'];
            }

        }

        /**
         * add module submenu box
         * @param $module
         * @param string $parent menu id
         * @Param args
         * @return string return submenu id
         */
        /*public static function add_submenu_box($module, $parent, $args = array()) {
            return self::add_menu_box($module, $args, $parent);
        }*/
        /**
         * declare other menu page for the module
         * @param $module
         * @param $slug
         * @param bool $parent whether menu is parent or submenu
         */
        public static function other_menu_page($module, $slug, $parent = true) {
            //valid
            if(!self::get_modules($module)) return ;    //module not found

            if(!isset(self::$menus[$module]['other_menus'])) {
                self::$menus[$module]['other_menus']= array(
                    'menus' => array(),
                    'sub_menus' => array()
                );
            }
            if(is_array($slug)) $menu_slug = $slug['slug'];
            else $menu_slug = $slug;
            if($parent) self::$menus[$module]['other_menus']['menus'][$menu_slug] = $slug;
            else self::$menus[$module]['other_menus']['sub_menus'][$menu_slug] = $slug;
        }

        /**
         * add module tab menu
         * @param $module
         * @param $menu
         */
        public static function add_module_tab_submenu_page($module, $menu) {
            //valid
            if(!self::get_modules($module)) return ;    //module not found
            if(!isset(self::$menus[$module]['modules_tab_menus'])) {
                self::$menus[$module]['modules_tab_menus']= array();
            }
            self::$menus[$module]['modules_tab_menus'][] = $menu;

        }
        /**
         * return all menus
         * @return array
         */
        /*public static function get_menus_data() { //already exists
            return self::$menus;
        }*/

        /**
         * return all modules
         * @return array
         */
        public static function get_modules_data() {
            return self::$modules;
        }
        /**
         * get field value
         * @param array|string $name
         * @param string $default_value
         * @return array|mixed|null|void
         */
        public static function get_field_value($name, $default_value=''){
            //ie receive setting field in section: $name=array( 'my_first_section', 'my_text_field', 0 )        #item 0 if value is array
            return AdminPageFramework::getOption( __CLASS__, self::valid_tab_slug($name), $default_value );
        }

        /**
         * return all fields values
         * @return array|mixed|null|void
         */
        public static function get_values() {
            return AdminPageFramework::getOption( __CLASS__ );
        }
        /**
         * valid tab slug
         * @param $name
         * @return mixed
         */
        public static function valid_tab_slug($name) {
            #return preg_replace('#-@!#$%^&*()+/\.<>~#', '_', $name);
            return HW_Validation::valid_apf_slug($name);
        }

        /**
         * return setting page for module
         * @param $module
         */
        public static function get_module_setting_page($module) {
            return wp_nonce_url(
                add_query_arg(
                    array(
                        'tab'       => HW_Module_Settings_page::valid_tab_slug($module),
                        'page'          => urlencode( HW_Module_Settings_page::PAGE_SLUG ),
                        'module'        => urlencode( $module ),
                        #'module_name'   => urlencode( $item['sanitized_plugin'] ),
                        #'module_source' => urlencode( $item['url'] ),
                        'tgmpa-tab-nonce' => urlencode( wp_create_nonce( 'tgmpa-tab-nonce' ) ),
                    ),
                    #network_admin_url( 'options-general.php' )
                    admin_url( 'admin.php' )
                ),
                'tgmpa-install'
            );
        }

        /**
         * @param $oAdminPage
         * @hook load_{page-slug}_{modules-config tab}
         */
        public function _list_modules_menu_page($oAdminPage) {
            $oAdminPage->addSettingFields(
                array(
                    'field_id' => md5(__CLASS__),
                    'type'=> 'hw_html',
                    'show_title_column'=> false,
                    'output_callback' => array(&$this, 'list_modules_menu_page')
                )
            );
        }

        /**
         * @param $data
         * @return mixed
         */
        private function valid_module_box($data) {
            if(!isset($data['icon_url'])) $data['icon_url'] = HW_HOANGWEB_URL. '/images/module-box.png';
            if(!isset($data['link']) || !is_string($data['link'])) $data['link'] = '#';
            return (object)$data;
        }
        /**
         * hw_html apf field callback
         * @param $aField
         */
        public function list_modules_menu_page($aField) {

            $menus_data = array();
            foreach($this->get_modules_menu_boxes() as $module => $menu_data) {
                $menu_data = $this->valid_module_box($menu_data);
                $classes = array('hw-box-'. $module, 'hw-module-menu-box' );
                if(isset($menu_data->classes)) {
                    $classes = array_merge((array)$menu_data->classes, $classes );
                }

                echo '<div class="'.join(' ',$classes).'">';
                echo '<a name="hw-module-'.$module.'"></a>';    //create anchor tag
                //begin menu struct
                echo '<ul class="hw-module-menus-container"><li>';
                echo '<a href="'. $menu_data->link .'" target="_blank">';
                if(!empty($menu_data->icon_url)) {
                    echo '<img src="'.$menu_data->icon_url.'"/>';
                }
                echo '<h3>'.$menu_data->title.'</h3>';
                echo '</a>';
                //sub menus
                $menus = self::$menus[$module];
                #reset($menus['menus']);
                $first_id = key($menus['menus']);   //ignore main menu

                $inst = $menu_data->module; //self::get_modules($module);
                $args = array(
                    'attributes' => array(
                        'id' => 'hw-menu-'.$inst->option('module_name')
                    ),
                    'classes' => array('hw-menu-'.$inst->option('module_name'), 'hw-module-menus','sf-menu')
                );
                echo $this->generate_modules_menu($first_id,$menus, $args);
                echo '</li></ul>';  //end menu struct
                echo '</div>';
            }

        }

        /**
         * adding custom submenu
         * @param $menu
         * @param null $root_menus
         */
        public static function add_custom_submenu($extra_menu, $root_menus = null) {
            if(empty($root_menus)) {
                global $submenu;
                $root_menus = $submenu;
            }
            $extra_menu = self::valid_custom_submenu($extra_menu);
            $parent = &self::get_root_wp_menu_data($root_menus);
            if($parent) $parent[] = $extra_menu;
        }

        /**
         * Create the main function to build milti-level menu. It is a recursive function.
         * @param $parent
         * @param $menu
         * @param $args
         */
        private function generate_modules_menu($parent=0,$menu, $args) {
            $html = "";
            HW_HOANGWEB::load_class('HW_UI_Menus');
            //parse arguments
            $default_args = array(
                'attributes' => array('module-menus'),
                'item_attributes' => array(),
                'sub_attributes' => array(),
                'sub_item_attributes' => array(),

                'classes' => array(),
                'item_classes' => array(),
                'sub_classes' => array(),
                'sub_item_classes' => array()
            );
            $args = array_merge($default_args, $args);

            $html = HW_UI_Menus::buildMenu($parent, $menu, $args);
            return $html;
        }

        /**
         * setup form fields
         */
        public function setUp() {

            $current_module_tab = hw__get('module');  //get current module setting tab $_GET['tab']==$_GET['module']

            if(HW_HOANGWEB::is_current_screen(self::PAGE_SLUG)
                && hw__get('tab') && $current_module_tab && (!HW_Module::is_active($current_module_tab)
                    || (isset(self::$modules[$current_module_tab])
                        && !self::$modules[$current_module_tab]->option('enable_tab_settings')))
            )
            {
                wp_die('Xin lỗi: Module này chưa kích hoạt hoặc không có form cài đặt.');
            }
            // Set the root menu (http://admin-page-framework.michaeluno.jp/en/v2/classes/AdminPageFramework_Menu.html)
            #$this->setRootMenuPage( 'Settings' );        // specifies to which parent menu to add.
            $this->setRootMenuPage('(H) Cấu hình modules' , HW_HOANGWEB_URL. '/images/ie-gear-icon.png');

            // Add the sub menus and the pages/ addSubMenuItems
            $this->addSubMenuItem( //addSubMenuItems
                array(
                    'title'    =>    'Cấu hình modules',        // the page and menu title
                    'page_slug'    =>    self::PAGE_SLUG         // the page slug
                )
            );
            /*$this->addSubMenuItem(array(
                'title' => 'sdf',
                'href' =>  self::get_module_setting_page('abc'),
                #'page_slug'    =>    self::PAGE_SLUG
            ));*/
            //define tabs
            $this->addInPageTabs(
                self::PAGE_SLUG,    // set the target page slug so that the 'page_slug' key can be omitted from the next continuing in-page tab arrays.
                array(
                    'tab_slug'    =>    'modules-config',    // avoid hyphen(dash), dots, and white spaces
                    'title'        =>    __( 'Cấu hình chung' ),
                )

            );
            //add more tabs
            foreach(self::$modules as $name => $inst){
                $info = HW_Plugins_Manager::get_module($name);

                if(!empty($info) && $inst->option('enable_tab_settings')) {

                    $this->addInPageTab( array(
                        'tab_slug' => $name,
                        'title' => $info['name']
                    ));

                    // load + page slug + tab slug
                    add_action( 'load_' . self::PAGE_SLUG . '_' . self::valid_tab_slug($name), array( $inst, '_replyToAddFormElements' ) );
                    //triggered before rendering the page.
                    add_action('do_before_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'do_before_tab_hook'));

                    // triggered in the middle of rendering the page.
                    add_action('do_' .  self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'do_tab_hook'));

                    //triggered after rendering the page
                    add_action('do_after_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'do_after_tab_hook'));
                    /**
                     * filters
                     */
                    //receives the output of the middle part of the page including form input fields.
                    add_filter('content_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'content_tab_filter'));
                    /*
                   ==> find in : hw-hoangweb\lib\admin-page-framework\development
                    //receives the output of the top part of the page.
                    add_filter('head_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'head_tab_filter'));

                    // receives the output of the bottom part of the page.
                    add_filter('foot_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'foot_tab_filter'));

                    //receives the exporting array sent from the tab page.
                    add_filter('export_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'export_tab_filter'));
                    //receives the importing array submitted from the tab page.
                    add_filter('import_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'import_tab_filter'));
    */
                    #add_filter(''. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'tab_filter'));

                    //receives the output of the CSS rules applied to the tab page of the slug.
                    add_filter('style_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'print_styles'));

                    //receives the output of the JavaScript script applied to the tab page of the slug.
                    add_filter('script_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, 'print_scripts'));

                    //receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
                    add_filter('validation_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name), array($inst, '_validation_tab_filter'));
                }

            }

            $this->setInPageTabTag( 'h2' );        // sets the tag used for in-page tabs
            //submit button
            /*$this->addSettingFields(
                array(
                    'field_id' => 'submit_button',
                    'type' => 'submit',
                    'show_title_column' => false,
                )
            );*/
        }
        /**
         * @hook do_before_{page-slug}_{modules-config tab}
         */
        public function _do_before_tab_hook() {
            HW_Libraries::enqueue_jquery_libs('menus/superfish');
        }

        /**
         * auto hook callback for this admin page class
         * @hook script_{page-slug}_{modules-config tab}
         */
        public function script_hw_modules_settings_modules_config() {

            echo "<script>
            jQuery(document).ready(function() {
                jQuery('ul.hw-module-menus-container').superfish({
                    //animation: {height:'show'},
                    //delay:		 1200,			// 1.2 second delay on mouseout
                    pathClass:	'current'
                });
            });
            </script>";
        }
        /**
         * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
         * @hook do_{page-slug}
         */
        public function do_hw_modules_settings() {

        }
        /**
         * The pre-defined validation callback method.
         *
         * Notice that the method name is validation_{instantiated class name}_{field id}. You can't print out inside callback but stored in session variale instead
         *
         * @param    string|array    $sInput        The submitted field value.
         * @param    string|array    $sOldInput    The old input value of the field.
         */
        public function validation_HW_Module_Settings_page( $sInput, $sOldInput ) {
            return $sInput;
        }
        /**
         * initial
         * @hook init
         */
        public static function __init(){
            //if(class_exists('HW_SKIN')) hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);
            HW_APF_FieldTypes::apply_fieldtypes(array('hw_html', 'hw_admin_table'), 'HW_Module_Settings_page');
            //init custom field type
            /*if(class_exists('APF_hw_admin_table')) {
                new APF_hw_admin_table('HW_Module_Settings_page');
            }*/
            new HW_Module_Settings_page();
        }
    }

//init
    if(is_admin() || class_exists('HW_CLI_Command', false)) {
        add_action('hw_trigger_modules_settings', 'HW_Module_Settings_page::__init');
    }
endif;

/**
 * Class HW_Modules_Manager
 */
class HW__Modules_Manager {
    /**
     * @var array
     */
    protected $options = array();


    /**
     * main class constructor
     */
    function __construct() {

    }

    /**
     * list all displayable modules
     * @return array
     */
    public static function get_modules_displayable() {
        $data = array();
        $modules = HW_Module_Settings_page::get_modules_data();
        foreach($modules as $module) {
            if($module->option('enable_position')) {
                $meta = (array)$module->option('module_info');
                $data[$meta['slug']] = $meta['name'];
            }
        }
        return $data;
    }
    /**
     * Determine if the current user may use the menu editor.
     *
     * @return bool
     */
    public function current_user_can_edit_menu(){
        #$access = $this->options['plugin_access'];

        if ( is_super_admin() ) {
            return true;
        }/*else if ( $access === 'specific_user' ) {
            return get_current_user_id() == $this->options['allowed_user_id'];
        }*/else {
            $capability = apply_filters('hw-admin_menu_editor-capability', 'activate_plugins');
            return current_user_can($capability);
        }
    }


}
if(is_admin() || class_exists('HW_CLI_Command', false)) {
    new HW__Modules_Manager;
}