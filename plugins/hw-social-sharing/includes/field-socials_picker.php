<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 08/07/2015
 * Time: 14:36
 */
/*custom field type*/
/**
 * Class APF_HW_FieldType_socials_button_picker
 */
class APF_HW_FieldType_socials_button_picker extends AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array('socials_button_picker',);
    protected $aDefaultKeys = array(
        'label' => array(),
        'is_multiple' => false,
        'attributes' => array(
            array('select' =>array('size' =>  10, 'autofocusNew' => null, 'multiple' => null, 'required' => null)),
            'optgroup' => array(),
            'option' => array(),
        ),
    );


    protected function getField($aField) {
        $_oSelectInput = new AdminPageFramework_Input_select($aField);
        $out = $aField['before_label'] . "<div class='admin-page-framework-input-label-container admin-page-framework-select-label' style='min-width: " . $this->sanitizeLength($aField['label_min_width']) . ";'>" . "<label for='{$aField['input_id']}'>" . $aField['before_input'] . $_oSelectInput->get() . $aField['after_input'] . "<div class='repeatable-field-buttons'></div>" . "</label>" . "</div>";

        $out .= '<br/><a href="JavaScript:void(0);" id="hwss_apf_btn_up">Up</a> |
				<a href="JavaScript:void(0);" id="hwss_apf_btn_down">Down</a>
				';

        $out .= $aField['after_label'];
        return $out;
    }
    protected function _socials_button_select(){

    }
    /*additional*/
    /*
    Add inline scripts and styles with getScripts(), getStyles().
    */
    protected function getScripts() {
        #$_aJSArray = json_encode($this->aFieldTypeSlugs);
        //$this->sanitizeSlug('socials_button_hidden')
        //note: field_id has index =0 so, id attribute of form element with format of {field_id}__0
        return "
			jQuery( document ).ready( function(){
			console.log( 'debugging: loaded' );
				get_items_pick_socials = function (){
				  var options = $('#socials_button__0 option');
				  var values = $.map(options ,function(option) {
						return option.value;
					});
					return values.join(',');
			  }
			  //move item to up
			  $('#hwss_apf_btn_up').bind('click', function() {
				$('#socials_button__0 option:selected').each( function() {
						var newPos = $('#socials_button__0 option').index(this) - 1;
						if (newPos > -1) {
							$('#socials_button__0 option').eq(newPos).before(\"<option value='\"+$(this).val()+\"' selected='selected'>\"+$(this).text()+\"</option>\");
							$(this).remove();
						}
					});
					$('#socials_button_hidden__0').val(get_items_pick_socials());
				});
				//move item to bottom
				$('#hwss_apf_btn_down').bind('click', function() {
					var countOptions = $('#socials_button__0 option').size();
					$('#socials_button__0 option:selected').each( function() {
						var newPos = $('#socials_button__0 option').index(this) + 1;
						if (newPos < countOptions) {
							$('#socials_button__0 option').eq(newPos).after(\"<option value='\"+$(this).val()+\"' selected='selected'>\"+$(this).text()+\"</option>\");
							$(this).remove();
						}
					});
					$('#socials_button_hidden__0').val(get_items_pick_socials());
				});
			});
		" . PHP_EOL;
    }
    protected function getStyles() {
        return ".admin-page-framework-field-select .admin-page-framework-input-label-container {vertical-align: top; }.admin-page-framework-field-select .admin-page-framework-input-label-container {padding-right: 1em;}";
    }

    /*
    Write additional code of procedural subroutine in the setUp() method that will be performed when the field type definition is parsed by the framework.
    */
    protected function setUp() {
        #wp_enqueue_script( 'jquery-ui-datepicker' );
    }
    protected function getEnqueuingScripts() {
        return array(
            #array( 'src' => dirname( __FILE__ ) . '/js/datetimepicker-option-handler.js', ),
        );
    }
    protected function getEnqueuingStyles() {
        return array(
            #dirname( __FILE__ ) . '/css/jquery-ui-1.10.3.min.css',
        );
    }

}