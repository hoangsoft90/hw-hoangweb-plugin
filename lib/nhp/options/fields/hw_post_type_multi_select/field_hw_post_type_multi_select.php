<?php
class NHP_Options_hw_post_type_multi_select extends NHP_Options{
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since NHP_Options 1.0.1
	*/
	function __construct($field = array(), $value ='', $parent){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		//$this->render();
		
	}//function
	
	
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since NHP_Options 1.0.1
	*/
	function render(){
		//validation
        if(!isset($this->value) || !is_array($this->value)) $this->value = array();

		$class = (isset($this->field['class']))?'class="'.$this->field['class'].'" ':'';
		$size = isset($this->field['size'])? $this->field['size'] : 10;
		echo '<select id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][]" '.$class.' multiple size="'.$size.'">';
		
		if(!isset($this->field['args'])){$this->field['args'] = array();}
		$args = wp_parse_args($this->field['args'], array('public' => true));
			
		$post_types = get_post_types($args, 'object'); 
		foreach ( $post_types as $k => $post_type ) {
			echo '<option value="'.$k.'"'.selected(in_array( $k,$this->value)?true: false).'>'.$post_type->labels->name.'</option>';
		}
		echo '</select>';

		echo (isset($this->field['desc']) && !empty($this->field['desc']))?' <span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
	
}//class
?>