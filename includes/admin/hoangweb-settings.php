<?php
#root>includes/hoangweb-core.php

if ( ! defined( 'ABSPATH' ) )
    exit;

if ( ! class_exists( 'AdminPageFramework' ) ) {
    #include_once(HW_HOANGWEB_PATH.'/lib/admin-page-framework/admin-page-framework.min.php');    //require admin page framework
    HW_HOANGWEB::load_class('AdminPageFramework');
}
//load hw_table_fields type
HW_HOANGWEB::load_fieldtype('APF_hw_table_fields');
HW_HOANGWEB::load_class('HW_POST');  //register_class('HW_POST', HW_HOANGWEB_PATH.'/classes/hw_posts.class.php');
if(function_exists('hwskin_load_APF_Fieldtype')) hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);
/**
 * Interface HW_AdminPageFramework_Interface
 */
interface HW_AdminPageFramework_Interface {
    /**
     * @param $menu_arg
     * @return mixed
     */
    public static function valid_custom_submenu($menu_arg);
}

/**
 * Interface HW_APF_Field_Page_Interface
 */
interface HW_APF_Field_Page_Interface {
    /**
     * @param $tab
     * @param $page_slug
     * @return mixed
     */
    public function add_tab( $tab, $page_slug );

    /**
     * @param $name
     * @return mixed
     */
    public function create_field_name($name);
}

/**
 * Interface HW_AdminPage_MenuItem_Interface
 */
interface HW_AdminPage_MenuItem_Interface {
    /**
     * menu page title
     * @return mixed
     */
    public function menu_title();

}

/**
 * Interface HW_Settings_AdminPage_Interface
 */
interface HW_Settings_AdminPage_Interface {
    /**
     * @param AdminPageFramework $oAdminPage
     * @return mixed
     */
    public function replyToAddFormElements($oAdminPage=null);
    /**
     * receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     * @return mixed
     */
    public function validation_tab_filter($values);
    /**
     * triggered before rendering the page.
     * @return mixed
     */
    public function do_before_tab_hook();

    /**
     * triggered in the middle of rendering the page.
     * @return mixed
     */
    public function do_tab_hook();

    /**
     * triggered after rendering the page
     * @return mixed
     */
    public function do_after_tab_hook();
    /**
     * receives the output of the middle part of the page including form input fields.
     * @return mixed
     */
    public function content_tab_filter($content);

    /**
     * @param $name
     * @return mixed
     */
    public function create_full_field_name($name);

    /**
     * @param $name
     * @param string $value
     * @return mixed
     */
    public function get_field_value($name, $value='');

    /**
     * @return mixed
     */
    public function get_values();
    /**
     * @return mixed
     */
    public function before_fields();

    /**
     * @return mixed
     */
    public function after_fields();

    /**
     * @return mixed
     */
    public function print_styles();

    /**
     * @return mixed
     */
    public function print_scripts();
}

/**
 * Class HW_APF_Field_Page
 */
abstract class HW_APF_Field_Page extends HW_Core implements HW_APF_Field_Page_Interface{
    /**
     * section
     * @var null
     */
    public $tab = null;
    /**
     * AdminPageFramework
     * @var null
     */
    public $oAdminPage = null;

    /**
     * @param $section_tab
     */
    public function __construct($section_tab) {
        #parent::__construct();
        $this->tab = $section_tab;
    }
    /**
     * @param array $aField
     */
    public function addField($aField = array()) {
        if(empty($this->oAdminPage) || !$this->oAdminPage instanceof AdminPageFramework) return ;
        //validation
        if(!isset($aField['field_id'])) $aField['field_id'] = uniqid(HW_String::generateRandomString(5));   //random field id
        if(isset($aField['field_id']) && empty($this->tab)) {
            $aField['field_id'] = $this->create_field_name($aField['field_id']);
        }
        $aField['module_ref'] = $this;  //store module reference
//if($aField['field_id']=='submit_button2')_print($this->tab);
        if(empty($this->tab) ) $this->oAdminPage->addSettingFields($aField);
        else $this->oAdminPage->addSettingFields($this->tab['section_id'], $aField);
    }
    /**
     * generate field name for current module tab
     * @param $name
     * @return string
     */
    public function create_field_name($name) {
        return  HW_Validation::valid_apf_slug($name);
    }

