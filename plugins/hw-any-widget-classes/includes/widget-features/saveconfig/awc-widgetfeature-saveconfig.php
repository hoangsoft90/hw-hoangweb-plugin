<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/06/2015
 * Time: 07:32
 */
if(!class_exists('HWAWC_SaveWidgets_options')) {
    include_once ('awc-widgets-config.php');
}
/**
 * Class AWC_WidgetFeature_saveconfig
 */
if(class_exists('HW_AWC_WidgetFeature')):
class AWC_WidgetFeature_saveconfig extends HW_AWC_WidgetFeature{
    /**
     * db table to store data from the class
     */
    const DB_TABLE = 'hw_widgets_settings';

    /**
     * @var
     */
    protected static $widopt_groups;
    /**
     * @var
     */
    static $ajax_save_url;
    /**
     * @var
     */
    protected $groups = null;

    /**
     * @param WP_Widget $widget
     * @param null $instance
     */
    public function __construct($widget, $instance='') {
        parent::__construct($widget, $instance);
        $this->enable_tab_setting();
        $this->enable_submit_button();
        $this->support_fields('hw_admin_table');
    }
    /**
     * init feature
     * @param WP_Widget $widget
     * @return mixed|void
     */
    public function init(WP_Widget $widget) {

        add_action('hw_awc_widget_saveconfig_group', array($this, '_hw_awc_widget_saveconfig_group'));
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));   //

        //ajax handle
        add_action("wp_ajax_hwawc_widfea_saveconfig", array($this, "_ajax_hwawc_widfea_saveconfig") );
        add_action("wp_ajax_nopriv_hwawc_widfea_saveconfig", array($this, "_ajax_hwawc_widfea_saveconfig") );

        $nonce = wp_create_nonce("hwawc_widfea_saveconfig_nonce");
        self::$ajax_save_url = admin_url('admin-ajax.php?action=hwawc_widfea_saveconfig&widget_id='.$widget->id.'&nonce='.$nonce);

        self::$widopt_groups = array(
            'main_loop' => 'Lặp nội dung chính'
        );
        $this->remove_settings = true;
    }

    /**
     * after feature complete loaded
     * @return mixed|void
     */
    public function feature_loaded() {

    }
    public function is_active(){}
    /**
     * Triggered when the tab is loaded.
     * @param $oAdminPage
     */
    public function replyToAddFormElements($oAdminPage ='') {
        //you can get feature data belong to current widget that applying
        $this->addFields(array(
            'field_id' => 'list_saved_widgets_setting',
            'type' => 'hw_admin_table',
            'title' => '',
            'show_title_column' => false,
            'WP_List_Table' => 'HW_List_Table_Widgets_settings',
            //params
            'columns' => array(
                'id' => __('ID'),
                'name'=>__('Tên'),
                'group'=>__('Nhóm'),
                'widget' => __('Widget'),
                'description'=>__('Mô tả'),
                'setting' => __('Config')
            ),
            'sortable_columns' => array(
                'id' => array('id', false),     #true the column is assumed to be ordered ascending,
                'name' => array('name' ,false),     #if the value is false the column is assumed descending or unordered.
                'group' => array('group', false)
            )
        ));
        //actions
        if(class_exists('HW_HELP')) $page_hook = HW_HELP::load_settings_page_hook_slug(HW_Widget_Features_Setting::PAGE_SLUG);
        else $page_hook = 'load-settings_page_'.HW_Widget_Features_Setting::PAGE_SLUG;

        add_action( $page_hook, array($this, '_add_options') );
        if(class_exists('HW_HOANGWEB') && HW_HOANGWEB::is_current_screen('hw_widgets_settings')) {
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }
    }
    /**
     * @hook $page_hook
     */
    public function _add_options() {
        $option = 'per_page';   //wordpress api
        $args = array(
            'label' => 'Kết quả hiển thị',
            'default' => 10,
            'option' => 'hw_items_per_page'
        );
        add_screen_option( $option, $args );
    }
    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {
        HW_Libraries::enqueue_jquery_libs('jquery-colorbox');

    }
    /**
     * encode wiget config (refer to widget instance)
     * @param array $config
     */
    public static function encode_config($config) {
        return base64_encode(serialize($config));
    }

    /**
     * decode encoded widget config
     * @param string $encodeconfig
     */
    public static function decode_config($encodeconfig) {
        return is_string($encodeconfig)? @unserialize(base64_decode($encodeconfig)) : array();
    }
    /**
     * get data
     * @param $sql: extra sql to get right data
     */
    public static function get_widgets_settings($sql = '') {
        global $wpdb;
        $query = "SELECT * FROM ".self::DB_TABLE . $sql;
        $result = $wpdb->get_results($query);

        return $result;
    }

    /**
     * get widgets settings data for building select tag
     * @param extends from method 'get_widgets_settings'
     * @return array
     */
    public static function get_widgets_settings_select($sql = '') {
        $options = array(
            '' => 'Không dùng'
        );
        foreach(self::get_widgets_settings($sql) as $item) {
            $options[$item->id] = $item->id. '-'. $item->name;
        }
        return $options;
    }
    /**
     * return specific widget setting
     * @param $id: setting id
     * @return mixed
     */
    public static function get_widget_setting($id) {
        global $wpdb;
        $query = "SELECT * FROM ".self::DB_TABLE . ' WHERE id = "'.$id. '"';
        return $wpdb->get_row($query);
    }

    /**
     * return widget instance
     * @param $id: config id
     * @param array $match_config: match config
     * @return mixed
     */
    public static function get_widget_setting_instance($id, $match_config = array()) {
        $widg = self::get_widget_setting($id);

        //check condition
        $check_condition = true;

        foreach ($match_config as $config => $val){
            if(property_exists($widg, $config) && $widg->$config != $val) {
                $check_condition = false;
                break;
            }
        }
        //get  widget setting, ie 'hw_taxonomy_post_list_widget'
        if($check_condition && !empty($widg->setting) ) {
            $setting = self::decode_config($widg->setting);
        }
        //remove widget feature saveconfig
        $setting = self::remove_settings($setting);
        return $setting;
    }
    /**
     * add widget setting
     * @param array $data
     * @return false|int
     */
    private function add_widget_setting ($data = array()){
        global $wpdb;
        //valid
        if(empty($data['_group']) || !$this->valid_group($data['_group']) ) return;
        if(empty($data['setting'])) return;

        $wpdb->insert(self::DB_TABLE , $data);
        return $wpdb->insert_id;
    }
    /**
     * delete widget setting
     * @param $id
     */
    public static function del_widget_setting($id) {
        global $wpdb;
        $wpdb->query('DELETE FROM '.self::DB_TABLE. ' WHERE id='.$id);
    }
    /**
     * valid feature config group
     * @param $name
     * @return bool
     */
    private function valid_group($name) {
        if(!empty($this->groups )) return in_array($name, array_keys($this->groups) );
        return true;    //pass default
    }

    /**
     * return widget setting groups
     * @return mixed
     */
    public static function get_groups() {
        return self::$widopt_groups;
    }

    /**
     * set widget setting groups
     * @param array $groups
     */
    public static function set_groups($groups = array()) {
        if(is_array($groups)) self::$widopt_groups = array_merge(self::$widopt_groups , $groups);
    }
    /**
     * ajax callback
     */
    public function _ajax_hwawc_widfea_saveconfig() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hwawc_widfea_saveconfig_nonce")) {
            exit("No naughty business please");
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            $setting = isset($_POST['setting'])? $_POST['setting'] : '';
            if(!empty($setting)) {  //remove setting from this widget feature
                $setting = AWC_WidgetFeature_saveconfig::decode_config($setting);
                $setting = $this->remove_settings($setting);
                //encode widget settings
                $setting = AWC_WidgetFeature_saveconfig::encode_config($setting) ;
            }

            $data = array(
                'name' => isset($_POST['name'])? $_POST['name'] : '',
                '_group' => isset($_POST['group'])? $_POST['group'] : '',
                'setting' => $setting,
                'description' => isset($_POST['description'])? $_POST['description'] : '',
                'widget' => isset($_POST['widget'])? $_POST['widget'] : ''
            );
            $insert_id = $this->add_widget_setting($data);
            echo $insert_id;    //return new id inserted to database
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

    public function _hw_check_authorize() {
        echo "anonymous.";
        die();
    }
    /**
     * @hook 'hw_awc_widget_saveconfig_group'
     * @param $widopt_groups
     */
    public function _hw_awc_widget_saveconfig_group($widopt_groups = array()) {
        for($i=1; $i <=5; $i++) self::$widopt_groups['group-'.$i] = 'Nhóm '. $i;
        $this->groups = self::$widopt_groups; //save groups
        return self::$widopt_groups;
    }

    /**
     * enqueue scripts/stylesheets in admin
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts() {
        if(HW_HOANGWEB::is_current_screen('hw_widgets_settings')) {
            wp_enqueue_script('hw_awc_widgetfeature_js', plugins_url('scripts.js', __FILE__));
            wp_localize_script('hw_awc_widgetfeature_js', '__hw_awc_widgetfeature_saveconfig', array());
        }
    }
    /**
     * widget feature form
     * yes, since saveconfig purpose for HW Taxonomy post list widget
     * @param $widget
     * @param $instance
     */
    public function do_widget_feature($t,$instance = array()) {
        include('template/widget_feature-saveconfig.php');
    }
    public function do_widget_feature_frontend($widget, $instance) {

    }
    /**
     * remove this widget features params
     * @param $instance: widget instance
     */
    public function remove_settings($instance) {
        $fields = array('hw_widopt_group', 'hw_widopt_name', 'hw_widopt_desc', 'hw_widopt_setting');
        if(is_array($instance) ) {
            foreach( $fields as $field) {
                if(isset($instance[$field])) unset($instance[$field]);
            }
        }
        return $instance;
    }
    /**
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed|void
     */
    function validation($instance,$new_wf_instance, $old_instance) {
        $new_wf_instance = $this->remove_settings($new_wf_instance); //do not save this feature data to current working widget
        //if(isset($new_wf_instance['hw_widopt_setting'])) unset($new_wf_instance['hw_widopt_setting']);
        return $new_wf_instance;
    }
}
endif;