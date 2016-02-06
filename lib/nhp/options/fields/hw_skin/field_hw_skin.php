<?php
/**
 * Class NHP_Options_hw_skin
 */
class NHP_Options_hw_skin extends NHP_Options{
    const DEFAULT_TEMPLATE = '-1';
    /**
     * default field args
     * @var array
     */
    private $aDefaultKeys = array();
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since NHP_Options 1.0
	*/
	function __construct($field = array(), $value ='', $parent){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;

        $this->aDefaultKeys = array(
            'enable_external_callback' => false,//enable external callback
            'set_dropdown_ddslick_setting' => array('width'=>'350'), //ddslick setting
            'DROPDOWN_DDSSLICK_THEME' => true,
            'apply_current_path' => plugin_dir_path(__FILE__),
            'plugin_url' => plugins_url('',__FILE__),
            'skins_folder' => '',
            'group' => '',
            'external_skins_folder' => '',
            'skin_filename' => '',
            'files_skin_folder' => "",
            'display' => 'select'
        );
        $aField = $this->field;    //$this->field;
        /**
         * parse hw_skin params
         */
        //group
        $group = $this->get_field_value('group');
        $skins_folder = $this->get_field_value('skins_folder');
        $external_skins_folder = $this->get_field_value('external_skins_folder');
        $skin_filename = $this->get_field_value('skin_filename');
        $apply_current_path = $this->get_field_value('apply_current_path'); //path or url
        $plugin_url = $this->get_field_value('plugin_url');
        $files_skin_folder = $this->get_field_value('files_skin_folder');
        //if(!$files_skin_folder) return; //required

        $enable_external_callback = $this->get_field_value('enable_external_callback');
        $set_dropdown_ddslick_setting = $this->get_field_value('set_dropdown_ddslick_setting');

		//$this->render();
        if(class_exists('HW_SKIN')) {
            $this->skin = new HW_SKIN($this,$apply_current_path,$external_skins_folder,$skin_filename,$skins_folder);
            $this->skin->plugin_url = $plugin_url;
            if($group) $this->skin->set_group($group);  //set skin group
            $this->skin->enable_external_callback = $enable_external_callback;
            $this->skin->set_dropdown_ddslick_setting($set_dropdown_ddslick_setting);

            //add_filter('hw_skin_data', array($this, '_hw_skin_data_filter'));
            $this->skin_config = $this->skin->get_config(true); //get skin config
        }
		
	}//function