    /**
     * pure fields values result
     * @param array $values
     * @return array
     */
    public function pure_fields_result($values = array()) {
        if(empty($this->tab)) {
            $prefix_module_field = ($this->create_field_name(''));
            $module_data = array();

            foreach($values as $field => $value) {
                if(strpos($field, $prefix_module_field) === 0) {
                    $module_data[preg_replace('#^('.$prefix_module_field.')#','', $field)] = $value;
                }
            }
            return $module_data ;
        }
        return $values ;
    }
    /**
     * add more fields
     */
    public function addFields() {
        $args = func_get_args();
        foreach ($args as $arg) {
            if(is_array($arg) && isset($arg['type'])) {
                if($arg['type'] == 'hw_help') $arg['module'] = $this;
                $this->addField($arg);
            }
        }
    }
    /**
     * add tab
     * @param $tab
     * @return mixed
     */
    public function add_tab( $tab, $page_slug){}
    /**
     * add label field
     * @param $title
     */
    public function addFieldLabel($title ) {
        $field = array(
            'field_id' => uniqid(HW_String::generateRandomString(5)),
            'type' => 'hw_apf_field',
            'show_title_column' =>false,
        );
        if(is_string($title)) $field['description'] = "<h2 class='hw-apf-field-label'>{$title}</h2>";
        elseif(is_array($title)) $field = array_merge($field, $title);

        $this->addField($field);
    }

    /**
     * add break line
     */
    public function addBreakLine() {
        $this->addField(array(
            'field_id' => uniqid(HW_String::generateRandomString(5)),
            'type' => 'hw_html',
            'show_title_column' => false,
            'description' => '<hr/>'
        ));
    }
    /**
     * add apf html field
     * @param $callback callable
     */
    public function addHTML($callback) {
        $this->addField(
            array(
                'field_id' => uniqid('html'),
                'type' => 'hw_html',
                'title'=>'',
                'show_title_column' => false,
                'output_callback' => $callback
            )
        );

    }
    /**
     * add submit buttons
     */
    public function add_submit_buttons() {
        $enable_submit = $this->option('enable_submit_button');
        if(!$enable_submit) return;
        if(!empty($this->tabs)) {
            foreach($this->tabs as $id => $tab) {
                $tab->addField(array(
                    'field_id' => 'submit_button',   #'submit_button_' .$id,
                    'type' => 'submit',
                    'show_title_column' => false,
                ));
            }
        }
        else {
            $this->addField(array(
                'field_id' => 'submit_button',   #'submit_button_' .$id,
                'type' => 'submit',
                'show_title_column' => false,
            ));
        }
    }
    /***
     * add/get option
     * @param $name
     * @param string $value
     * @param bool $merge_array
     */
    public function option($name, $value = '', $merge_array = false) {
        return $this->_option($name, $value ,$merge_array);
    }
    /**
     * enable submit button
     * @param bool $enable
     */
    public function enable_submit_button($enable = true) {
        $this->option('enable_submit_button', $enable);
    }
}

/**
 * Class HW_Settings_Field
 */
class HW_Settings_Field extends HW_APF_Field_Page {
    /**
     * create full field name for current module
     * @param $name
     * @return string
     */
    public function create_full_field_name($name) {
        return 'HW_HOANGWEB_Settings['.$this->create_field_name($name).']';
    }
    /**
     * get field value for current module
     * @param array|string $name
     * @param $value
     * @return array|mixed|null|void
     */
    public function get_field_value($name, $value='') {
        if(!empty($this->tab)) {
            $fields = HW_HOANGWEB_Settings::get_field_value( $this->tab['section_id']);
            return $fields && isset($fields[$name])? $fields[$name] : $value;
        }
        return HW_HOANGWEB_Settings::get_field_value($this->create_field_name($name), $value);
    }
    /**
     * get all fields values
     * @return array|mixed|null|void
     */
    public function get_values() {
        if(!empty($this->tab)) {
            $fields = HW_HOANGWEB_Settings::get_field_value( $this->tab['section_id']);
            return $fields;
        }
        $values = HW_HOANGWEB_Settings::get_values();
        return $this->pure_fields_result($values);
    }

    /**
     * add tab
     * @param $tab
     * @param $page_slug
     * @return mixed|void
     */
    public function add_tab($tab, $page_slug) {

    }
}

/**
 * Class HW_Settings_AdminPage
 */
abstract class HW_Settings_AdminPage extends HW_Settings_Field implements HW_Settings_AdminPage_Interface{

    /**
     * set option
     * enable tab setting
     */
    public function enable_tab_settings() {
        $this->option('enable_tab_settings', true);
    }

    /**
     * receives the output of the JavaScript script applied to the page of the slug.
     */
    public function print_scripts(){}

    /**
     * receives the output of the CSS rules applied to the page of the slug.
     */
    public function print_styles(){}
}
/**
 * Class HW_AdminPage_MenuItem
 */
