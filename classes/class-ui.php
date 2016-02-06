<?php
/**
 * Class HW_UI_Component
 */
class HW_UI_Component {
    /**
     * list instances
     * @var
     */
    public static $list_instances = array();

    /**
     * ui args
     * @var array
     */
    private $params =array();
    /**
     * cloudzoom options
     * @var array
     */
    private $options = array();
    /**
     * get called class
     * @var null
     */
    public $class = null;

    /**
     * main class constructor
     * @param $args
     * @param $options
     */
    public function __construct($args = array(), $options = array()){
        HW_HOANGWEB::load_class('HW_String');
        $this->class = get_called_class(); //get called class
    }
    /**
     * register new tab
     * @param $id
     * @param $options construct params
     */
    public static function register($id, $options = array()) {
        if(!isset(self::$list_instances[$id])) {
            $class = get_called_class(); //get called class
            self::$list_instances[$class.'-'.$id] = new $class($options);
            self::$list_instances[$class.'-'.$id]->id =  $id;    //save current tab id
        }
    }
    /**
     * return tab instance
     * @param $id
     * @return mixed
     */
    public static function get($id) {
        $class = get_called_class(); //get called class
        if(!empty(self::$list_instances[$class.'-'.$id])) {
            return self::$list_instances[$class.'-'.$id];
        }
    }
    /**
     * get component params
     * @param array $info
     * @return array
     */
    public function set_params($info= array()) {
        $args=func_get_args();
        foreach ($args as $arg)
        if(!empty($arg) && is_array($arg)) {
            $this->params = array_merge($this->params, $arg);
        }
        return apply_filters($this->class. '_params', $this->params);
    }

    /**
     * get params
     * @return $this->params
     */
    public function get_params() {
        return $this->params;
    }

    /**
     * set options
     * @param $options
     * @return array
     */
    public function set_options($options = array()) {
        $args=func_get_args();

        foreach ($args as $arg)
        if(is_array($arg) && count($arg)) {
            $this->options = array_merge($this->options, $arg);
        }
        return apply_filters($this->class . '_options', $this->options);
    }

    /**
     * get options
     * @return mixed $this->options
     */
    public function get_options() {
        return $this->options;
    }
    /**
     * generate attributes for element
     * @param array $aAttributes
     * @return string
     */
    public static function generateAttributes(Array $aAttributes = array()) {
        $_sQuoteCharactor = "'";
        $_aOutput = array();
        foreach ($aAttributes as $sAttribute => $sProperty) {
            if (is_array($sProperty) || is_object($sProperty) ) {
                continue;
            }
            $sProperty = ( 'href' === $sAttribute ) ? esc_url( $sProperty ) : esc_attr( $sProperty );
            $_aOutput[] = "{$sAttribute}={$_sQuoteCharactor}{$sProperty}{$_sQuoteCharactor}";
        }
        return implode(' ', $_aOutput);
    }

    /**
     * create select tag
     * @param $options
     * @param string $select
     * @param array $atts
     * @param $empty_option
     * @return string
     */
    public static function build_select_tag($options, $select = '', $atts = array(), $empty_option = false) {
        if(is_array($atts)) $atts = self::generateAttributes($atts);

        $tag = vsprintf('<select %s>', $atts);    //name="%s" id="%s"
        if($empty_option) $tag .= '<option value="">--- '.__('Select').' ---</option>';
        foreach ($options as $value => $text) {
            $selected = ($select == $value)? 'selected="selected"' : '';
            //$selected = selected($select , $value, false);
            $tag .= sprintf('<option value="%s" %s>%s</option>', $value, $selected, $text) ;
        }
        $tag .= ('</select>');
        return $tag;
    }

    /**
     * @param $options
     */
    public static function empty_select_option(&$options) {
        if(is_array($options)) {
            #array_unshift($options, '--- Chọn ---');
            $options = array('--- Chọn ---') + $options;    //keep array keys instead of reindexes
        }
    }

    /**
     * @param $data
     * @param $style_tag
     */
    public static function generateCSS($data, $style_tag= true) {
        if(is_array($data)) {
            $out = $style_tag? '<style>' : '';
            foreach ($data as $selector => $css) {
                $out .= $selector ."{";
                if(is_array($css)) {
                    foreach ($css as $key => $val) {
                        if(trim($val) === '') continue;
                        $out .= "{$key}:{$val};";
                    }
                }
                $out .= "}";
            }
            if($style_tag) $out .= '</style>';
            return $out ;
        }
    }
}