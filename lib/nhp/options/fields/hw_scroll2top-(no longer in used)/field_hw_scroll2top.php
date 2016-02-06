<?php
//no longer in use, moved to hw_skin_link field type
class NHP_Options_hw_scroll2top extends NHP_Options{	
	
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
		//$this->render();
		//setup metaslider themes
		if(class_exists('HW_SKIN') /*&& !isset($this->skin)*/){
		    $this->skin = new HW_SKIN($this,plugin_dir_path(__FILE__),'hw_scroll2top_skins','hw-scroll2top.php','skins');
		    $this->skin->plugin_url = plugins_url('',__FILE__);
		      $this->skin->files_skin_folder = 'images';    //set skin folder files
		      $this->skin->enable_external_callback = false;
		      //$this->skin->getSavedCallbacksJs_data(array($this,'get_callbacks_data'));
		      //$this->skin->registerExternalStorage_JSCallback(array($this, 'save_callback_event'));
		      $this->skin->set_dropdown_ddslick_setting(array(
		              'width'=>'350'
		      ));
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
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since NHP_Options 1.0
	*/
	function render(){
	    
		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" ':'';
		$this->skin->saveCallbackJs4SkinChangeEvent('scrool2top_choose_skin_callback(skin);');
		echo $this->skin->get_skins_select_tag($this->args['opt_name'].'['.$this->field['id'].']',$this->value,0,0,HW_SKIN::SKIN_LINKS);
		#_print($this->skin->get_skin_link( $this->value));
		echo '<input type="hidden" name="'.$this->args['opt_name'].'[scroll2top_skin_file]" value="'.$this->skin->get_default_skin_file().'"/>';
		echo '<input type="hidden" id="'.$this->field['id'].'_save" name="'.$this->args['opt_name'].'[scroll2top_image_file]" value="'.$this->skin->get_skin_link( $this->value).'"/>';
		echo '<script>
		        //save skin file into new nhp field
		      function scrool2top_choose_skin_callback(skin){		        
		        jQuery("#'.$this->field['id'].'_save").val(skin.screenshot);
		        }  
		        
		    </script>';
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
	
}//class
?>