abstract class HW_Settings_AdminPage_MenuItem extends HW_Settings_AdminPage implements HW_AdminPage_MenuItem_Interface{
    /**
     * sub menu info
     * @var
     */
    private $menuItem = array();
    /**
     * @param $section_tab
     */
    public function __construct($section_tab='') {
        parent::__construct($section_tab='');
        //default fields to support
        $this->support_fields('hw_html');
    }
    /**
     * list allow fields type for the module
     * @param $fields
     */
    public function support_fields($fields) {
        HW_APF_FieldTypes::apply_fieldtypes($fields, 'HW_HOANGWEB_Settings');

    }

    /**
     * @param $tab
     * @param $page_slug
     * @return mixed|void
     */
    public function add_tab( $tab, $page_slug) {

    }
    /**
     * @param $menu
     * @return mixed
     */
    public function setMenuItem($menu='') {
        if(is_array($menu) && isset($menu['page_slug'])) $this->menuItem= $menu;
        return $this->menuItem;
    }
    /**
     * init menu page item
     */
    public static function init() {
        $child = get_called_class();
        $inst = new $child();
        HW_HOANGWEB_Settings::add_submenu($child, $inst);
    }

    /**
     * placeholder
     * @param AdminPageFramework $oAdminPage
     * @return mixed|void
     */
    public function replyToAddFormElements($oAdminPage=null){

    }
    /**
     * cover from plugins/module.php
     * @param AdminPageFramework $oAdminPage
     * @return mixed|void
     */
    public function _replyToAddFormElements($oAdminPage){
        $this->oAdminPage = $oAdminPage;
        $this->before_fields();
        if(method_exists($this, 'replyToAddFormElements')) {
            $this->replyToAddFormElements($oAdminPage);
        }
        $this->after_fields();
    }
    /**
     * receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     * @return mixed
     */
    public function _validation_tab_filter($values){
        #$values = $this->pure_fields_result($values);  //please don't remove prefix module from field name, this cause damany your data
        $values = $this->validation_tab_filter($values);
        return $values;
    }
    /**
     * @return mixed|void
     */
    public function do_before_tab_hook() {

    }
    /**
     * triggered in the middle of rendering the page.
     * @return mixed
     */
    public function do_tab_hook(){}

    /**
     * triggered after rendering the page
     * @return mixed
     */
    public function do_after_tab_hook(){}
    /**
     * receives the output of the middle part of the page including form input fields.
     * @return mixed
     */
    public function content_tab_filter($content){
        return $content;
    }

    /**
     * @return mixed|void
     */
    public function before_fields() {}

    /**
     * insert after all fields
     */
    public function after_fields() {
        if(empty($this->oAdminPage) || !$this->oAdminPage instanceof AdminPageFramework) return ;

        if($this->_get_option('enable_submit_button')) {
            //add submit button
            $this->add_submit_buttons();
        }
    }

    /**
     * register submenu item
     */
    public static function register_menu() {
        HW_HOANGWEB_Settings::register_submenu(get_called_class());
    }
}
/**
 * Class HW_AdminPageFramework
 */
if(class_exists('AdminPageFramework')):
abstract class HW_AdminPageFramework extends AdminPageFramework implements HW_AdminPageFramework_Interface{
    /**
     * @var
     */
    public static $setting_pages = array();
    /**
     * @var array
     */
    public static $menus = array();

    /**
     * main constructor
     */
    public function __construct() {
        parent::__construct();
        $this->save_singleton();
    }
    /**
     * get root menu settings for this page
     * @param $menu global wp menu
     * @return mixed
     */
    public static function &get_root_wp_menu_data(&$menu) {
        //$root = new HW_Array_List_items_path($menu, __CLASS__);
        //$parent = &$root->get_search_item(0,$menu);
        $class = get_called_class();
        //return $parent;
        return $menu[$class];
    }

    /**
     * store admin page class object
     */
    final public function save_singleton() {
        if(!isset(self::$setting_pages[get_called_class()])) {
            self::$setting_pages[get_called_class()] = $this;
        }
    }

