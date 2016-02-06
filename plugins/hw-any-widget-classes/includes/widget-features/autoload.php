<?php
#/root>
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 16/06/2015
 * Time: 07:40
 */
/**
 * Interface HW_AWC_WidgetFeature_implement
 */
interface HW_AWC_WidgetFeature_implement {
    /**
     * @return mixed
     */
    public function is_active() ;

    /**
     * display widget feature form
     * @param $widget
     * @param $instance
     * @return mixed
     */
    public function do_widget_feature($widget ,$instance= array());

    /**
     * validate widget data
     * @return mixed
     */
    public function validation($instance,$new_wf_instance, $old_instance);

    /**
     * remove feature settings
     * @param array $instance feature instance
     * @return mixed
     */
    public function remove_settings($wf_instance) ;

    /**
     * do in frontend
     * @return mixed
     */
    public function run_frontend();

    /**
     * do widget feature on frontend before load widget content
     * @return mixed
     */
    public function do_widget_feature_frontend($widget, $instance);
    /**
     * call buttom of construct method
     * @return mixed
     */
    public function init(WP_Widget $widget) ;

    /**
     * @return mixed
     */
    public function feature_loaded() ;
    /**
     * @param $oAdminPage
     * @return mixed
     */
    public function replyToAddFormElements($oAdminPage='');
    /**
     * receives the form submission values as array. The first parameter: submitted input array. The second parameter: the original array stored in the database.
     * @return mixed
     */
    public function validation_tab_filter($values);
    /**
     * receives the output of the middle part of the page including form input fields.
     * @return mixed
     */
    public function content_tab_filter($content);
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
}

/**
 * Class HW_WidgetFeature_Field
 */
class HW_WidgetFeature_Field extends HW_Module_Field{
    /**
     * create full field name for current module
     * @param $name
     * @return string
     */
    public function create_full_field_name($name) {
        return 'HW_Widget_Features_Setting['.$this->create_field_name($name).']';
    }
    /**
     * get field value for current module
     * @param array|string $name
     * @param $value
     * @return array|mixed|null|void
     */
    public function get_field_value($name, $value='') {
        if(!empty($this->tab)) {
            $fields = HW_Widget_Features_Setting::get_field_value( $this->tab['section_id']);
            return $fields && isset($fields[$name])? $fields[$name] : $value;
        }
        return HW_Widget_Features_Setting::get_field_value($this->create_field_name($name), $value);
    }
    /**
     * get all fields values
     * @return array|mixed|null|void
     */
    public function get_values() {
        if(!empty($this->tab)) {
            $fields = HW_Widget_Features_Setting::get_field_value( $this->tab['section_id']);
            return $fields;
        }
        $values = HW_Widget_Features_Setting::get_values();
        return $this->pure_fields_result($values);
    }
    /**
     * add new tab
     * @param array $tab
     * @return tab id
     */
    /*public function add_tab( $tab) {
        return $this->add_tab($tab, HW_Widget_Features_Setting::PAGE_SLUG);
    }*/
    /**
     * add new tab
     * @param AdminPageFrameWork $oAdminPage (depricated)
     * @param array $tab
     * @return tab id
     */
    public function add_tab( $tab ) {
        if(!isset($tab['id']) || !isset($tab['title'])) return;
        #if(empty($this->oAdminPage) || !$this->oAdminPage instanceof AdminPageFramework) return ;

        if(!isset($tab['description'])) $tab['description'] = 'Module '. $this->feature_name. ', tab: '. $tab['title'];
        $section = array(
            'section_tab_slug' => 'module_'. $this->feature_name .'_setting_tab',
        );

        foreach($tab as $key => $val) {
            if ($key == 'id'){
                $section['section_id'] = $this->feature_name.'_' .$tab['id'];
            } else
                $section[$key] = $val;
        }
        #$this->oAdminPage->addSettingField($tab['id']); //group, this allow you to place field in tab or non-tab
        if(!empty($this->oAdminPage) && $this->oAdminPage instanceof AdminPageFramework) {
            $this->oAdminPage->addSettingSections(HW_Widget_Features_Setting::PAGE_SLUG , $section);
        }

        $obj = new self($section);//new HW_Module_tab($tab);

        #$obj = $this->castAs($obj);
        $obj->oAdminPage = $this->oAdminPage;
        $obj->feature_name = $this->feature_name;
        $this->tabs[$tab['id']] = $obj; //add to manager
        return $obj;
    }
    /**
     * generate field name for current module tab
     * @param $name
     * @return string
     */
    public function create_field_name($name) {
        return HW_Validation::valid_apf_slug($this->feature_name) . "_". HW_Validation::valid_apf_slug($name);
        #return  $name . "[{$name}]";
    }
    /**
     * get real field name
     * @param $name
     * @return mixed
     */
    public function  real_field_name($name) {
        $prefix = HW_Validation::valid_apf_slug($this->feature_name);
        return preg_replace('#^'.$prefix.'_#', '', trim($name));
    }
}
/**
 * Class HW_AWC_WidgetFeature
 */
