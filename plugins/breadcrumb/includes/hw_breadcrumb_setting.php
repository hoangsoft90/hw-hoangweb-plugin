<?php
/**
 * Class HW_Breadcrumb_Setting
 */
class HW_Breadcrumb_Setting{
    /**
     * this class instance
     * @var
     */
    private static $instance;
    /**
     * HW_SKIN instance
     * @var
     */
    public $skin = null;

    function __construct(){
        //set hook actions
        $this->setup_actions();

        //@see https://core.trac.wordpress.org/ticket/10527 #dirname(dirname( plugin_basename( __FILE__ ) )).'/languages'
        //load plugin textdomain
        load_plugin_textdomain('breadcrumb-navxt', false, dirname(dirname( plugin_basename( __FILE__ ) )).'/languages');

        // instance skin, note: if use create instance from class you should use property 'skin'
        if(class_exists('HW_SKIN')){
            $this->skin = new HW_SKIN($this,HW_BREADCRUMB_PATH,'hw_breadcrumb_skins','hw-bcn-skin.php','skins');
            $this->skin->plugin_url = HW_BREADCRUMB_URL;          //set plugin url or url to app that use hw_skin
            $this->skin->enable_external_callback = false;     //turn off/on external callback

            $this->skin->set_template_header_info(array(
                'name' => 'HW Template',
                'description'   => 'Description',
                'author'        => 'Author',
                'uri'           => 'Author URI',
            ));
        }
        #else exit('HW_SKIN class not found in hw-breadcrumb/includes/hw_breadcrumb_setting.php');

    }

