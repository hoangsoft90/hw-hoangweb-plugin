<?php
/**
 * Module Name: Customize Sidebar
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 12/10/2015
 * Time: 20:42
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

//main file
include_once ('hw-any-widget-classes.php');
/**
 * Class HW_Module_AWC
 */
class HW_Module_AWC extends HW_Module {
    /**
     * main class constructor
     */
    public function __construct() {
        $this->enable_tab_settings();
        $this->enable_submit_button();
        $this->support_fields('hw_html');
        //add_action('hw_modules_loaded', array($this, 'modules_loaded'));
    }

    /**
     * @return mixed|void
     */
    public function module_loaded() {
        //register content help for this module
        $this->register_help('awc_module' ,'awc.html', 'helps');
        $this->enable_config_page('cli/box');
    }

    /**
     * when all modules has loaded
     * @return mixed|void
     */
    public function modules_loaded() {
        $this->desploy_sidebars_position();
    }
    /**
     * desploy modules with positions
     */
    private function desploy_sidebars_position() {
        if(is_admin()) return;
        $sidebars_positions = $this->get_tab('sidebars_pos')->get_field_value('sidebars_position');
        if(is_array($sidebars_positions))
        foreach($sidebars_positions as $sidebar=> $item) {
            //if($item['condition'] == 0) continue;
            $match = 1;
            //check condition before module can display
            //$dynamic_settings = HW_Condition::get_active_conditions_settings();
            //$condition = $item['condition']; //condition id

            /*if($condition) {
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
            }*/

            if($match) {
                //add action for sidebar position
                HW_Module_Positions::add_position_hook($item['position'], function() use($sidebar, $item){
                    if ( is_active_sidebar( $sidebar ) ) :
                        if(function_exists('hw_dynamic_sidebar')) hw_dynamic_sidebar( $sidebar );
                        else dynamic_sidebar( $sidebar );
                    endif;
                });
            }
        }
    }
    /**
     * @param $aField
     * @return string
     */
    public function list_registers_sidebars($aField) {
        global $wp_registered_sidebars;
        $html = '<table border="1px" cellpadding="0px" cellspacing="0px">';
        $html .= '<tr>
            <th><strong>Tên</strong></th>
            <th><strong>Mô tả</strong></th>
            <th></th>
        </tr>';
        //HW_URL::curPageURL(['a'=>'A']);
        foreach ($wp_registered_sidebars as $sidebar) {
            if(!isset($sidebar['description'])) $sidebar['description'] = '';
            $edit_link = $this->get_setting_page_url('edit=' . $sidebar['id']); //edit sidebar
            $sidebar_customize_link = HW_AWC_Sidebar_Settings::get_edit_sidebar_setting_page_link($sidebar['id']);
            if(isset($sidebar['hw_can_delete'])) {
                $del_link = $this->get_setting_page_url('del_sidebar='. $sidebar['id']);//hw_current_url();
            }
            else $del_link = '';

            $html .= '<tr>';

            if(isset($sidebar['hw_can_delete'])) $html .='<td><span style="color:green">'.$sidebar['name'].'</span></td>';
            else $html .= '<td>'. $sidebar['name'] .'</td>';

            $html .='<td>'.$sidebar['description'].'</td>';
            $html .= '<td>';
            //edit sidebar link
            $html .= '(<a href="'. $edit_link .'">Sửa</a>)';
            //customize sidebar link
            $html .= '(<a href="'. $sidebar_customize_link .'" target="_blank">Cấu hình</a>)';
            //unregister sidebar link
            if($del_link) {
                $html .= '(<a href="javascript:void(0)" onclick="if(confirm(\'Bạn có chắc chắn ?\')) window.location.href=\''.$del_link.'\'" >Xóa</a>)';
            }
            $html.= '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
    /**
     * @param null $oAdminPage
     * @return mixed|void
     */
    public function replyToAddFormElements($oAdminPage) {
        //sidebars mananage tab
        $this->setting_form_sidebars_tab();
        /**
         * positions tab
         */
        $this->setting_form_positions_tab();
    }

    /**
     * fields setting for managing sidebars tab
     */
    private function setting_form_sidebars_tab() {
        global $wp_registered_sidebars;
        //if(count($wp_registered_sidebars)) $sidebar = reset($wp_registered_sidebars);
        #else
        $sidebar = array();
        //del sidebar
        if(hw__req('del_sidebar')) {
            hwawc_unregister_sidebar(hw__req('del_sidebar'));
        }
        if(hw__req('edit')) {
            $param = hwawc_get_registers_sidebars(hw__req('edit'));
            $sidebar = array_merge($sidebar, $param);
        }
        //add tab
        $setting_tab = $this->add_tab(array(
            'id'=>'sidebars',
            'title' => 'Quản lý Sidebars',
            'description' => 'Quản lý Sidebars'
        ));


        $setting_tab->addFieldLabel('Danh sách sidebars');
        $setting_tab->addFields(
            array(
                'field_id' => 'list-registered-sidebars',
                'type' => 'hw_html',
                'output_callback' => array($this, 'list_registers_sidebars'),
                'show_title_column' => false
            )
        );

        $setting_tab->addFieldLabel('Thêm sidebar');
        $setting_tab->addFieldLabel(array(
            'description' => '<a href="'. $this->get_setting_page_url() .'">Thêm mới sidebar</a>'
        ));
        $setting_tab->addFields(
            array(
                'field_id' => 'sidebar_name',
                'type' => 'text',
                'title' => 'Tên sidebar',
                'description' => 'Tên sidebar',
                'value' => isset($sidebar['name'])? $sidebar['name'] : ''
            ),
            array(
                'field_id' => 'sidebar_desc',
                'type' => 'text',
                'title' => 'Mô tả',
                'description' => 'Mô tả sidebar.',
                'value' => isset($sidebar['description'])? $sidebar['description'] : ''
            ),
            array(
                'field_id' => 'before_widget',
                'type' => 'text',
                'title' => 'Before Widget',
                'value' => isset($sidebar['before_widget'])? $sidebar['before_widget'] : '',
                'description' => htmlentities('Vd: <div id="%1$s" class="boxtourhome %2$s *1" >'),
            ),
            array(
                'field_id' => 'before_title',
                'type' => 'text',
                'title' => 'Before title',
                'value' => isset($sidebar['before_title'])? $sidebar['before_title'] :'',
                'description' => htmlentities('Vd: <h2 class="titteA" style="%1$s {css_title}">') ,
            ),
            array(
                'field_id' => 'after_title',
                'type' => 'text',
                'title' => 'After title',
                'value' => isset($sidebar['after_title'])? $sidebar['after_title']:'',
                'description' => htmlentities('vd: </h2>'),
            ),
            array(
                'field_id' => 'after_widget',
                'type' => 'text',
                'title' => 'After Widget',
                'value' => isset($sidebar['after_widget'])? $sidebar['after_widget']:'',
                'description' => htmlentities('</div>') ,
            )

        );
    }
    /**
     * fields setting for positions tab
     */
    private function setting_form_positions_tab() {
        global $wp_registered_sidebars;
        $position_tab = $this->add_tab(
            array(
                'id'=>'sidebars_pos',
                'title' => 'Vị trí hiển thị',
                'description' => 'Quản lý Vị trí hiển thị'
            )
        );
        //get all register positions
        $positions = HW_Module_Positions::get_positions();
        HW_UI_Component::empty_select_option($positions);

        $position_tab->addFieldLabel('Cấu hình vị trí của sidebars');
        //list all avaiable sidebars
        $sidebars_list = array();
        foreach($wp_registered_sidebars as $sidebar) {
            $sidebar = self::_valid_sidebar($sidebar); //valid
            if(!isset($sidebar['id'])) continue;
            $sidebars_list[$sidebar['id']] = !empty($sidebar['name'])? $sidebar['name'] : $sidebar['description'];

        }
        $position_tab->addFields(

            array(
                'field_id' => 'sidebars_position',
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
                        'name' => 'sidebar',
                        'type' => 'select',
                        'options' => $sidebars_list,
                        /*'event' => array(
                            'change'=>''
                        ),
                        'attributes' => array(
                            'style'=>'color:blue;'
                        ),*/
                        'description' => ''
                        //'static' => true
                    ),

                    'col2' => array(    //taxonomy template
                        'name' => 'position',
                        'type'=>'select',
                        'options' => $positions
                    )
                ),
                'table_header' => array(
                    'col1' => 'Sidebar',
                    'col2' => 'Vị trí'
                )
            )
        );
    }