if(class_exists('HW_SKIN_Option')) :
abstract class HW_AWC_WidgetFeature extends /*HW_SKIN_Option*/HW_WidgetFeature_Field implements HW_AWC_WidgetFeature_implement{
    /**
     * @var string
     */
    static $wf_options_name = 'awcwf_options';

    /**
     * allow to save feature settings?
     * if set to true all settings of current widget feature will removed before save to db
     * @var
     */
    public $remove_settings = true;

    /**
     * feature name
     * @var null
     */
    public $feature_name = null;

    /**
     * feature url
     * @var null
     */
    public $feature_url = null;

    /**
     * feature path
     * @var null
     */
    public $feature_path = null;


    /**
     * widget object
     * @var
     */
    public $widget;
    /**
     * store widget instance
     * @var
     */
    public $widget_instance;

    /**
     * inherit args from 'do_widget_feature' method
     * @param $widget
     * @param $instance
     */
    public function __construct(WP_Widget $widget, $instance = null) {
        $this->widget = $widget;
        if($instance) $this->widget_instance = $instance;

        $this->init($widget);  //init for first
        //$this->do_widget_feature($widget, $instance); //moved to hw-sidebar-widgets-settings.php/::load_widget_feature
        $this->support_fields('hw_html');
    }

    /**
     * enable setting tab for feature
     */
    public function enable_tab_setting() {
        $this->_option('enable_tab_settings', true);
    }

    /**
     * add/get option
     * @param $name
     * @param $value
     * @param bool $merge_array
     */
    public function option($name, $value = '', $merge_array = false) {
        return $this->_option($name, $value , $merge_array);
    }
    /**
     * before user fields
     */
    public function before_fields() {
        //detect first widget for feature attachting
        $this->addFieldLabel( 'Widget: '.$this->widget->id);
    }
    /**
     * list allow fields type for the module
     * @param $fields
     */
    public function support_fields($fields) {
        HW_APF_FieldTypes::apply_fieldtypes($fields, 'HW_Widget_Features_Setting');
    }

    /**
     * @param string $oAdminPage
     */
    public function _replyToAddFormElements($oAdminPage) {
        $this->oAdminPage = $oAdminPage;
        $this->before_fields();
        if(method_exists($this, 'replyToAddFormElements')) {
            $this->replyToAddFormElements($oAdminPage);
        }
        $this->after_fields();
    }
    /**
     * placeholder
     * @param $oAdminPage
     * @return mixed
     */
    public function replyToAddFormElements($oAdminPage='') {}

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
     * @hook 'validation_'. self::PAGE_SLUG  . '_' . self::valid_tab_slug($name)
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {
        return $values;
    }
    /**
     * generate field id for current widget option
     * @param $field field name
     */
    public function get_field_id($field) {
        return $this->widget->get_field_id( $this->get_widget_feature_name('awcwf-')). '_'. $field;
    }

    /**
     * generate field name attr for current widget option
     * @param $field
     */
    public function get_field_name($field) {
        return $this->widget->get_field_name( $this->get_widget_feature_name('awcwf-')) . "[$field]";
    }
    /**
     * return widget inst
     * @return WP_Widget
     */
    public function get_widget_ref() {
        return $this->widget_instance;
    }

    /**
     * return value of widget feature field
     * @param $field field name
     * @param $default default value
     */
    public function get_field_value($field, $default = '', $instance = null) {
        $data = (!empty($instance) && is_array($instance))? $instance : $this->widget_instance;
        if(isset($data[ $this->get_widget_feature_name('awcwf-')][$field])) {
            return $data[ $this->get_widget_feature_name('awcwf-')][$field];
        }
        return $default;
    }