    /**
     * get field value
     * @param $fname
     * @param $default: default value if not found
     */
    function get_field_value($fname, $default = ''){
        $aField = $this->field;    //$this->field;
        if(isset($aField[$fname]) ) {
            $value = apply_filters('nhp_hwskin_link',$aField[$fname],$fname);
        }
        elseif(isset($this->aDefaultKeys[$fname])) $value = $this->aDefaultKeys[$fname];
        return isset($value)? $value : $default;
    }

	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since NHP_Options 1.0
	*/
	function render(){
        //valid
        if(! isset($this->skin)) {
            echo 'Cần kích hoạt plugin HW SKIN.';
            return;
        }

        $display = $this->get_field_value('display','select');  //picker style
		$class = (isset($this->field['class']))? $this->field['class'] : '';

        $field_name = $this->args['opt_name'].'['.$this->field['id'].']';   //base field name
        $attributes = array(
            'id' => $this->field['id'],
            'name' => $field_name.'[hash_skin]',
            'class' => $class,

        );

        //get hash skin value
        $hash_skin_value = isset( $this->value['hash_skin'] ) ? $this->value['hash_skin'] : '';
        //get skin config
        $skin_config_value = isset( $this->value['hwskin_config'] ) ? esc_attr($this->value['hwskin_config']) : '';
        if(!$skin_config_value) $skin_config_value = esc_attr($this->skin_config);

        $notice_skin_option = 'Vui lòng nhấn save để tùy chỉnh options của theme này nếu có.';  //skin options notice
        //valid skin options file for this skin
        if($hash_skin_value) {
            $option_file = $this->skin->get_file_skin_options($hash_skin_value);

            //get document
            $readme = $this->skin->get_file_skin_resource('readme.html');//_print($readme);
            if(file_exists($readme)) $readme_link = $this->skin->get_skin_url('readme.html');

            if(file_exists($option_file)) {
                include($option_file);
                if(isset($theme_options) && is_array($theme_options)){
                    $skin_data = $this->skin->get_skin_data($hash_skin_value);  //get active skin data
                    $parse_id = md5($skin_data['screenshot']);
                    $theme_options_output = array();
                    $theme_options_output[] = '<hr/><div class="hw-skin-options-holder" data-collapse="accordion persist" id="'.$parse_id.'">';
                    $theme_options_output[] = '<h2>Tùy biến skin</h2><div class="skin-options">'; //toggle title
                    //create fields
                    foreach($theme_options as $_field){
                        //note that, i named form field with multi array accession so, you should turn of xdebug to prevent error report or set WP_DEBUG=0
                        $theme_options_output[] = $this->renderOptionField($_field, $field_name);

                    }
                    $theme_options_output[] = '</div></div>';     //close parse_id div tag
                    $theme_options_output[] = '<div class="hw-skin-options-notice" style="display: none;color:red !important;">'.$notice_skin_option.'</div>';
                }

            }

        }
        $msg_id = 'hwskin_msg_'.$attributes['id'];    //div message id

        $theme_options_output[] = '<div class="message hw-skin-options-notice" id="'.$msg_id.'"></div>';
        if(isset($readme_link)) $theme_options_output[] = '<a href="'.$readme_link.'" target="_blank">Xem tài liệu</a>';

        if(isset($parse_id)){
            $this->skin->saveCallbackJs4SkinChangeEvent('hwskin_nhp_field_'.$attributes['id'].'_'.$parse_id.'_change_event(skin);');
            //$this->skin->saveCallbackJs4SkinChangeEvent('$("#'.$skin_config_field_id.'").val(skin.screenshot);');  //call this before render skins selector

            $aOutput[] = '<script>
                        function hwskin_nhp_field_'.$attributes['id'].'_'.$parse_id.'_change_event(skin){
                            //console.log(skin.md5,"'.$parse_id.'");
                            if(skin.md5 === "'.$parse_id.'"){
                                jQuery("#'.$parse_id.'").removeClass("hw-skin-options-none").show();jQuery("#'.$parse_id.'").next().hide();
                                jQuery("#'.$parse_id.'").find("input,select").removeAttr("disabled").css({"display":""});
                            }
                            else{
                                jQuery("#'.$parse_id.'").addClass("hw-skin-options-none").hide();jQuery("#'.$parse_id.'").next().show();
                                jQuery("#'.$parse_id.'").find("input,select").attr({"disabled":"disabled"}).css("display","none");
                            }

                        }
                        </script>
                    ';
        }
        else $this->skin->saveCallbackJs4SkinChangeEvent('jQuery("#'.$msg_id.'").html("'.$notice_skin_option.'")');

        //render skins selector
        switch($display){
            case 'ddslick':
                $aOutput[] = $this->skin->get_skins_select_tag(null,$hash_skin_value,$attributes,HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_FILES);
                break;
            case 'list':
                $aOutput[] = $this->skin->get_skins_listview(null,$hash_skin_value,HW_SKIN::SKIN_FILES, $attributes);
                break;
            default:
                $aOutput[] = $this->skin->get_skins_select_tag(null,$hash_skin_value,$attributes,false);
        }
        //$aOutput[] = $this->skin->get_skins_select_tag(null,$hash_skin_value,$attributes,false);

        $aOutput[] = '<input type="hidden" name="'.$field_name.'[hwskin_config]" id="'.$this->field['id'].'_hwskin_config" value="'.($skin_config_value).'"/>'; //hold skin config
        //field condition
        if($this->get_field_value('enable_skin_condition')) {
            $aOutput[] = $this->skin->create_skin_template_condition_field($field_name, $this->value, array('hw-nhp-skin-condition'));
        }

		$aOutput[] = (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';

        //callback params
        $args = array('field_name' => $field_name);
        // callback addition field form
        if(isset($this->field['hwskin_nhp_field_output_callback']) && is_callable($this->field['hwskin_nhp_field_output_callback'])){    //filter field output
            $aOutput = call_user_func($this->field['hwskin_nhp_field_output_callback'],$aOutput,$this->skin, $this->field,$this->value, $args);
        }

        else $aOutput = apply_filters('hwskin_nhp_field_output',$aOutput, $this->skin, $this->field,$this->value,  $args);    //filter with hook of version

        $aOutput = array_merge($aOutput, $theme_options_output);
        echo implode("\n",$aOutput);
		
	}//function
    /**
     * render theme option field
     * @param $aField
     */
    private function renderOptionField($field, $field_name, $wrapper = true,$repeat = true){
        if(!isset($field['type'])) $field['type'] = 'checkbox';   //default set checkbox option
        //valid field type
        if(!in_array($field['type'],HW_SKIN_Option::allow_field_types() )) return ;
        if(!isset($field['id'])) $field['id'] = mt_rand(5, 15); //random id if not found

        $tag = '';
        $name = $field_name.'[skin_options]['.$field['id'].']'; //field name
        $id = $this->field['id'].'_nhp_theme_option_'.$field['id'];   //field id
        $title = isset($field['title']) && $field['title']? $field['title'] : $field['id'];
        $desc = isset($field['desc'])? $field['desc'] : '';

        //field value
        if(isset($this->value['skin_options']) && isset($this->value['skin_options'][$field['id']])) {
            $value = $this->value['skin_options'][$field['id']];
        }
        else $value = '';

        if($wrapper) $tag .= '<p style="margin-bottom:14px;">';
        $field = HW_SKIN_Option::build_option_field($field, array(
            'id' => $id, 'name' => $name, 'title' => $title,
            'desc' => $desc, 'value' => $value), $tag
        ,   array(&$this, '_renderOptionField_callback'),
            array('field_name' => $field_name)
        );

        /*if($field['type'] == 'checkbox') {  //checkbox
            $tag .= '<label for="'.$id.'"><strong>'.$title.'</strong><br/>
                <input type="'.$field['type'].'" name="'.$name.'" id="'.$id.'" '.($value? 'checked="checked"':'').'/>
                </label><br/><em>'.$desc.'</em>';
        }
        elseif($field['type'] == 'text'){   //text
            $tag .= '<label for="'.$id.'"><strong>'.$title.'</strong><br/>
                <input type="'.$field['type'].'" name="'.$name.'" id="'.$id.'" value="'.($value).'"/>
                </label><br/><em>'.$desc.'</em>';
        }

        elseif($field['type'] == 'select' && isset($field['options'])) {    //select
            if(is_string($field['options'])) $field['options'] = explode(',',$field['options']);

            $tag .= '<label for="'.$id.'"><strong>'.$title.'</strong><br/><select name="'.$name.'" id="'.$id.'">';
            if(is_array($field['options']))
                foreach($field['options'] as $option){
                    $tag .= '<option value="'.esc_attr($option).'" '.($option == $value? 'selected':'').'>'.$option.'</option>';
                }
            $tag .= '</select></label>';
            $tag .= '<br/><em>'.$desc.'</em>';

        }
        elseif($field['type'] == 'string'){ //string field
            if(isset($field['title'])) $tag .= "<h3>{$field['title']}</h3>";
            if(isset($field['desc'])) $tag .= "<div>{$field['desc']}</div>";
        }
        */

        if($wrapper) $tag .= '</p>';
        if($repeat == true && isset($field['repeat'])):
        //repeat current field
        if(isset($field['repeat']) && is_numeric($field['repeat'])) {

            for($i=1;$i < $field['repeat'] ;$i++){  //- first repeat field
                $save_id = $field['id'];
                $field['id'] .= '_'.$i;
                $field['title'] = $field['id']; //sync for title

                $tag .= $this->renderOptionField($field,$field_name,false,false);
                $field['id'] = $save_id;

            }
        }
            endif;
        return $tag;
    }

    /**
     * HW_SKIN_Option::build_option_field callback
     * @param $field
     * @param $args
     */
    public function _renderOptionField_callback($field, $args) {
        $tag = &$args['tag'];
        $data = $args['data'];
        $field_name = $data['field_name'];

        if($field['type'] == 'sidebar'){    //sidebar field
            if(method_exists(__CLASS__ ,'FieldType_data_'.$field['type'])) {
                $field = call_user_func(array(__CLASS__, 'FieldType_data_'.$field['type']),$field);
                //$save_field = $field;   //save field setting
                //if(isset($field['repeat'])) unset($field['repeat']);    //exclude repeat field this will cause duplicate repeating
                $tag .= $this->renderOptionField($field,$field_name,true,false);   //this don;t need to repeat because first build field tag
                //$field = $save_field;   //resume field setting
            }

        }
        elseif($field['type'] == 'template'){   //template
            $data = $this->get_list_templates();
            $field['options'] = array(
                self::DEFAULT_TEMPLATE => __('Mặc định')
            );    //override args
            foreach($data as $file){
                $field['options'][base64_encode($file['file_path'])] = $file['name'];
            }
            $field['type'] = 'select';

            $tag .= $this->renderOptionField($field, $field_name,false,false);   //render select tag
        }
    }
    /**
     * get avaiable templates in mlslider skin, note: this call after user choose a skin
     * call in the method 'renderOptionField'
     */
    private function get_list_templates(){
        $config = (object)$this->skin->get_config(false);
        $skin_info = $this->skin->get_active_skin_info();
        $skin_dir = $skin_info[0].'/'.$skin_info[1];

        $filename = trim(trim($config->skin_name),'.php');  //skin file name without extension
        $template  = $filename.'_template';     //detect template file
        $templates = array(
            //default skin
            array(
                'name' => $config->skin_name,
                'file_path' => base64_encode($skin_dir.$config->skin_name)
            )
        );

        $skins_iterator = new RecursiveDirectoryIterator( $skin_dir );
        foreach ( new RecursiveIteratorIterator( $skins_iterator ) as $skin ) {
            if ( $skin->getExtension() == 'php' && basename( $skin ) != '.' && basename( $skin ) != '..'
                && strpos(basename( $skin ) ,$template) === 0) {
                $temp = array();
                $temp['name'] =  basename( ( $skin ) );
                $temp['file_path'] = $skin_dir.$temp['name'];
                $templates[] = $temp;
            }
        }
        return $templates;
    }
    /**
     *
     * @return array
     */
    static function FieldType_data_sidebar($field= array()){
        static $field_setting;
        if(!$field_setting){
            global $wp_registered_sidebars;
            #if(0&&class_exists('HW_Sidebar_Settings')) $sidebars = HW_Sidebar_Settings::get_all_sidebars();
            #else
            {//get all registered sidebars
                $sidebars = array();
                foreach($wp_registered_sidebars as $sidebar => $param){
                    //$sidebars[$sidebar] = $param['name'];
                    $sidebars[] = $sidebar;
                }
            }
            $_field = array(
                'type' => 'select',
                'options' => $sidebars
            );
                $field_setting = array_merge($field,$_field);
        }
        return $field_setting;
    }
	
}//class
?>