    /**
     * valid sidebar data
     * @param $data sidebar param
     */
    public  static function _valid_sidebar($data) {
        if(!is_array($data)) return $data;

        if(!isset($data['id']) && isset($data['name'])) $data['id'] = strtolower(HW_Validation::valid_objname(HW_String::vn_str_filter($data['name'])) );
        return $data;
    }
    /**
     * validation form fields
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($_values) {
        HW_HOANGWEB::load_class('HW_String');
        /*foreach(array('xxx') as $option) {
            if(isset($values[$option])) $values[$option] = $values[$option]? true:false;
        }*/
        $values = $this->pure_fields_result($_values);
        $values = $values['sidebars'];

        if(!empty($values['sidebar_id'])) $id = $values['sidebar_id'];
        else $id = strtolower(HW_Validation::valid_objname(HW_String::vn_str_filter($values['sidebar_name']) ));

        $sidebar = array(
            'id' => ($id),
            'name' => $values['sidebar_name'],
            'description' => isset($values['sidebar_desc'])? $values['sidebar_desc'] :'',
            'before_widget' => $values['before_widget'],
            'before_title' => $values['before_title'],
            'after_title' => $values['after_title'],
            'after_widget' => $values['after_widget']
        );
        if(!empty($sidebar['id'])) hwawc_register_sidebar($sidebar);
        return $_values; //un-save
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {

    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

    }
    public function print_head(){}
    public function print_footer(){}
}
add_action('hw_modules_load', 'HW_Module_AWC::init');
/**
 * deactivation hook
 */
function hw_module_awc_register_deactivation_hook(){
    hwawc_unregister_all_sidebars();
}
hw_register_deactivation_hook(__FILE__, 'hw_module_awc_register_deactivation_hook');