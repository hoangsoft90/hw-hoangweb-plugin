<?php
class NHP_Validation_editor extends NHP_Options{
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since NHP_Options 1.0
	*/
	function __construct($field = array(), $value ='', $parent){
		
		parent::__construct(/*$parent->sections, $parent->args, $parent->extra_tabs*/);
		$this->field = $field;
        $this->field['msg'] = __('You must provide a valid email for this option.', 'nhp-opts');
		$this->value = $value;
        $this->current = $parent;
        $this->validate();

	}//function

    /**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for the field in the settings
     * https://github.com/leemason/NHP-Theme-Options-Framework/wiki/Creating-a-Validation-Class
     * @since NHP_Options 1.0
     */
    function validate(){
        $this->value = 'XXX';//($this->value);
    }//function
	
}//class
?>