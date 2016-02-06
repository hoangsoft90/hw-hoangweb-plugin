<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 26/10/2015
 * Time: 10:27
 */
/**
 * Interface HW_APF_FormField_Interface
 */
interface HW_APF_FormField_Interface {

    /**
     * @hook admin_enqueue_scripts
     * @return mixed
     */
    public function admin_enqueue_scripts();
}
/**
 * Class HW_APF_FormField
 */
if(class_exists('AdminPageFramework_FieldType')):
abstract class HW_APF_FormField extends AdminPageFramework_FieldType implements HW_APF_FormField_Interface{
    /**
     * save field params
     * @var null
     */
    protected $aField = null;
    /**
     * module reference linked to field
     * @var
     */
    public $module;
    /**
     * field type location
     * @var null
     */
    private $field_type_location = null;
    /**
     * default keys value
     * @var array
     */
    public $aDefaultKeys = array();

    /**
     * main class constructor
     * @param string $class
     * @param string $location child extend class location
     */
    public function __construct($class, $location=''){
        parent::__construct($class);
        $this->field_type_location = $location;
    }

    /**
     * enqueue script file
     * @param $file
     * @param array $dependencies
     * @param string $handle
     */
    public function enqueue_script($file, $dependencies = array(), $handle='') {
        global $wp_scripts;
        //valid
        if(!is_array($dependencies)) $dependencies = array();
        //handle script name
        if(!$handle) $handle = $this->get_field_id($file);
        $handle = md5('hw-apf-field-'.strtolower(__CLASS__) .'-'. ($handle));

        if(!wp_script_is($handle, 'queue')) {
            wp_enqueue_script(($handle), $this->get_file_url($file), $dependencies);
            return $handle;
        }
    }

    /**
     * @param $handle
     * @param $object_name
     * @param $key
     * @param $value
     */
    public function add_data($handle, $object_name, $key, $value) {
        global $wp_scripts;
        if(!wp_script_is($handle, 'queue')) return;
        //valid
        if(is_array($value)) $value = json_encode($value);

        $localize_data = $wp_scripts->get_data($handle, 'data');
        $wp_scripts->add_data($handle, 'data', $localize_data. "{$object_name}['{$key}'] = ". $value. ';' );
    }
    /**
     * localize script
     * @param $handle
     * @param $object_name
     * @param array $data
     */
    public function localize_script($handle, $object_name, $data = array()) {
        //validation
        if(!is_array($data)) $data = array();
        wp_localize_script($handle, HW_Validation::valid_objname($object_name) , $data);
    }
    /**
     * get file url in field type directory
     * @param $file
     * @return string
     */
    public function get_file_url ($file) {
        if(filter_var($file, FILTER_VALIDATE_URL)) return $file;
        else
            return plugins_url($file, $this->field_type_location) ;
    }
    /**
     * enqueue stylesheet
     * @param $file
     * @param array $dependencies
     * @param string $handle
     */
    public function enqueue_style($file, $dependencies = array(), $handle='') {
        global $wp_styles;
        //valid
        if(!is_array($dependencies)) $dependencies = array();
        //handle script name
        if(!$handle) $handle = $this->get_field_id($file);
        $handle = md5('hw-apf-field-'.strtolower(__CLASS__) .'-'. ($handle));

        if(!wp_style_is($handle, 'queue')) {
            wp_enqueue_style(($handle), $this->get_file_url($file), $dependencies);
            return $handle;
        }
    }
    /**
     * @hook admin_enqueue_scripts holder
     * @return mixed|void
     */
    public function admin_enqueue_scripts() {}
    /**
     * generate field name
     * @param $field
     */
    public function get_field_name($field='') {
        //validation
        if(empty($this->aField)) return;

        $_aAttributes = $this->aField['attributes'];
        $name = $_aAttributes['name'];
        return $name.'['.$field.']';
    }

    /**
     * generate field id
     * @param string $field
     * @return mixed
     */
    public function get_field_id($field = '') {
        return HW_Validation::valid_apf_slug($this->get_field_name($field));
    }

    /**
     * get field value
     * @param $item
     */
    public function get_field_value( $item = '') {
        $value = isset($aField['attributes']['value'])? $aField['attributes']['value'] : '';
        if($item) return isset($value[$item])? $value[$item] : '';
        return $value;
    }

    /**
     * @return null
     */
    public function getFieldData() {
        return $this->aField;
    }

    /**
     * return property value
     * @param $key
     */
    public function get_property($key) {
        if(isset($this->aField[$key] )) return $this->aField[$key];
        elseif(isset($this->aDefaultKeys[$key])) return $this->aDefaultKeys[$key];
        else
            $this->get_field_value($key);
    }
    /**
     * @param AdminPageFramework $aField
     */
    public function init($aField) {
        $this->aField = $aField;
        if(isset($aField['module_ref'])) $this->module = $aField['module_ref'];
        //enqueue files hook
        #add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'), 10);
        $this->admin_enqueue_scripts();
    }
}
endif;