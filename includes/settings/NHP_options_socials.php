<?php
# used by includes/hw-nhp-theme-options.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 14/06/2015
 * Time: 07:34
 */
class NHP_Options_socials extends HW_NHP_Options {
    /**
     * all skins data
     * @var
     */
    private $hw_skin_data;

    /**
     * @param $data
     * @param $hw_skin
     * @return mixed
     */
    public function _hw_skins_data($data,$hw_skin) {
        $config = $hw_skin->get_config(false);
        if($config['skin_name'] == 'hw-social-skin.php') {
            $this->hw_skin_data = $data;
        }
        return $data;
    }

    /**
     * nhp_field hw skin output
     * @param $aOutput
     * @param $skin
     * @param $field
     * @param $value
     */
    public function _hwskin_nhp_field_output_callback($aOutput, $skin, $field,$value, $args) {
        if($field['id'] == 'social_skin') {
            //get base field name
            $field_name = $args['field_name'];

            //if(!isset($value['all_skins'])) { //alway update skins
                $all_skins_value = array();
                foreach($this->hw_skin_data as $hash => $theme) {
                    $all_skins_value[$theme['skinname']] = $hash;
                }
                $all_skins_value = base64_encode(serialize($all_skins_value));

            //}else $all_skins_value = $value['all_skins'];


            $aOutput[] = '<input type="hidden" name="'.$field_name.'[all_skins]" id="'.$field['id'].'_all_skins" value="'.esc_attr($all_skins_value).'"/>';

        }
        return $aOutput;
    }
    /**
     * filter skin data in method HW_SKIN::load_skins_data
     * @param $data
     * @return mixed
     */
    public function _hw_skin_data_filter($data, $skin, $inst) {
        //$config = $inst->get_config(false);
        if(/*$config['skin_name'] == 'hw-social-skin.php'*/isset($skin['filename']) && $skin['filename'] == 'hw-social-skin.php'){
            /*$url = explode($config['default_skin_path'].'/'.$config['group'], $data['url']);
            $data['skinname'] = trim($url[1],'\/');*/
            $data['skinname'] = trim($skin['path'], '\/');
            if(!isset($data['md5'])) $data['md5'] = md5($data['screenshot']);
        }

        return $data;
    }
    public function _get_skins_listview_column($out,$skin) {
        if($skin['filename'] == 'hw-social-skin.php') {
            $name = trim($skin['path'], '\/');
            $out .= '<td><code>hw_load_socials("'.$name.'")</code></td>';
        }

        return $out;
    }
    public function _get_skins_listview_colspan_head($colspan, $skin) {
        if($skin['filename'] == 'hw-social-skin.php') {
            return 4;
        }
        return $colspan;
    }
    /**
     * add filters
     */
    private function add_hooks() {
        add_filter('hw_skins_data', array(&$this, '_hw_skins_data'), 10,2);    //$data,$hw_skin
        add_filter('hw_skin_data', array($this, '_hw_skin_data_filter'), 10,3);
        add_filter('HW_SKIN.get_skins_listview.column', array($this, '_get_skins_listview_column'), 10,2);
        add_filter('HW_SKIN.get_skins_listview.header_colspan', array($this, '_get_skins_listview_colspan_head'), 10,2);
    }
    /**
     * register nhp fields
     * @param $sections
     */
    public function get_fields(&$sections) {
        //prepare hooks
        $this->add_hooks();
        $help_tip = '<code></code>';

        $sections['socials-options'] = array(
            'icon'=>NHP_OPTIONS_URL.'img/glyphicons/glyphicons_169_albums.png',
            'title'=>'Mạng xã hội',
            'fields'=> array(
                'facebook_url' => array(
                    'id'=>'fb_url',
                    'type' => 'text',
                    'title'=>'Facebook page URL',
                    'desc' => '<code>hw_option("fb_url")</code>'
                ),
                'google_url' => array(
                    'id'=>'gplus_url',
                    'type' => 'text',
                    'title' => 'Google Plus URL',
                    'desc' => '<code>hw_option("gplus_url")</code>'
                ),
                'twitter_url' => array(
                    'id' => 'twitter_url',
                    'type' => 'text',
                    'title' => 'twitter fanpage url',
                    'desc' => '<code>hw_option("twitter_url")</code>'
                ),
                'youtube_url'=>array(
                    'id' => 'youtube_url',
                    'type' => 'text',
                    'title' => 'Youtube chanel url',
                    'desc' => '<code>hw_option("youtube_url")</code>'
                ),
                'social_skin' => array(
                    'id' => 'social_skin',
                    'type' => 'hw_skin',
                    'title' => 'Giao diện',
                    'desc' => 'Mở rộng skin tạo thêm folder: /wp-content/hw_social_skins.
                        <br/>Lưu ý: Nhấn "Lưu thay đổi" khi có skin mới bổ xung.
                        <br/>Gọi <code>hw_load_socials()</code> để hiển thị với skin đã chọn.',
                    //use by hw_skin
                    'external_skins_folder' => 'hw_social_skins',
                    'skin_filename' => 'hw-social-skin.php',
                    'enable_external_callback' => false,
                    'skins_folder' => 'skins',
                    'apply_current_path' => plugin_dir_path(dirname(dirname(__FILE__))),
                    'plugin_url' => plugins_url('',dirname(dirname(__FILE__))),
                    //'files_skin_folder' => 'images',
                    'display' => 'list', //accept: ddslick, list,select
                    'group' => 'socials', //dynamic/
                    'hwskin_nhp_field_output_callback' => array(&$this, '_hwskin_nhp_field_output_callback')
                )
            )
        );
    }

