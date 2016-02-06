<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

if(class_exists('HW_AWC_WidgetFeature')) :
/**
 * AWC_WidgetFeature_title_link
 */
class AWC_WidgetFeature_title_link extends HW_AWC_WidgetFeature{
    public function is_active(){}
    /**
     * indicate widget title
     * @param $t
     * @param $instance
     */
    public static function get_widget_title($t, $instance) {
        $title = '';
        if($t->id_base == 'xx') {
            //get title for for certain widget
        }
        else {
            $title = isset($instance['title'])? $instance['title'] : '';
        }
        $title = apply_filters('hw_WidgetFeature_title_link_filter_title', $title, $t, $instance);
        return $title;
    }
    /**
     * load option widget title link
     * @param WP_Widget $t: widget object
     * @param array $instance: widget data
     */
    function do_widget_feature($t,$instance = array()) {
        HW_HOANGWEB::load_class('HW_UI_Component');

        $this->widget_instance = $instance;
        $title = self::get_widget_title($t,$instance);
        $link = $this->get_field_value('link_widget_title');
        //link targets
        $link_targets = array('_blank'=>'_blank', '_self'=> '_self','_parent' => '_parent', '_top' => '_top');
    ?>
        <fieldset><legend>Liên kết tiêu đề</legend>
            <!-- hidden fields -->
            <input type="hidden" name="<?php echo $this->get_field_name('title')?>" id="<?php echo $this->get_field_id('title')?>" value="<?php echo $title?>"/>
            <input type="hidden" name="<?php echo $this->get_field_name('widget_name')?>" id="<?php echo $this->get_field_id('widget_name')?>" value="<?php echo $t->id_base?>"/>

            <p><em>Chú ý: Đặt tiêu đề widget và nhấn lưu widget một lần nữa sau khi chọn liên kết.</em></p>
        <p>
            <label for="<?php echo $this->get_field_id('link_widget_title')?>"><strong>Trỏ Liên kết vào tiêu đề</strong></label>
            <input type="text" class="hwawc-hidden" name="<?php echo $this->get_field_name('link_widget_title')?>" id="<?php echo $this->get_field_id('link_widget_title')?>" value="<?php echo $link?>"/>
        <div>
            <button class="button" id="<?php echo $this->get_field_id('insert-link-btn')?>">Chọn liên kết</button>
                <span class="link">
                    <?php if($link){?>
                        <a href="<?php echo $link?>" target="_blank">Mở liên kết</a>
                    <?php }?>
                </span>
        </div>
            <div>
                <label for="<?php $this->get_field_id('target_attr')?>"><strong>Target</strong></label>
                <?php echo HW_UI_Component::build_select_tag($link_targets, $this->get_field_value('target_attr') , array(
                    'name' => $this->get_field_name('target_attr'),
                    'id' => $this->get_field_id('target_attr'),
                ));
                ?>
            </div>

        <script>
            var wpLinkL10n = {"title":"Insert\/edit link","update":"Update","save":"Add Link","noTitle":"(no title)","noMatchesFound":"No matches found."};
            jQuery('#<?php echo $this->get_field_id('insert-link-btn')?>').on('click', function(event) {
                event.preventDefault();

                hw_awc_open_link_dialog(function(url){console.log(url);
                    jQuery('#<?php echo $this->get_field_id('link_widget_title')?>').val(url).attr('value',url);

                },'<?php echo $this->get_field_id('link_widget_title')?>',jQuery(this).next());

                return false;
            });
        </script>
        </p>
        </fieldset>
    <?php
    }
    public function do_widget_feature_frontend($widget, $instance) {}
    /**
     * @param mixed $title: widget title or widget feature values
     * @param string $link: field link_widget_title value (optional)
     */
    private function hwawc_update_widget_links($values,$link= ''){
        $links = get_option('hwawc_widget_title_links');
        if(!$links) $links = array();
        if(is_array($values)){
            $values['link'] = $link = $values['link_widget_title'];    //get widget anchor link
            //get widget title
            if(isset($values['title'])){
                $title = $values['title'];
            }
            else {
                $title = reset($values);  //get first item in array
            }

        }
        if(isset($title) && is_string($title) ){
            $san_title = sanitize_title(strtolower($title));
            $links[$san_title] = $values;    //add new or modify widget title link
            //update widget links
            update_option('hwawc_widget_title_links', $links);
        }
    }
    /**
     * return all widgets links data or for specific widget
     * @param string $title: get widget link field by title (optional)
     * @return mixed|void
     */
    public static function get_all_widget_links($title = ''){
        static $widget_links;
        if(!$widget_links) $widget_links = get_option('hwawc_widget_title_links', array());
        if($title) {
            $san_title = sanitize_title(strtolower(HW_String::vn_str_filter($title)));
            return isset($widget_links[$san_title])? $widget_links[$san_title] : '';
        }
        return $widget_links;
    }
    /**
     * validation widget instance
     * @param $instance
     * @param $new_instance
     * @param $old_instance
     * @return mixed
     */
    function validation($instance,$new_wf_instance, $old_instance) {
        if(isset($new_wf_instance['link_widget_title'])) {
            /**
             * valid widget title url
             */
            preg_match('#href="(.+)"#',$new_wf_instance['link_widget_title'],$s);
            if(count($s)>=2) $new_wf_instance['link_widget_title'] = $s[1];
            //$instance['link_widget_title'] = $new_wf_instance['link_widget_title'];  // anchor widget title link

            /**
             * update widget links data
             */
            $this->hwawc_update_widget_links($new_wf_instance);
        }
        return $new_wf_instance;
    }
    /**
     * do while create the class instance
     * @param WP_Widget $widget
     * @return mixed|void
     */
    public function init(WP_Widget $widget){
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts') );  //not work
        add_action('admin_footer',array($this, '_hw_awc_admin_footer'));
        //$this->_admin_enqueue_scripts();
    }
    /**
     * put stuff in admin footer
     * @hook admin_footer
     */