    /**
     * @param $class
     * @return mixed
     */
    public static function get_singleton($class) {
        if(isset(self::$setting_pages[$class])) return self::$setting_pages[$class];
    }
    /**
     * add custom submenu link under __CLASS__ menu
     * @param $menu
     */
    public static function valid_custom_submenu($menu) {
        if(count($menu) <3) return ;
        $menu = apply_filters('hw_valid_custom_submenu', $menu);

        //special wp core file
        $wp_core_files = array(
            'options-general.php', 'admin.php','tools.php','import.php', 'export.php','users.php','user-new.php','profile.php',
            'plugins.php', 'plugin-install.php','plugin-editor.php','theme-editor.php','customize.php',
            'nav-menus.php','widgets.php','themes.php','edit-comments.php','media-new.php','upload.php',
            'edit-tags.php','edit.php','post-new.php','update-core.php',
        );
        if($menu[0] == 'edit_theme_options' ) {
            switch($menu[1]) {
                case 'custom-header':
                    $menu[2] = admin_url('customize.php?return=&autofocus[control]=header_image');
                    return $menu;
                case 'custom-background':
                    $menu[2] = admin_url('customize.php?return=&autofocus[control]=background_image');
                    return $menu;

            };
        }
        //check wp core file
        if(in_array(trim($menu[2]) , $wp_core_files) ) {
            $menu[2] = admin_url($menu[2]);
            return $menu;
        }
        foreach($wp_core_files as $file) {
            if(preg_match("%^{$file}%", $menu[2])) {
                $menu[2] = admin_url($menu[2]);
                return $menu;
            }
        }
        $url = explode('?', $menu[2]);
        if(!HW_Validation::hw_valid_url($menu[2]) && preg_match('#\.php$#', trim(reset($url)) )) {
            $menu[2] = admin_url('admin.php?page=' .$menu[2]);
        }
        elseif(!HW_Validation::hw_valid_url($menu[2])) {
            $menu[2] = admin_url('options-general.php?page='. $menu[2]);
        }

        return $menu;
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
     * @param $name
     * @param $menu_inst
     */
    public static function add_submenu($name, $menu_inst) {
        $child = get_called_class();
        if(isset($child::$menus) && !isset($child::$menus['menus_page'])) {
            $child::$menus['menus_page']= array();
        }
        if(!isset($child::$menus['menus_page'][$name])) {
            $child::$menus['menus_page'][$name] = $menu_inst;
        }
    }

    /**
     * @param $name
     * @return HW_AdminPage_MenuItem
     */
    public static function get_submenus($name='') {
        $child = get_called_class();
        if(!isset($child::$menus)) return ;
        if($name ) return isset($child::$menus) && isset($child::$menus['menus_page'][$name])? $child::$menus['menus_page'][$name] : null;
        return $child::$menus['menus_page'];
    }
    /**
     * add module tab menu
     * @param $module
     * @param $menu
     */
    public static function add_custom_submenu_page($name, $menu) {
        $child = get_called_class();
        if(isset($child::$menus) && !isset($child::$menus['menus'])) {
            $child::$menus['menus']= array();
        }
        if(!isset($child::$menus['menus'][$name])) {
            $child::$menus['menus'][$name] = self::valid_custom_submenu($menu);
        }
    }
    /**
     * return setting page for module
     * @param $name
     * @param $action
     */
    public static function get_tab_setting_page($name, $action='', $args= array()) {
        $_args = array(
            'tab'       => HW_Validation::valid_apf_slug($name),
            'page'          => urlencode( self::PAGE_SLUG ),
            '_name'        => urlencode( $name ),
            'tab-nonce' => urlencode( wp_create_nonce( 'tab-nonce' ) ),
        );
        if(is_array($args)) $_args = array_merge($_args, $args);
        return wp_nonce_url(
            add_query_arg(
                $_args,
                #network_admin_url( 'options-general.php' )
                admin_url( 'admin.php' )
            ),
            $action
        );
    }
    /**
     * return all menus
     * @return array
     */
    public static function get_menus_data($item='') {
        $child=get_called_class();
        return $item? (isset($child::$menus) && isset($child::$menus[$item])? $child::$menus[$item]: null) : $child::$menus;
    }
    /**
     * get field value
     * @param array|string $name
     * @param string $default_value
     * @return array|mixed|null|void
     */
    public static function get_field_value($name, $default_value=''){
        //ie receive setting field in section: $name=array( 'my_first_section', 'my_text_field', 0 )        #item 0 if value is array
        return AdminPageFramework::getOption( get_called_class(), self::valid_tab_slug($name), $default_value );
    }

    /**
     * return all fields values
     * @return array|mixed|null|void
     */
    public static function get_values() {
        return AdminPageFramework::getOption( get_called_class() );
    }

}
endif;

/**
 * Class HW_HOANGWEB_Settings
 */
class HW_HOANGWEB_Settings extends HW_AdminPageFramework {
    /**
     * setting page slug
     */
    const HW_SETTINGS_PAGE = 'hw_settings';
    const PAGE_SLUG = 'hw_settings';
    /**
     * tabs data
     * @var array
     */
    protected $tabs = array();
    /**
     * @var array
     */
    public  static $menus = array();
    /**
     * hooks actions
     */
    private function add_actions() {

        //hw_skin
        add_filter('hw_skin_skins_holders', array($this, '_hw_skin_skins_holders'), 10, 2);
        add_filter('hwskin_first_skin_data', array($this, '_hwskin_first_skin_data'), 10, 2);
    }