    /**
     * enqueue script
     * @param $file
     * @param array $dependencies
     * @param string $handle
     */
    public function enqueue_script($file, $dependencies = array(), $handle='') {
        //valid
        if(!is_array($dependencies)) $dependencies = array();

        //handle script name
        if(!$handle) $handle = $this->get_widget_feature_name('script-');
        $handle = md5('hw-widgetfeature-'. $handle);
        wp_enqueue_script(($handle), $this->get_file_url($file), $dependencies);
        return $handle;
    }

    /**
     * return valid path to file inside widget feature
     * @param $file
     * @return string
     */
    public function get_file_url($file) {
        if(filter_var($file, FILTER_VALIDATE_URL)) return $file;
        else {
            return $this->feature_url ."/". $file;
        }
    }
    /**
     * enqueue stylesheets
     * @param $file
     * @param array $dependencies
     * @param string $handle
     */
    public function enqueue_style($file, $dependencies = array(), $handle='') {
        //valid
        if(!is_array($dependencies)) $dependencies = array();

        //handle script name
        if(!$handle) $handle = $this->get_widget_feature_name('style-');
        $handle = md5('hw-widgetfeature-'. $handle);

        wp_enqueue_style($handle, $this->get_file_url($file) ,$dependencies);
        return $handle;
    }
    /**
     * localize script
     * @extend from HW_Module class
     * @param $handle
     * @param $object_name
     * @param array $data
     */
    public function localize_script($handle, $object_name, $data = array()) {
        //validation
        if(!is_array($data)) $data = array();
        #$module = $this->get_module_name();
        //$handle = 'hw-module-'.$module.'-'. md5(self::get_file_name($file));

        wp_localize_script($handle, HW_Validation::valid_objname($object_name) , $data);
    }
    /**
     * return options object for this widget feature
     */
    public static function get_options_definition() {
        $options = self::get_path('options.php');
        if(file_exists($options)) include ($options);
        if(isset($theme_options)) return $theme_options;
    }

    /**
     * get path of current widget feature resource
     * @param $file
     */
    public static function get_path($file) {
        $wf_path = HW_HOANGWEB::get_class_path(get_called_class());
        return rtrim($wf_path, '/') .'/'. $file;
    }
    /**
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed|void
     */
    public function validation($instance,$new_wf_instance, $old_instance) {
        $instance = $new_wf_instance;
        return $instance;
    }

    /**
     * filter feature settings before save to database
     * @param $wf_instance feature settings
     * @return mixed
     */
    public function remove_settings($wf_instance) {
        return $wf_instance;
    }
    /**
     * do in frontend, it override by parent class
     * @return mixed|void
     */
    public function run_frontend() {

    }

    /**
     * return current feature name
     * @return mixed
     */
    public function get_widget_feature_name($prefix = 'awcwf-') {
        #$class = get_called_class();
        return $prefix . $this->feature_name;
    }

    /**
     * whether actived current feature for current widget
     * @param $widget
     * @depricated obviously return true :D because it' this feature presenting on current widget ->stupid
     * @return bool
     */
    /*public function is_active_feature($widget) {
        return HW_AWC_WidgetFeatures::check_widget_feature($widget, $this->get_widget_feature_name(''));
    }*/
    /**
     * return options value for current feature
     * @return default|string
     */
    public function get_options_value() {
        return $this->get_field_value(self::$wf_options_name, array());
    }
    /**
     * build options
     * @param array $theme_options options in array
     */
    public function build_options($theme_options = array()) {
        if(empty($this->widget)) return;

        $base_fname = self::$wf_options_name;  //.$this->get_widget_feature_name().']';
        //get options values
        $options_value = $this->get_field_value($base_fname, array());

        //get field name/id
        $myfield_id = $this->get_field_id($base_fname) ;
        $myfield_name = $this->get_field_name($base_fname) ;

        $theme_options_output = array();

        if(isset($theme_options) && is_array($theme_options)) {
            $parse_id = md5(rand());

            $theme_options_output[] = '<hr/><div id="'.$parse_id.'">';

            //create fields
            foreach($theme_options as $_field){
                $theme_options_output[] = $this->renderOptionField(
                    $_field,
                    $myfield_name,
                    $myfield_id,
                    $options_value
                );

            }
            $theme_options_output[] = '</div>';     //close parse_id div tag

        }
        return implode("\n", $theme_options_output);
    }

