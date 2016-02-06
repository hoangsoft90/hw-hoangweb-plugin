<?php
/**
 * Class NHP_Options_hw_ckeditor
 */
class NHP_Options_hw_ckeditor extends NHP_Options{
	
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

        #add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));   //note: this not work.
        $this->_admin_enqueue_scripts();        //directly call while render field
		//$this->render();
		
	}//function
	private function setup_actions() {

    }

    /**
     * enqueue scripts/css
     */
    public function _admin_enqueue_scripts() {
        $serialize_obj = array(
            'field_url' => plugins_url('',__FILE__)
        );

        wp_enqueue_script('ckeditor.js', plugins_url('ckeditor/ckeditor.js', __FILE__));  //insert ckeditor lib
        wp_enqueue_script('hw-ckeditor-config', plugins_url('config.js', __FILE__), array('ckeditor.js'));    //field js
        wp_localize_script('hw-ckeditor-config', 'nhp_hw_ckeditor_field', $serialize_obj);
    }
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since NHP_Options 1.0
	*/
	function render(){
		
		$class = (isset($this->field['class']))?$this->field['class']:'';

        $placeholder = (isset($this->field['placeholder']))?' placeholder="'.esc_attr($this->field['placeholder']).'" ':'';

        echo '<textarea id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$placeholder.'class="'.$class.'" rows="6" >'.esc_attr($this->value).'</textarea>';

		//echo '<textarea id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" class="'.$class.'" rows="6" >'.$this->value.'</textarea>';
		echo '<script>
            jQuery(function(){
                CKEDITOR.config.extraPlugins = "hw_wp_media_button";
                // Replace the <textarea id="editor1"> with a CKEditor
                // instance, using default configuration.
                CKEDITOR.replace( "'.$this->field['id'].'" ,{
                    language: "vi",
                    uiColor: "#9AB8F3"
                });
            });

		</script>';
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function

	
}//class
?>