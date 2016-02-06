<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_ButtonToggle_widget
 */
class HW_ButtonToggle_widget{
    /**
     * save current widget object
     * @var unknown
     */
    private $widget = null;

    /**
     * @var array|null
     */
    private $options = null;

    /**
     * index this class apply for widgets. one widget has more this class instance so it is make them independent  
     * @var unknown
     */
    static $index = 0;

    /**
     * instance this class
     * @param unknown $widget: reference widget
     * @param unknown $instance: widget instance
     */
    public function __construct($widget, &$instance = array()){
        if($widget instanceof WP_Widget){
            $this->widget = $widget; //save curernt working widget 
        }
        //set widget instance
        if(is_array($instance) && count($instance)){
            $this->options = $instance;
        }
        
        add_action('in_widget_form', array($this,'hw_awc_in_widget_form'),10,3);
        self::$index++;   //next instance
    }
    public function hw_awc_in_widget_form(){}
    /**
     * in widget form update hook
     * @param unknown $instance
     * @param unknown $new_instance
     * @param unknown $old_instance
     */
    public static function _hwbtw_in_widget_form_update($instance, $new_instance, $old_instance){        
        global $wp_registered_widgets;        
        $widget_obj = $wp_registered_widgets[$new_instance['hwbtw-widget-id']];
        //re-create this class assign to current widget
        $hwbtw = new self($widget_obj['callback'][0], $new_instance);
    //this not work if has more than one button toggle, just only save for first toggle
        $expandoptions_field = $hwbtw->generate_name('expandoptions',true);session_start();$_SESSION['a1']=$widget_obj['callback'];
        if(isset($new_instance[$expandoptions_field])) {
            $instance[$expandoptions_field] = ($new_instance[$expandoptions_field]);  //save expand options
        }
        return $instance;
    }
    /**
     * admin enqueue stuffs
     * @hook admin_enqueue_scripts
     */
    public static function _hwbtw_admin_enqueue_scripts(){
        wp_register_style('hwbtw-style-1', plugins_url('css/hwbtw-style.css',dirname(__FILE__)));
        wp_enqueue_style('hwbtw-style-1');
    }
    /**
     * valid widget class instance
     * @return number
     */
    private function valid_widget(){
        return $this->widget? 1:0;
    } 
    /**
     * generate unique name to wrap js function in current widget
     * @return string
     */
    private function generate_object_name(){
        if(!$this->valid_widget()) return;
        //return 'hw_toggle_'.preg_replace('/[@#\$%\^&\*\(\)\|\{\}+\=\-\~\!]+/','_',$this->generate_name('Expand'));
        return 'hw_toggle_'.HW_Validation::valid_objname($this->generate_name('Expand'));
    }
    /**
     * create unique string base on reference widget
     * @param unknown $str: give name
     * @return bool $exclude_id: don't prefix widget base id
     */
    private function generate_name($str,$exclude_id = false){
        $id = $exclude_id? '': $this->widget->id_base.'_';
        return 'hwbtw_'.$id.$str.'_'.$this->widget->number.'_'.self::$index;
    }
    /**
     * 
     * return current widget instance data
     * @Param array $instance: if pass widget instance directly 
     */
    private function get_widget_instance($instance = array()){
        if(!$this->valid_widget()) return;
        if($this->options) return $this->options;
        
        if(is_array($instance)) {   //override
            $this->options = &$instance;
        }
        //get current widget instance
        if(!isset($this->options) || !$this->options){
            $options = get_option($this->widget->option_name);
            if(isset($options[$this->widget->number])) $this->options = $options[$this->widget->number];            
        }
        if(!isset($this->options['widget_id'])) $this->options['widget_id'] = $this->widget->number;	//assign widget id to instance
        return $this->options;
    } 
    
    /**
     * show button tog & open wrapper
     * @param string $title: set title on button tog
     */
    public function set_button_toggle_start_wrapper($title = ''){
        $widget = $this->widget;    ///get widget obj
        $expandtog_class = $this->generate_name('expand-options');  //expand tog class
        $expand_container_class = $this->generate_name('all-options');      //expand container class
        $expandoptions_field = $this->generate_name('expandoptions',true);  //expandoptions widget form field
        $title = $title? $title : 'Nâng cao...';   //button title
        
        $obj = $this->generate_object_name();
        //get widget instance
        $instance = $this->get_widget_instance();
        //get saved expand option
        $expandoptions = isset($instance[$expandoptions_field])? $instance[$expandoptions_field] : 'contract';
        
        ?> 
        <script type="text/javascript">
        var <?php echo $obj?> = {};
        /**
		 * expand options
		 * @param id
		 * @param widget: widget id
		 */
		<?php echo $obj?>.Expand = function(id){
				    jQuery('#' + id).val('expand');
				    jQuery('.<?php echo $expand_container_class?>').slideDown();
				    jQuery('.<?php echo $expandtog_class ?>').hide();
			};
		/**
		 * collapse options
		 * @param id
		 * @param widget: widget id
		 */
		 <?php echo $obj?>.Contract = function (id){
			jQuery('#' + id).val('contract');
			jQuery('.<?php echo $expand_container_class?>').slideUp(400,function(){
			         jQuery('.<?php echo $expandtog_class?>').show();
				}); 
			  
		};
		jQuery(document).ready(function(){
			var status = jQuery('#<?php echo $widget->get_field_id($expandoptions_field); ?>').val();
			if(status == 'expand')
				jQuery('.<?php echo $expandtog_class?>').hide();
			else if(status == 'contract'){
				jQuery('.<?php echo $expand_container_class?>').hide();
			}
		});
        </script> 
        <!-- bring widget id to $instance param on hook widget_update_callback -->
        <input type="hidden" name="<?php echo $widget->get_field_name('hwbtw-widget-id')?>" value="<?php echo $widget->id?>"/>
        <div class="<?php echo $expandtog_class ?> hwbtw-button-title button"><a href="javascript:void(0)" onclick="<?php echo $obj?>.Expand('<?php echo $widget->get_field_id($expandoptions_field); ?>')" ><?php echo $title?></a></div>
        <div class="<?php echo $expand_container_class?>">
            <div class="<?php echo $this->generate_name('contract-options')?> hwbtw-button-title button"><a href="javascript:void(0)" onclick="<?php echo $obj?>.Contract('<?php echo $widget->get_field_id($expandoptions_field); ?>')" >Ẩn <?php echo $title?></a></div>
            <input type="hidden" value="<?php echo $expandoptions; ?>" id="<?php echo $widget->get_field_id($expandoptions_field); ?>" name="<?php echo $widget->get_field_name($expandoptions_field); ?>" />
        <?php
    }
    /**
     * close tag
     */
    public function set_button_toggle_end_wrapper(){
        echo '</div>';
        
    }

    /**
     * used in HW_HOANGWEB::register_class
     */
    public static function __init(){
        add_action('admin_enqueue_scripts', 'HW_ButtonToggle_widget::_hwbtw_admin_enqueue_scripts',10);  //admin enqueue scripts
        add_filter('widget_update_callback', 'HW_ButtonToggle_widget::_hwbtw_in_widget_form_update',10,3);  //update widget instance
    }
    public function ___call($method, $args){
        //do nothing
    }

}