    /**
     * whether current context is widgets page
     * @return bool
     */
    public static function is_widgets_page() {
        return HW_HOANGWEB::is_current_screen(array('widgets'));
    }
    /*public function do_widget_feature($widget ,$instance= array()){}
    public function init(WP_Widget $widget){}*/
    /**
     * @return mixed|void
     */
    public function feature_loaded(){}
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

}
endif;
/**
 * Class HW_AWC_WidgetFeatures
 */
class HW_AWC_WidgetFeatures {
    /**
     * register widgets fields
     * @var
     */
    private static $widgets_fields = array();

    /**
     * return $widgets_fields member
     * @param $feature
     * @return array
     */
    public static function get_features_data($feature='') {
        return $feature? (isset(self::$widgets_fields[$feature])? self::$widgets_fields[$feature]:'' ) : self::$widgets_fields;
    }
    /**
     * return registered widget features
     */
    public static function get_all_features() {
        $data = array();
        $features = HW_HOANGWEB::get_classes_by_group('widget-features');
        foreach($features as $item) {
            $feature_name = trim(self::get_feature_name_byClass( $item['class']));
            $data[$feature_name] = $item['alias'];
        }
        return $data;
    }

    /**
     * return feature class by name
     * @param $name
     * @return string
     */
    public static function get_feature_class_byName($name){
        return 'AWC_WidgetFeature_'. self::get_feature_name_byClass($name);
    }

    /**
     * extract feature name by class
     * @param $class
     */
    public static function get_feature_name_byClass($class) {
        return str_replace('AWC_WidgetFeature_','', $class);
    }
    /**
     * load widget fields feature
     * @param WP_WIdget $current  current widget
     * @param array $instance widget instance data
     */
    public function load_widget_features($current, $instance){
        foreach(self::$widgets_fields as $name => $widgets){
            foreach($widgets as $id=>&$widget){
                if($current->id == $id /*$widget['widget']->id*/){

                    /*if(       //old code
                    method_exists($this, 'do_widget_feature_'.$name)) {

                    }*/
                    //load widget feature from enternal
                    $class = self::get_feature_class_byName($name);  // 'AWC_WidgetFeature_'.$name;
                    if(class_exists($class) && (!is_object($widget['class']) || ! $widget['class'] instanceof HW_AWC_WidgetFeature)) {
                        $widget['class'] = new $class($widget['widget'], $instance);
                    }

                    if( is_object($widget['class']) ) {
                        //$widget['class'] = new $class($widget['widget'], $instance);
                        $widget['class']->widget_instance = $instance;    //addition data
                        $widget['class']->feature_loaded(); //make feature complete loaded
                        $widget['class']->do_widget_feature($widget['widget'], $instance, $widget['class']);  //display fields
                    }
                    //load widget feature from internal
                    elseif(method_exists($this, 'do_widget_feature_'.$name )) {
                        call_user_func(array($this, 'do_widget_feature_'.$name), $widget['widget'],$instance);
                    }
                }
            }

        }
    }
    /**
     * register widget fields feature
     * @param $widget: widget instance
     * @param $feature: give feature name by hoangweb
     */
    public static function register_widget_feature($widget,$feature){
        if(!isset(self::$widgets_fields[$feature])) self::$widgets_fields[$feature] = array();
        $register_features = HW_AWC_WidgetFeatures::get_all_features(); //get all register features
        //load widget feature from enternal
        $class = self::get_feature_class_byName($feature);  //'AWC_WidgetFeature_' . $feature;
        if(class_exists($class)) {
            $class = new $class($widget, array());
            $class->feature_name = $feature;    //widget feature identifier
            $class->feature_url = HW_AWC_WidgetFeatures_URL . '/' .$feature;
            $class->feature_path = HW_AWC_WidgetFeatures_PATH. '/' . $feature;
            $class->option('feature_alias', $register_features[$feature]);

            //set static options
            $class->_static_option('feature_name', $class->feature_name);
            $class->_static_option('feature_url', $class->feature_url);
            $class->_static_option('feature_path', $class->feature_path);

            $class->feature_loaded();
            //add widget feature to manager
            HW_Widget_Features_Setting::add_widget_feature($feature, $class);
            self::_setup_actions($class);
        }
        //prevent to duplicate widgets in one feature ($widget->id_base)
        self::$widgets_fields[$feature][$widget->id_base.'-'.$widget->number] = array('widget' => $widget, 'class' => $class );
    }