    /**
     * display socials icon
     * @param string $skin
     */
    public static function do_social_skin($skin = '') {
        //valid
        //if(empty($skin)) $skin = 'default';

        //get option
        $social = hw_option('social_skin');

        if(!isset($social['all_skins']) ) {
            printf('<em>Vui lòng nhấn Save changes <a href="%s">ở đây</a> trước khi sử dụng.</em>', admin_url('admin.php?page=hoangweb-theme-options&tab=socials-options') );
            return;
        }
        $data_skins = unserialize(base64_decode($social['all_skins']));
        //get user skin
        if(empty($skin) && isset($social['hash_skin'])) {   //default user selected skin
            $hash_skin = $social['hash_skin'];
        }
        elseif($skin && isset($data_skins[$skin])) {    //get any skin by name
            $hash_skin = $data_skins[$skin];
        }
        if( !isset($hash_skin)) return;      //validate

        if($hash_skin && isset($social['hwskin_config']) && class_exists('APF_hw_skin_Selector_hwskin')){
            $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($social);

            $file = ($skin->instance->get_skin_file($hash_skin));//(new HW_SKIN)->get_skin_url('');
            //get theme setting file
            $setting = $skin->instance->get_file_skin_setting() ;
            if(file_exists($setting)) include($setting);

            //$skin_url = $skin->instance->get_skin_url('');
            $skin_variables = $skin->instance->get_skin_variables();

            if(file_exists($file)) {
                HW_HOANGWEB::load_class('HW_Twig_Template');

                //get templates folder from skin
                if(isset($theme) && isset($theme['templates_folder'])) {
                    $tpl = $theme['templates_folder'];
                }
                else $tpl = '';
                //prepare data for template
                $data = array(
                    'facebook_url' => hw_option('fb_url'),
                    'googleplus_url' => hw_option('gplus_url'),
                    'twitter_url' => hw_option('twitter_url') ,
                    'youtube_url' => hw_option('youtube_url'),
                    'skin_variables' => $skin_variables,
                    'SKIN_URL' => $skin_variables['url']
                );

                $twig = HW_Twig_Template::create($skin->instance->get_file_skin_resource($tpl));
                $twig->set_template_data($data);    //inject data to current twig for skin using

                $content = $skin->instance->render_skin_template($data,false);  //data, return=false
                if($content!==false) echo $content;
                if($skin->instance->allow_skin_file()) include($file);    //load footer skin configuration
            }
            //valid
            if(!isset($theme['styles'])) $theme['styles'] = array();
            if(!isset($theme['scripts'])) $theme['scripts'] = array();
            //put stuff from skin
            if(count($theme['styles']) || count($theme['scripts'])) {
                $skin->instance->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
            }
        }
    }
}