<?php
/**
 * Module Name: Positions
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 10/11/2015
 * Time: 22:09
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

include_once('includes/class.positions.php');

/**
 * Class HW_Module_Position
 */
class HW_Module_Position extends HW_Module {
    public function __construct() {
        //enable tab settings
        $this->enable_tab_settings();
        $this->enable_submit_button();
        $this->support_fields('hw_table_fields');

        //add_action('hw_modules_loaded', array($this, 'modules_loaded'));
    }

    /**
     * after module loaded
     * @return mixed|void
     */
    public function module_loaded() {
        $this->enable_config_page('cli/admin');
    }

    /**
     * after all modules loaded
     * @hook hw_modules_loaded
     */
    public function modules_loaded() {
        $this->desploy_modules_position();
    }

    /**
     * desploy modules with positions
     */
    private function desploy_modules_position() {
        if(is_admin()) return;
        $modules_positions = $this->get_field_value('modules_position');
        if(is_array($modules_positions))
        foreach($modules_positions as $module=> $item) {
            //if($item['condition'] == 0) continue;
            $match = 1;
            //check condition before module can display
            $dynamic_settings = HW_Condition::get_active_conditions_settings();
            $condition = $item['condition']; //condition id

            if($condition) {
                if(!isset($dynamic_settings[$condition])) continue;

                $setting = array($condition => $dynamic_settings[$condition]);
                $setting_conditions = HW__Template_Condition::parse_template_conditions($setting);#_print($setting_conditions);

                foreach($setting_conditions as $pages_condition) {   //and, or condition
                    if(isset($pages_condition) && is_array($pages_condition)) {     //get template alternate with AND relation

                        foreach ($pages_condition as $temp => $meet_condition) {
                            if($meet_condition['result']) {
                                $match = 1;
                                //break;  //first occurence
                            }
                            else {
                                $match = 0;
                            }
                        }
                    }
                }
            }

            if($match) {
                //add action for module position
                $module_inst = HW_Module_Settings_page::get_modules($module);
                if(!empty($module_inst)) {
                    HW_Module_Positions::add_position_hook($item['position'], $module_inst->get_module_content_cb());
                }
            }
        }
    }

    /**
     * Triggered when the tab is loaded.
     * @param $oAdminPage
     */
    public function replyToAddFormElements($oAdminPage) {
        $modules_pos = HW_Modules_Manager::get_modules_displayable();   //list displayable modules
        $avaiable_pages = HW__Template::get_pages_select(); //pages

        //get all register positions
        $positions = HW_Module_Positions::get_positions();
        HW_UI_Component::empty_select_option($positions);

        //condition
        $conditions = array();
        $dynamic_settings = HW_Condition::get_active_conditions_settings();
        foreach ($dynamic_settings as $id=>$item){
            $conditions[$id] = $item['title'];
        }
        HW_UI_Component::empty_select_option($conditions) ;

        $this->addFieldLabel('Cấu hình vị trí của các modules');
        $this->addFields(

            array(
                'field_id' => 'modules_position',
                'type' => 'hw_table_fields',
                'title' => '',
                'show_title_column' => false,
                'repeatable' => false,
                'show_root_field' => true,
                'data_base_field' => 'col1',
                'attributes'=>array(
                    'hw_table_fields' => array()
                ),
                'fields' => array(
                    //root field
                    'col1' => array(    //select taxonomy
                        'name' => 'module',
                        'type' => 'select',
                        'options' => $modules_pos,
                        /*'event' => array(
                            'change'=>''
                        ),
                        'attributes' => array(
                            'style'=>'color:blue;'
                        ),*/
                        'description' => ''
                        //'static' => true
                    ),
                    'col2' => array(
                        'name' => 'condition',   //
                        'type' => 'select',
                        'options' => $conditions,
                        'description' => ''
                    ),

                    'col3' => array(    //term
                        'name' => 'page',   //'Cấu hình hiển thị',
                        'type' => 'select',
                        'options' => $avaiable_pages,
                        'description' => ''
                    ),

                    'col4' => array(    //taxonomy template
                        'name' => 'position',
                        'type'=>'select',
                        'options' => $positions
                    )
                ),
                'table_header' => array(
                    'col1' => 'Module',
                    'col2' => 'Điều kiện',
                    'col3' => 'Trang hiển thị',
                    'col4' => 'Vị trí'
                )
            )
        );
    }
    /**
     * validation form fields
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {
        /*foreach(array('xxx') as $option) {
            if(isset($values[$option])) $values[$option] = $values[$option]? true:false;
        }
*/
        return $values;
    }
}
add_action('hw_modules_load', 'HW_Module_Position::init');