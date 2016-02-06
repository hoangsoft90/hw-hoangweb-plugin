<?php
/**
 * @Class HW_Tabs
 */
class HW_Tabs extends HW_UI_Component{
    #static $tabs_list = array();

    /**
     * tabs items
     * @var array
     */
    var $tabs = array();

    /**
     * tabs data
     * @var array
     */
    var $tabs_data = array();

    /**
     * @param array $tab_info
     * @param array $options
     */
    public function __construct($tab_info=array(), $options= array()) {
        parent::__construct($tab_info, $options);

        //valid
        if(! is_array($options)) $options = array();
        if(! is_array($tab_info)) $tab_info = array();
        if(empty($tab_container)) $tab_container = HW_String::generateRandomString();

        $default_options = array(
            'animate' => false,
            'animationSpeed' => '1000',
            'updateHash' => false
        );
        //easytabs options
        $this->set_options (array_merge($default_options, $options));
        //params
        $params = array(
            'container_id' => 'hw-tabs-container',
            'container_class' => $tab_container,
            'tabs_menu_class' => 'tabs_menu_class',
            'current_tab_class' => '',
            'tab_content_class' => ''
        );
        if(class_exists('HW_Module_Tabs') && HW_Module_Tabs::is_active()) {
            $setting = HW_Module_Tabs::get();
            $params['container_id'] = $setting->get_field_value('container_id','hw-tabs-container');
            $params['tabs_menu_class'] = $setting->get_field_value('tabs_menu_class');
            $params['current_tab_class'] = $setting->get_field_value('current_tab_class');
            $params['tab_content_class'] = $setting->get_field_value('tab_content_class');
        }
        $this->set_params($tab_info, $params);

        /*$this->info['container_id'] = $tab_container;
        $this->info['tabs_menu_class'] = $tab_container;*/

    }
    /**
     * init
     * @param $tab_info
     * @param $options
     */
    static public function init($tab_info, $options=null) {
        #HW_Libraries::add('');
        return new self($tab_info, $options);
    }

    /**
     * valid tabs info
     * @param array $info
     */
    /*public static function valid_tabs_meta($info = array()) {
        $meta = array(
            'container_id',
            'container_class',
            'tabs_menu_class',
            'current_tab_class',
            'tab_content_class'
        );
        $meta= array_flip($meta);
        //remove all number values in array
        $meta=array_map(function($v){
            return is_numeric($v)? '':$v;
        },$meta);

        $meta = array_merge($meta, $info);
        return $meta;
    }*/
    /**
     * add tab
     * @param $id
     * @param $text
     */
    public function add_tab($id, $text) {
        $this->tabs[$id] = $text;
    }

    /**
     * add tab content
     * @param $id
     * @param $content allow shortcode, callback
     */
    public function add_tab_content($id, $content) {
        //validate
        if(isset($this->tabs[$id] )) {
            $this->tabs_data[$id] = $content ;
        }
    }

    /**
     * render tabs ui
     */
    public function render_ui() {
        extract($this->get_params());

        $html= '<div class="hw-tabs '.$container_class.'" id="'.$container_id.'">';
        //tabs menu
        $html .= $this->render_tabs_menu();
        //tab content
        $html .= $this->render_tabs_content() ;

        $html .= '</div>';
        return $html;
    }

    /**
     * render tabs menu
     */
    public function render_tabs_menu() {
        extract($this->get_params());

        //tabs items ui
        $html= '<ul class="etabs '.$tabs_menu_class.'">';
        if(is_array($this->tabs))
            foreach($this->tabs as $id => $text) {
                $html .= "<li class='tab'><a href='#tab-{$id}'>{$text}</a></li>";
            }
        $html .= '</ul>';
        return apply_filters('hw_tabs_menu', $html);
    }

    /**
     * tabs content
     */
    public function render_tabs_content() {
        ob_start();
        extract($this->get_params() );   //tab info
        //tabs content
        if(is_array($this->tabs_data)) {
            foreach($this->tabs_data as $id => $cont) {
                echo "<div class='hw-tab-content {$tab_content_class}' id='tab-{$id}'>";

                do_action('hw_tab_content_before', $id, $this->tabs[$id], $this);

                //parse tab content by invoking callback
                if(is_callable($cont)) {
                    $cont = call_user_func($cont, $id);
                }

                if(is_string($cont)) echo do_shortcode($cont);
                do_action('hw_tab_content_after', $id,  $this->tabs[$id], $this);

                echo '</div>';
            }
        }
        $content = ob_get_contents();
        ob_clean();
        return apply_filters('hw_tabs_contents', $content);
    }
    /**
     * render tabs with easytabs
     * @param array $args
     * @param $options override options
     */
    public function display($args = array('use_default_css' =>1, 'show_tabs'=>1), $options = array()) {
        //valid
        if(!is_array($args)) $args = array();

        #HW_Libraries::enqueue_jquery_libs('easytabs');
        HW_Libraries::get('easytabs')->enqueue_scripts('jquery.easytabs.min.js');

        //easytabs css
        if(isset($args['use_default_css']) && $args['use_default_css']) {
            HW_Libraries::get('easytabs')->enqueue_styles('easytabs.css');
        }
        HW_Module_Tabs::get()->enqueue_style('style.css');

        //prepare HTML struct & data
        if(isset($args['show_tabs']) && $args['show_tabs']) {
            echo $this->render_ui();
        }

        extract($this->get_params() );
        //get options
        if(is_array($options) && !empty($options)) $this->set_options( $options);

        $json = json_encode($this->get_options());

        echo "<script>
jQuery(document).ready(function(){
        //if(typeof $.easytabs == 'functions')
        jQuery('#{$container_id}').easytabs({$json});
});
        </script>";
    }

    /**
     * render scroll tabs
     * @param array $args
     * @param $options override options
     */
    public function display_scrolltabs($args= array(), $options = array()) {
        //scroll tabs js
        HW_Libraries::get('easytabs')->enqueue_scripts('jquery.scrolltracker.js');
        HW_Libraries::get('easytabs')->enqueue_scripts('jquery.vietcodex.track-content-tabs-scroll.js');

        //prepare data
        if(isset($args['show_tabs']) && $args['show_tabs']) echo $this->render_ui();
        do_action('hw_tabs_display_scrolltabs_after', $this);

        $config = $this->get_params();
        extract($config );

        //option
        $opt = array('tabs_topSpacing'=>0, 'debug'=>false, 'current_tab_class'=>'current-tab');
        if($config['current_tab_class']) $opt['current_tab_class'] = $config['current_tab_class'];

        if(is_array($options)) $opt = array_merge($opt, $options);

        echo "<script>
        jQuery(document).ready(function($){
            jQuery('.{$tabs_menu_class}').vcdx_trackContentPos(".json_encode($opt).");
        });
        </script>";
    }

}