    /**
     * setup hooks
     */
    private function setup_actions(){
        add_action('init', array($this, '_hw_bcn_help_init'));
        //add_action( 'admin_menu', array($this, '_change_menu_label') );   //does't work
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));

        add_filter('bcn_opts_update_prebk', array($this, '_bcn_opts_update_prebk'));
        add_action('bcn_settings_general', array($this, '_hw_bcn_settings_general'));
        add_action('bcn_settings_current_item', array($this, '_bcn_settings_current_item'));
    }
    /**
     * return only instance of the class
     * @return HW_Breadcrumb
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * change global admin menu label
     */
    public function _change_menu_label() {
        global $menu;
        global $submenu;


    }
    /**
     * initial something
     */
    public function _hw_bcn_help_init(){
        //reigster help for this plugin
        if(class_exists('HW_HELP') ){
            HW_HELP::set_helps_path('breadcrumb', HW_BREADCRUMB_PATH.'helps');
            HW_HELP::register_help('breadcrumb');
            HW_HELP::load_module_help('breadcrumb');
        }
    }
    /**
     * update breadcrumbNavXT options
     * @param $opts
     */
    public function _bcn_opts_update_prebk($opts){
        //override first early bcn_options
        if(isset($_POST['bcn_options']['hw_override_bcn_options'])) {
            //since breadcrumbnaxt not allow to override default options at this hook, so i will use js script to treat that issue.
            $bcn_opts = unserialize(base64_decode($_POST['bcn_options']['hw_override_bcn_options']));
            $opts = array_merge($opts, (array)$bcn_opts);

        }
        //enable breadcrumb
        if(isset($_POST['bcn_options']['hw_active_skin'])) {
            $opts['hw_active_skin'] = $_POST['bcn_options']['hw_active_skin'] == 'on'? 1 : 0;
        }
        else $opts['hw_active_skin'] = 0;

        //breadcrumb skin option
        if(isset($_POST['bcn_options']['hw_skin'])) {
            $opts['hw_skin'] = $_POST['bcn_options']['hw_skin'];
        }
        // ->old version
        /*if(isset($_POST['bcn_options']['hw_skin_config'])) {
            $opts['hw_skin_config'] = $_POST['bcn_options']['hw_skin_config'];
        }
        */

        //remove current item from breadcrumb
        if(isset($_POST['bcn_options']['hw_remove_current_item'])) {
            $opts['hw_remove_current_item'] = $_POST['bcn_options']['hw_remove_current_item'] == 'on'? 1 : 0;
        }
        else $opts['hw_remove_current_item'] = 0;

        //allow trail items link
        if(isset($_POST['bcn_options']['hw_allow_trail_link'])) {
            $opts['hw_allow_trail_link'] = $_POST['bcn_options']['hw_allow_trail_link'] == 'on'? 1 : 0;
        }
        else $opts['hw_allow_trail_link'] = 0;

        //display breadcrumb in reverse
        if(isset($_POST['bcn_options']['hw_bcn_reverse'])) {
            $opts['hw_bcn_reverse'] = $_POST['bcn_options']['hw_bcn_reverse'] == 'on'? 1 : 0;
        }
        else $opts['hw_bcn_reverse'] = 0;
        //breadcrumb_navxt::setup_options($opts);   //this may no longer be needed

        //save current skin to db for this widget
        $this->skin->save_skin_assets(array(
            'skin' =>  $opts['hw_skin'],
            'object' => 'hw-breadcrumb'
        ));

        return $opts;
    }
    /**
     * enqueue script/stylesheet for admin
     */
    public function _admin_enqueue_scripts(){
        if(class_exists('HW_HOANGWEB') and HW_HOANGWEB::is_current_screen('breadcrumb-navxt')){
            wp_enqueue_style('hw-bcn-style', HW_BREADCRUMB_URL.'/css/style.css');
            wp_enqueue_script('hw-bcn-js', HW_BREADCRUMB_URL.'/js/js.js');
            wp_localize_script('hw-bcn-js', '__hw_breadcrumb', array());
        }
    }

    /**
     * current item bcn setting
     */
    public function _bcn_settings_current_item($opts){
        $remov_current_item = isset($opts['hw_remove_current_item']) && $opts['hw_remove_current_item']? true: false;
        ?>
        <tr>
            <th scope="row">
                <label for="bcn_options_hw_skin"><strong><?php _e('Xóa item hiện tại', 'breadcrumb-navxt'); ?></strong></label>
            </th>
            <td>
                <input type="checkbox" name="bcn_options[hw_remove_current_item]" id="bcn_options_hw_remove_current_item" <?php checked($remov_current_item)?>/> <span>Có</span>
            </td>
        </tr>
        <?php
    }
    /**
     * put more in general setting tab at general section
     */

    public function _hw_bcn_settings_general($opts){
        $inst = HW_Breadcrumb_Setting::getInstance();
        $skin_tag_atts = array(
            'class'=>'widefat',
            'name' => 'bcn_options[hw_skin]',
            'style' => 'width:auto;',
            'id' => 'bcn_options_hw_skin'
        );
        //enable skin toggle
        $enable_skin = isset($opts['hw_active_skin']) && $opts['hw_active_skin']? true : false;

        //skin config   ->old version
        /*if(empty($opts['hw_skin_config']) ) {
            $skin_config_value = $inst->skin->get_config(true); //get skin config
        }
        else $skin_config_value = $opts['hw_skin_config'];*/

        //skin data
        $skin_values = isset($opts['hw_skin'])? $opts['hw_skin'] : array();
        $hash_skin = isset($skin_values['hash_skin'])? $skin_values['hash_skin'] : '';

        //allow item trail
        $allow_trail_link = isset($opts['hw_allow_trail_link']) && $opts['hw_allow_trail_link']? true : false;
        //display reverse
        $display_reverse = isset($opts['hw_bcn_reverse']) && $opts['hw_bcn_reverse']? true : false;
        /*
         echo '<table class="form-table">';
        //create checkobox field
        $this->input_check(__('Link Current Item', 'breadcrumb-navxt'), 'bcurrent_item_linked', __('Yes', 'breadcrumb-navxt'));
        //input text
        $this->input_text(__('Paged Template', 'breadcrumb-navxt'), 'Hpaged_template', 'large-text', false, __('The template for paged breadcrumbs.', 'breadcrumb-navxt'));
        //radio
        $this->input_radio('Spost_post_taxonomy_type', 'category', __('Categories'));
        $this->input_radio('Spost_post_taxonomy_type', 'date', __('Dates', 'breadcrumb-navxt'));
        echo '</table>';
        */
        //get skin setting in which define override bcn options
        if(!empty($hash_skin) && !empty($this->skin)) {
            $file = $this->skin->get_skin_file($hash_skin);
            if(file_exists($file)) {
                $setting = $this->skin->get_file_skin_setting();
                //get theme setting from main skin file or setting file
                if(!empty($setting)) include($setting);
                else include ($file);
                if(isset($theme['bcn_options']) && is_array($theme['bcn_options'])) {
                    $override_bcn_options = base64_encode(serialize($theme['bcn_options']));
                }
            }
        }
        ?>
        <tr>
            <th scope="row">
                <label for="bcn_options_hw_active_skin"><strong><?php _e('Kích hoạt giao diện', 'breadcrumb-navxt'); ?></strong></label>
            </th>
            <td><input type="checkbox" name="bcn_options[hw_active_skin]" id="bcn_options_hw_active_skin" <?php checked($enable_skin)?>/> <span>Có</span></td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="bcn_options_hw_skin"><strong><?php _e('Giao diện', 'breadcrumb-navxt'); ?></strong></label>
            </th>
            <td>
                <?php #echo $inst->skin->get_skins_select_tag(null,$hash_skin,$skin_tag_atts,false);?>
            <!--
            <input type="hidden" name="bcn_options[hw_skin_config]" id="bcn_options_hw_skin_config" value="<?php #echo $skin_config_value?>"/>
            -->
                <?php
                echo $inst->skin->create_total_skin_selector('bcn_options[hw_skin]', $skin_values, array(), array(
                    'show_main_skin' =>1,
                    'show_config_field' => 1,
                    'show_condition_field' => 1,
                    'show_skin_options' => 1,

                ));
                ?>

                <?php if(isset($override_bcn_options)):?>
                    <input type="hidden" name="bcn_options[hw_override_bcn_options]" value="<?php echo esc_attr($override_bcn_options)?>"/>
                    <p>Chú ý: tìm thấy trong skin này có thay thế các cài đặt breadcrumb với thể hiện viền mầu đỏ:
                        <code><em><?php echo implode(',', array_keys($theme['bcn_options']));?></em></code><br/>
                        Các giá trị này được sửa đổi bởi skin , tuy nhiên bạn có thể sửa đổi trên giao diện cài đặt này.
                    </p>
                    <script>
                        jQuery(function($){
                            __hw_breadcrumb.highlight_bcn_options(<?php echo json_encode(($theme['bcn_options']))?>);
                        });

                    </script>
            <?php endif;?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="bcn_options_hw_enable_item_link"><strong><?php _e('Hiển thị liên kết', 'breadcrumb-navxt'); ?></strong></label>
            </td>
            <td><input type="checkbox" name="bcn_options[hw_allow_trail_link]" id="bcn_options_hw_enable_item_link" <?php checked($allow_trail_link)?>/> <span>Có (khuyến cáo nên chọn Có)</span></td>
        </tr>
        <tr>
            <td>
                <label for="bcn_options_hw_bcn_reverse"><strong><?php _e('Hiển thị đảo ngược', 'breadcrumb-navxt'); ?></strong></label>
            </td>
            <td><input type="checkbox" name="bcn_options[hw_bcn_reverse]" id="bcn_options_hw_bcn_reverse" <?php checked($display_reverse)?>/> <span>Có</span></td>
        </tr>
    <?php
    }
}