    /**
     * init hooks
     * @param $obj
     */
    protected static function _setup_actions($obj) {
        //validation
        if(! $obj instanceof HW_AWC_WidgetFeature) return;
        /**
         * enqueue scripts on frontend
         */
        if(method_exists($obj, 'enqueue_scripts'))
            add_action('wp_enqueue_scripts', array($obj, 'enqueue_scripts'));
        /**
         * load stuffs in admin page
         */
        if(method_exists($obj, 'admin_enqueue_scripts'))
            add_action('admin_enqueue_scripts', array($obj, 'admin_enqueue_scripts'));
        //add_action('admin_enqueue_scripts', array(__CLASS__, '_admin_enqueue_scripts'));
        /**
         * @hook wp_head
         */
        if(method_exists($obj, 'print_head')) add_action('wp_head', array($obj, 'print_head'));
        /**
         * @hook wp_footer
         */
        if(method_exists($obj, 'print_footer')) add_action('wp_footer', array($obj, 'print_footer') );
    }
    /**
     * check widget feature avaiable for specific widget
     * @param $widget widget object
     * @param $feature name of feature widget
     */
    public static function check_widget_feature($widget,$feature){
        if(!isset(self::$widgets_fields[$feature])) return false;
        if(is_object($widget) && $widget instanceof WP_Widget) {
            $id = $widget->id_base.'-'. $widget->number;
        }
        elseif(is_string($widget)) $id = $widget;

        if(is_array(self::$widgets_fields[$feature]) && isset(self::$widgets_fields[$feature][$id])) {
            /*foreach (self::$widgets_fields[$feature] as $widg) {
                if($widget == $widg['widget']
                    || ($widg['widget']->id_base == $widget->id_base && $widg['widget']->number == $widget->number)
                ) {
                    return true;
                }
            }*/
            return true;
        }
        return false;
    }

    /**
     * get widget feature instance by widget identifier
     * @param $widget widget object
     * @param $feature name of feature widget
     */
    public static function get_widget_feature($widget, $feature) {
        if(isset(self::$widgets_fields[$feature])) {
            if(is_object($widget) && $widget instanceof WP_Widget) {
                $id = $widget->id_base.'-'. $widget->number;
            }
            elseif(is_string($widget)) $id = $widget;

            /*foreach(self::$widgets_fields[$feature] as $widg) {
                if($widget == $widg['widget']
                    || ($widg['widget']->id_base == $widget->id_base && $widg['widget']->number == $widget->number)
                ) {
                    return $widg['class'];
                }
            }*/
            if(isset(self::$widgets_fields[$feature][$id])) return self::$widgets_fields[$feature][$id]['class'];
        }
    }
    /**
     * update widget feature fields
     * function args inherit from callback '_hw_awc_in_widget_form_update'
     * @param $instance:
     * @param $new_instance
     * @param $old_instance
     */
    public  function valid_widget_feature($instance,$new_instance, $old_instance){
        foreach(/*array_keys*/(self::$widgets_fields) as /*$name*/$name => $widgets ) {
            foreach ($widgets as &$widget) {
                if( isset($widget['class']) ) {
                    //widget instance
                    if(is_object($widget['class']) && isset($new_instance[$widget['class']->get_widget_feature_name()]) ) {
                        $_new_instance = $new_instance[$widget['class']->get_widget_feature_name()];
                        //check remove feature settings
                        if($widget['class']->remove_settings == true && method_exists($widget['class'], 'remove_settings')) {
                            $_new_instance = (array) $widget['class']->remove_settings($_new_instance);
                        }
                        $instance[$widget['class']->get_widget_feature_name()] = $widget['class']->validation($instance,$_new_instance, $old_instance);
                        //$instance = array_merge($instance, $new_instance );
                    }

                }

            }
        }
        return $instance;
    }
    /**
     * do widget feature on frontend before load widget content
     * @return mixed
     */
    public function do_widget_feature_frontend($widget, $instance){

    }

    /**
     * check feature for widget is active
     */
    public function is_active(){}
}
#load all widget features class
if(class_exists('HW_HOANGWEB')){
    hwawc_register_widget_features();

}
