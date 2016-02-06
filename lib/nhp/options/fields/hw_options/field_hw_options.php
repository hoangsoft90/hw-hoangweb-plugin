<?php
class NHP_Options_hw_options extends NHP_Options{
	
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
		
	}//function
	private function setup_actions() {

    }
	
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since NHP_Options 1.0
	*/
	function render(){
		$title = $this->field['title'];     //field title
		$class = (isset($this->field['class']))?$this->field['class']:'';
        $values = $this->value;
        $placeholder = (isset($this->field['placeholder']))?' placeholder="'.esc_attr($this->field['placeholder']).'" ':'';

        $myfield_id = $this->field['id'];
        $myfield_name = $this->args['opt_name'].'['.$this->field['id'] . ']';

        //get options
        $options = ($this->field['settings']);
        $theme_options_output = array();
        if( is_array($options)) {
            $parse_id = md5(rand());

            $theme_options_output[] = $title. '<hr/><div id="'.$parse_id.'">';
            //create fields
            foreach($options as $_field){
                $theme_options_output[] = $this->renderOptionField(
                    $_field,
                    $myfield_name,
                    $myfield_id,
                    $values
                );

            }
            $theme_options_output[] = '</div>';     //close parse_id div tag
        }
        echo implode("\n", $theme_options_output);
        #echo '<textarea id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" '.$placeholder.'class="'.$class.'" rows="6" >'.esc_attr($this->value).'</textarea>';

		//echo '<textarea id="'.$this->field['id'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].']" class="'.$class.'" rows="6" >'.$this->value.'</textarea>';

		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><span class="description">'.$this->field['desc'].'</span>':'';
		
	}//function
    /**
     * render theme option field
     * @param $aField
     */
    public function renderOptionField($field, $_name, $_id, $values = null, $wrapper = true,$repeat = true) {
        if(!isset($field['type'])) $field['type'] = 'text';   //default set checkbox option
        //valid field type
        if(!in_array($field['type'], array('select','checkbox','text','string','hidden'))) return ;
        if(!isset($field['name']) ) {
            $field['name'] = '';     //valid field name
            //return ;  //allow type='string'
        }
        //filter field setting before render field form
        $field = apply_filters('hw_nhp_renderOptionField', $field);

        $tag = '';
        $name = $_name.'['.$field['name'].']' ; //field name
        $id = $_id.'_'.$field['name'];   //field id
        $title = isset($field['title'])? $field['title'] : $field['name'];
        $desc = isset($field['description'])? $field['description'] : '';

        //field value
        if(!empty($values) && isset($values[$field['name']])) {
            $value = esc_attr($values[$field['name']]);
        }
        else {
            $value = isset($field['value'])? esc_attr($field['value']) : '';  //default field value
        }

        $field_atts = array(
            'id' => $id,
            'name' => $name,
            'value' => $value,
            'title' => $title,
            'desc' => $desc
        );
        if($wrapper) $tag .= '<p style="margin-bottom:14px;">';
        HW_SKIN_Option::build_option_field($field, $field_atts, $tag  );
        if($wrapper) $tag .= '</p>';

        //repeat the field
        if($repeat == true && isset($field['repeat'])):
            //repeat current field
            if(isset($field['repeat']) && is_numeric($field['repeat'])) {

                for($i=1;$i < $field['repeat'] ;$i++){  //- first repeat field
                    $save_id = $field['id'];
                    $field['id'] .= '_'.$i;
                    $field['title'] = $field['id']; //sync for title

                    $tag .= $this->renderOptionField($field, $_name, $_id,$values, false,false);
                    $field['id'] = $save_id;

                }
            }
        endif;
        return $tag;
    }
	
}//class
?>