    /**
     * @param string|HW_AdminPage_MenuItem $menuItem
     *
     */
    public static function register_submenu($menuItem) {
        if(is_string($menuItem)) call_user_func(array($menuItem, 'init'));
    }

    /**
     * define tabs data
     * @param string $tab
     * @return array
     */
    public function get_tabs($tab = '') {
        //define tabs
        $this->tabs = array(
            'general' => array(
                'title'=> 'Cài đặt chung',
                'description' => 'Cài đặt chung.'
            ),
            APF_Page_Templates::SETTINGS_GROUP => array(
                'title' => 'Danh mục',
                'description' => 'Cài đặt category/taxonomy.',
                'init' => 'APF_Page_Templates::init'
            ),
            'my_posttype_settings' => array(
                'title' => 'Post Type',
                'description' => 'Cài đặt Post Type.',
            ),
            APF_Theme_Templates::SETTINGS_GROUP => array(
                'title' => 'Templates',
                'description' => 'Cài đặt templates cho theme.',
                'init' => 'APF_Theme_Templates::init'
            ),
            APF_WidgetFeatures::SETTINGS_GROUP => array(
                'title' => 'Widget features',
                'description' => 'Cài đặt Widget features',
                #'callback' => 'APF_WidgetFeatures::replyToAddFormElements'
                'init' => 'APF_WidgetFeatures::init'
            )
        );
        return $tab ? (isset($this->tabs[$tab])? $this->tabs[$tab] : null) : $this->tabs;
    }
    /**
     * setup form fields
     */
    public function setUp() {
        //prepare hooks
        $this->add_actions();

        // Set the root menu
        $this->setRootMenuPage( /*'Settings'*/'Hoangweb', HW_HOANGWEB_URL. '/images/ie-gear-icon.png');        // specifies to which parent menu to add.
        #$this->addSubMenuPage();//for add_submenu_page root page mean class name, here is: HW_HOANGWEB_Settings, see: class-tgm-hw-private-plugin-activation.php

        // Add the sub menus and the pages
        $this->addSubMenuItems(
            array(
                'title'    =>    'HW Cấu hình',        // the page and menu title
                'page_slug'    =>    self::HW_SETTINGS_PAGE         // the page slug
            )
        );
        //deploy registered submenus items
        foreach (self::get_submenus() as $class => $menu_inst) {
            $submenu = $menu_inst->setMenuItem();  //sub menu info
            //init
            //$menu_inst->oAdminPage = $this;   //no we do it in _replyToAddFormElements
            //get menu title
            if(method_exists($menu_inst, 'menu_title') && $menu_inst->menu_title()) {
                $title = $menu_inst->menu_title();
            }
            elseif(!empty($submenu['title'])) {
                $title = $submenu['title'];
            }
            else $title = $class;

            $this->addSubMenuItems(array(
                'title'=> $title,
                'page_slug' => $submenu['page_slug'],
                'strScreenIcon' => 'options-general',
            ));
            add_action( 'load_' . self::valid_tab_slug($submenu['page_slug']), array($menu_inst,'_replyToAddFormElements') );
            //validation fields
            add_filter('validation_'. self::valid_tab_slug($submenu['page_slug']), array($menu_inst, '_validation_tab_filter'));
            //triggered before rendering the page.
            add_action('do_before_'. self::valid_tab_slug($submenu['page_slug']), array($menu_inst, 'do_before_tab_hook'));

            // triggered in the middle of rendering the page.
            add_action('do_' .  self::valid_tab_slug($submenu['page_slug']), array($menu_inst, 'do_tab_hook'));

            //triggered after rendering the page
            add_action('do_after_'. self::valid_tab_slug($submenu['page_slug']), array($menu_inst, 'do_after_tab_hook'));
            /**
             * Filters
             */
            //receives the output of the middle part of the page including form input fields.
            add_filter('content_'. self::valid_tab_slug($submenu['page_slug']), array($menu_inst, 'content_tab_filter'));

            //receives the output of the CSS rules applied to the page of the slug.
            add_filter('style_'. self::valid_tab_slug($submenu['page_slug']), array($menu_inst, 'print_styles'));

            //receives the output of the JavaScript script applied to the page of the slug.
            add_filter('script_'. self::valid_tab_slug($submenu['page_slug']), array($menu_inst, 'print_scripts'));

        }

        //define default tab
        /*$this->addInPageTabs(
            self::HW_SETTINGS_PAGE,    // set the target page slug so that the 'page_slug' key can be omitted from the next continuing in-page tab arrays.
            array(
                'tab_slug'    =>    'general',    // avoid hyphen(dash), dots, and white spaces
                'title'        =>    __( 'Cài đặt chung' ),
                'description' => 'Cài đặt chung.'
            )

        );*/
        //get tabs
        foreach($this->get_tabs() as $slug => $tab) {
            $this->addInPageTabs(self::HW_SETTINGS_PAGE,  array(
                'tab_slug' => $slug,
                'title' => $tab['title'],
                'description' => $tab['description']
            ));
            if(isset($tab['init']) && is_callable($tab['init']) ) {
                call_user_func($tab['init'],  $slug, $tab, $this); //init tab
            }
            //add callback for tab content
            // load + page slug + tab slug
            elseif(isset ($tab['callback']) && is_callable($tab['callback']) ) {
                add_action( 'load_' . self::HW_SETTINGS_PAGE . '_' . self::valid_tab_slug($slug), $tab['callback'] );
            }
            //internal callback
            elseif( method_exists($this, 'replyToAddFormElements_tab_'.$slug )) {
                add_action( 'load_' . self::HW_SETTINGS_PAGE . '_' . self::valid_tab_slug($slug), array( $this, 'replyToAddFormElements_tab_'.$slug ) );
            }
            #add_action( 'load_' . self::HW_SETTINGS_PAGE , array( $this, 'replyToAddFormElements') ,10,2);

        }
        $this->setInPageTabTag( 'h2' );        // sets the tag used for in-page tabs

        //init fields
        /*$this->addSettingSections(
            self::HW_SETTINGS_PAGE, // target page slug
            array(
                'section_id' => 'general',
                'title' => 'Cài đặt Chung',
                'description' => 'Cài đặt chung.',
                'section_tab_slug' => 'setting_tabs',
                'repeatable'  => false,
            ),
            ,
            array(
                'section_id' => 'my_posttype_settings',
                'title' => 'Post Type',
                'description' => 'Cài đặt Post Type.',
                'section_tab_slug' => 'setting_tabs'
            ),
            array(
                'section_id' => APF_Theme_Templates::SETTINGS_GROUP,
                'title' => 'Templates',
                'description' => 'Cài đặt templates cho theme.',
                'section_tab_slug' => 'setting_tabs'
            ),
            array(
                'section_id' => APF_WidgetFeatures::SETTINGS_GROUP ,
                'title' => 'Widget features',
                'description' => 'Cài đặt Widget features',
                'section_tab_slug' => 'setting_tabs'
            )
        );*/
        if(HW_HOANGWEB::is_current_screen('hw_settings') ) {

        }

    }