    public function _hw_awc_admin_footer(){
        if(HW_HOANGWEB::is_current_screen(array('widgets'))){
            //for wp link popup dialog
            include(plugin_dir_path(__FILE__). '/html/wp-link-form.html');
        }
    }

    /**
     * @wp_hook action admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts() {
        if(HW_HOANGWEB::is_current_screen(array('widgets'))){
            $this->enqueue_script('title_link.js', array('jquery') );  // plugins_url('/title_link.js', __FILE__)

            //wp link poup
            wp_enqueue_script('wplink');
            wp_enqueue_script('wpdialogs');
            wp_enqueue_script('wpdialogs-popup'); //also might need this

            // need these styles
            wp_enqueue_style('wp-jquery-ui-dialog');
            wp_enqueue_style('editor.min.css', includes_url('css/editor.min.css'), null);
            wp_enqueue_style('thickbox');
        }
    }

    public function run(){

    }
    /**
     * modify widget title by associate with internal link from widget option
     * @param string $title: widget title
     */

    static public function _widget_title_link($title){
        if(empty($title)) return;
        $w_title_link = self::get_all_widget_links($title);
        $target = isset($w_title_link['target_attr'])? $w_title_link['target_attr'] : '_self';
        //HW_Menu_Icons::render_icon();

        if(is_array($w_title_link) && !empty($w_title_link)) {
            preg_match('/href=(\'|")(.+?)(\'|")/',$title,$s);
            if(isset($s[2])) {
                $title=str_replace($s[2],$w_title_link['link'],$title);
            }
            else $title = '<a target="'.$target.'" href="'.$w_title_link['link'].'" class="hwawc-link">'.$title.'</a>';
        }
        return $title;
    }
}

/**
 * modify widget titlte
 */
add_filter('widget_title', 'AWC_WidgetFeature_title_link::_widget_title_link');
endif;