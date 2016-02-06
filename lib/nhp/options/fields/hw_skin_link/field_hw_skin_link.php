<?php
/**
 * Class NHP_Options_hw_skin_link
 */
class NHP_Options_hw_skin_link extends NHP_Options{
	
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
            'enable_external_callback' => false,
            'set_dropdown_ddslick_setting' => array('width'=>'350'),
            'DROPDOWN_DDSSLICK_THEME' => true,
            'apply_current_path' => plugin_dir_path(__FILE__),
            'plugin_url' => plugins_url('',__FILE__)
        );

		//$this->render();
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
        if(!$files_skin_folder) return; //required

        $enable_external_callback = $this->get_field_value('enable_external_callback');
        $set_dropdown_ddslick_setting = $this->get_field_value('set_dropdown_ddslick_setting');

		//setup metaslider themes
		if(class_exists('HW_SKIN') /*&& !isset($this->skin)*/)
        {
            //_print($apply_current_path.','.$external_skins_folder.','.$skin_filename);
            $this->skin = new HW_SKIN($this,$apply_current_path,$external_skins_folder,$skin_filename,$skins_folder);
		    $this->skin->plugin_url = $plugin_url;
		      $this->skin->files_skin_folder = $files_skin_folder;    //set skin folder files
            $this->skin->enable_external_callback = $enable_external_callback;
            if($group) $this->skin->set_group($group);
		      //$this->skin->getSavedCallbacksJs_data(array($this,'get_callbacks_data'));
		      //$this->skin->registerExternalStorage_JSCallback(array($this, 'save_callback_event'));
		      $this->skin->set_dropdown_ddslick_setting($set_dropdown_ddslick_setting);
            //add_filter('hw_skins_data', array($this, '_hw_skins_data'),10,2);
		}
	}//function

	//unused
    function get_callbacks_data(){        
        return  hw_option('scroll2top_image_file');
    }
    //unused
    function save_callback_event(){

    }

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
        if(! class_exists('HW_SKIN')) {
            echo 'Cần kích hoạt plugin HW SKIN.';
            return;
        }

        $style = $this->get_field_value('display'); //display mode
		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" ':'';
        $field_name = $this->args['opt_name'].'['.$this->field['id'].']';   //base field name
        $value = isset($this->value['hash_skin'])? $this->value['hash_skin'] : '';  //get hash skin value
        $obj_name = HW_SKIN::valid_objname($this->field['id']);
        $default_skin_file = base64_encode(serialize($this->skin->get_default_skin_file(true)));

        $active_skin_file = $this->skin->get_skin_link( $value, true);
        //$skin_info = $this->skin->set_active_skin($value);
        //$skin_info = $this->skin->get_skin_info($value);

        if(isset($this->skin)){
            $this->skin->saveCallbackJs4SkinChangeEvent($obj_name.'_choose_skin_callback(skin);');
            switch($style){
                case 'ddslick':
                    echo $this->skin->get_skins_select_tag($field_name.'[hash_skin]',$value,array(),HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_LINKS);
                break;
                case 'list':
                    echo $this->skin->get_skins_listview($field_name.'[hash_skin]',$value,HW_SKIN::SKIN_LINKS);
                    break;
                default:
                    echo $this->skin->get_skins_select_tag($field_name.'[hash_skin]',$value,array(),false,HW_SKIN::SKIN_LINKS);
            }

            #_print($this->skin->get_skin_link( $this->value));
            echo '<input type="hidden" name="'.$field_name.'[hwskin_link_default_skin_file]" value="'.$default_skin_file.'"/>';
            echo '<input type="hidden" id="'.$this->field['id'].'_hwskin_save_link" name="'.$field_name.'[hwskin_link_file_url]" value="'.base64_encode(serialize($active_skin_file[0])).'"/>';
            echo '<input type="hidden" name="'.$field_name.'[hwskin_link_source]" value="'.$active_skin_file[1].'"/>';

            echo '<script>
		        //save skin file into new nhp field
		      function '.$obj_name.'_choose_skin_callback(skin){
		        jQuery("#'.$this->field['id'].'_hwskin_save_link").val(skin.screenshot);
		      }

		    </script>';
        }
        else echo '<p>Không thể tạo skin, vì thiếu tham số của trường này.</p>';
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function


}//class
?>