    /**
     * @hook load + page slug + tab slug
     * @param $oAdminPage
     */
    public function replyToAddFormElements_tab_general($oAdminPage) {
        if(HW_HOANGWEB::is_current_screen('hw_settings') ) {
            //general setting tab
            $this->setUp_tab_general($oAdminPage, 'general'); //general setting
        }
    }

    /**
     * @hook load + page slug + tab slug
     * @param $oAdminPage
     */
    public function replyToAddFormElements_tab_my_posttype_settings($oAdminPage) {
        if(HW_HOANGWEB::is_current_screen('hw_settings') ) {
            //post type tab
            $this->setUp_tab_posttype($oAdminPage, 'my_posttype_settings');    //post type setting
        }
    }
    /**
     * @hook load + page slug
     * @param $tab
     */
    public function replyToAddFormElements($oAdminPage) {
        # return ;
    }


    /**
     * post types setting tab
     * @param $oAdminPage AdminPageFramework
     * @param $tab_id
     */
    protected function setUp_tab_posttype($oAdminPage, $tab_id) {
        /**
         * post type tab
         */
        $post_types = get_post_types( '', 'names' );
        if(function_exists('is_plugin_active') && is_plugin_active('codepress-admin-columns/codepress-admin-columns.php')) {
            $tip1 = 'Nhấn <a href="'.admin_url('options-general.php?page=codepress-admin-columns').'" target="_blank">vào đây</a> nếu muốn hiển thị nhiều cột hơn.';
        }
        else $tip1 = 'Kích hoạt plugin '.hw_install_plugin_link('codepress-admin-columns/codepress-admin-columns.php','<em>codepress-admin-columns</em>').' để cài đặt nhiều cột hơn. ';

        $fields = array();
        $tab = $this->get_tabs($tab_id);
        $oAdminPage->addSettingField($tab_id); //group
        $oAdminPage->addSettingSections(self::HW_SETTINGS_PAGE ,
            array(
            'section_id' => $tab_id ,
            'title' => $tab['title'],
            'description' => $tab['description'],
            'section_tab_slug' => 'setting_tabs',
            'repeatable'  => false,
        ));

        $oAdminPage->addSettingField(array(
            'field_id' => 'show_posts_thumbnail_column',
            'type' => 'checkbox',    //'posttype',
            'title' => 'Hiển thị feature image',
            'description' => 'Hiển thị thumbnail/feature image trong trang quản lý dữ liệu của post types. '.$tip1,
            //'label' => $post_types,
            'select_all_button' => true,
            'select_none_button' => true,
            'label' => $post_types
        ));
        APF_Related_templates::register_field($oAdminPage);

        //submit button
        $oAdminPage->addSettingFields(
            $tab_id,
            array(
                'field_id' => 'submit',
                'type' => 'submit',
                'label' => 'Lưu lại',
                'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            )
        );
    }
    /**
     * general tab
     * @param $oAdminPage AdminPageFramework
     * @param $tab_id
     */
    private function setUp_tab_general($oAdminPage, $tab_id) {
        /**
         * general tab
         */
        //$post_types = get_post_types( '', 'names' );
        //custom menu labels tip
        $custom_admin_menu_tip = '<ul>';
        if(function_exists('is_plugin_active') && is_plugin_active('adminimize/adminimize.php')) {
            $custom_admin_menu_tip .= '<li>Truy cập <a href="'.admin_url('options-general.php?page=adminimize/adminimize.php').'" target="_blank">vào đây</a> để tùy biến.</li>';
        }
        else {
            $custom_admin_menu_tip = '<li>Kích hoạt plugin '.hw_install_plugin_link('adminimize/adminimize.php','<em>adminimize</em>').' để tùy biến.</li>';
        }

        if(function_exists('is_plugin_active') && is_plugin_active('admin-menu-editor/menu-editor.php')) {
            $custom_admin_menu_tip .= '<li>Truy cập <a href="'.admin_url('options-general.php?page=menu_editor').'" target="_blank">vào đây</a> để tùy biến.</li>';
        }
        else {
            $custom_admin_menu_tip .= '<li>Kích hoạt plugin '.hw_install_plugin_link('admin-menu-editor/menu-editor.php','<em>adminimize</em>').' để tùy biến.</li>';
        }
        $custom_admin_menu_tip .= '</ul>';

        $tab = $this->get_tabs($tab_id);
        $oAdminPage->addSettingSections(self::HW_SETTINGS_PAGE ,array(
            'section_id' => $tab_id,
            'title' => $tab['title'],
            'description' => $tab['description'],
            'section_tab_slug' => 'setting_tabs',
            'repeatable'  => false,
        ));
        $oAdminPage->addSettingFields(
            $tab_id,
            array(
                'field_id' => 'enable_developer_feature',
                'type' => 'checkbox',
                'title' => 'Kích hoạt tính năng phát triển',
                'description' => 'Hiển thị các tính năng nâng cao dành cho Developer.'.$custom_admin_menu_tip
            ),
            array(
                'field_id' => 'match_occurence',
                'type' => 'select',
                'title' => 'Phát hiện điều kiện.',
                'label' => array(
                    'first_occurence' => __('Điều kiện đầu tiên'),
                    'last_occurence' => __('Điều kiện sau cùng')
                )
            ),
            array(
                'field_id' => 'allow_uploadfile_type',
                'type' => 'checkbox',
                'title' => 'Cho phép upload kiểu tệp',
                'description' => 'Hiển thị các tính năng nâng cao dành cho Developer.',
                'label' => hw_list_mines_type(),
                'select_all_button' => true,
                'select_none_button' => true,
            ),
            array(
                'field_id' => 'submit',
                'type' => 'submit',
                'label' => 'Lưu lại',
                'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            )
        );
    }


    /**
     * callback for compatible vars, but nerver use
     * @return WP_Query
     */
    public function _get_wp_query() {
        global $wp_query;
        return $wp_query;
    }

    /**
     * @hw_hook filter hw_skin_skins_holders
     * @param $skins_holders: extend skin holder folder
     * @param $hw_skin: current HW_SKIN instance
     */
    public function _hw_skin_skins_holders($skins_holders, $hw_skin) {
        $config = $hw_skin->get_config(false);
        if($config['skin_folder'] == 'hw_loop_skins') {
            $hwtpl_path = WP_PLUGIN_DIR.'/hw-taxonomy-post-list-widget/';

            //hw taxonomy post list skins
            if(isset($skins_holders[$hwtpl_path])) {
                $skins_holders[$hwtpl_path]['group'] = 'HW_TPL';  //modify group name
            }
            else {
                $skins_holders[$hwtpl_path] = array(
                    'folder' => 'skins',
                    'url' => 'get_ref_plugin_url',//$hwtpl_url,
                    'group' => 'HW_TPL'
                );
            }
        }
        return $skins_holders;
    }
    /**
     * filter each skin data for at first
     * @hw_hook filter hwskin_first_skin_data
     * @param $temp_skin
     * @param $folder
     * @return mixed
     */
    public function _hwskin_first_skin_data($temp_skin, $folder){
        if(isset($folder['group']) && $folder['group'] == 'HW_TPL'){  //extend skins folder
            $temp_skin['holder_url'] = WP_PLUGIN_URL.'/hw-taxonomy-post-list-widget/';
            $temp_skin['skin_url'] = WP_PLUGIN_URL.'/hw-taxonomy-post-list-widget/'.$temp_skin['skin_folder'].'/'.$temp_skin['path'];
            $temp_skin['screenshot_url'] = $temp_skin['skin_url'].'/'.$temp_skin['screenshot'];
        }
        return $temp_skin;
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_hw_settings() {
        // Show the saved option value.
        //$taxonomies_template = hw_get_setting(array(APF_Page_Templates::SETTINGS_GROUP,'taxonomies_template'));
        // The extended class name is used as the option key. This can be changed by passing a custom string to the constructor.
        /*echo '<h3>Saved Values</h3>';
        echo '<h3>Show as an Array</h4>';
        echo $this->oDebug->getArray( get_option( 'APF_CreateForm' ) );
        echo '<h3>Retrieve individual field values</h4>';
        echo '<pre>APF_CreateForm[my_first_section][my_text_field][0]: ' . AdminPageFramework::getOption( 'APF_CreateForm', array( 'my_first_section', 'my_text_field', 0 ), 'default' ) . '</pre>';
        echo '<pre>APF_CreateForm[my_second_section][my_dropdown_list]: ' . AdminPageFramework::getOption( 'APF_CreateForm', array( 'my_second_section', 'my_dropdown_list' ), 'default' ) . '</pre>';
        */
    }
    /**
     * The pre-defined validation callback method.
     *
     * Notice that the method name is validation_{instantiated class name}_{field id}. You can't print out inside callback but stored in session variale instead
     *
     * @param    string|array    $sInput        The submitted field value.
     * @param    string|array    $sOldInput    The old input value of the field.
     */
    public function validation_HW_HOANGWEB_Settings( $sInput, $sOldInput ) {

        /*
        // Set a flag
        $_fIsValid = true;

        // Prepare an field error array.
        $_aErrors = array();

        // Use the debug method to see what are passed.
        // $this->oDebug->logArray( $sInput );

        // Check if a url is passed
        if ( ! filter_var( $sInput, FILTER_VALIDATE_URL ) ) {

            $_fIsValid = false;

            // $variable[ 'field_id' ]
            $_aErrors['url'] = __( 'The value must be a url:', 'admin-page-framework-tutorials' ) . ' ' . $sInput;

        }

        // An invalid value is found.
        if ( ! $_fIsValid ) {

            // Set the error array for the input fields.
            $this->setFieldErrors( $_aErrors );
            $this->setSettingNotice( __( 'There was something wrong with your input.', 'admin-page-framework-tutorials' ) );
            return $sOldInput;

        }
        */
        return $sInput;

    }
    /**
     * Validates the submitted form data.
     *
     * Alternatively you may use validation_{instantiated class name} method.
     */
    public function validate( $aSubmit, $aStored, $oAdminWidget ) {

        // Uncomment the following line to check the submitted value.
        // AdminPageFramework_Debug::log( $aSubmit );

        return $aSubmit;

    }
    /**
     * initial
     */
    public static function init(){
        //if(class_exists('HW_SKIN')) hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);
        HW_HOANGWEB::load_class('APF_hw_table_fields');
        //init custom field type
        if(class_exists('APF_hw_table_fields')) {
            //new APF_hw_table_fields('HW_HOANGWEB_Settings');
            HW_APF_FieldTypes::apply_fieldtypes('hw_table_fields', 'HW_HOANGWEB_Settings');
            new HW_HOANGWEB_Settings();
        }

    }
}

if(is_admin() || is_call_behind()) {
    add_action('init', 'HW_HOANGWEB_Settings::init');
    include_once( 'hoangweb-admin.php');
}
//APF related templates field
include_once(HW_HOANGWEB_INCLUDES . '/settings/APF_related_templates.php');
include_once(HW_HOANGWEB_INCLUDES . '/settings/APF_taxonomies_templates.php');   //mutipulate tax templates
include_once(HW_HOANGWEB_INCLUDES . '/settings/APF_theme_templates.php');
include_once(HW_HOANGWEB_INCLUDES . '/settings/APF_widget-feature.php');