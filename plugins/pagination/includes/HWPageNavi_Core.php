<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 27/05/2015
 * Time: 17:08
 */
class HWPageNavi_Core{
    /**
     * save scbOptions instance
     * @var
     */
    private static $options;

    /**
     * init scbOptions
     * @param $options
     */
    public static function init($options){
        self::$options = $options;
    }

    /**
     * get scbOptions object
     * @return mixed
     */
    public static function getOptions(){
        return self::$options;
    }

    /**
     * render current pagenavi skin
     * @param $html
     */
    public static function render_pagination_skin ($html = '') {
        //get hoangweb scb options
        $options = HWPageNavi_Core::getOptions()->get();
        //$options = PageNavi_Core::$options->get();    //other way

        if(isset($options['hw_skin']['hwskin_config']) && isset($options['hw_skin']['hash_skin'])){
            #$skin = HW_SKIN::resume_skin($options['hw_skin']['hwskin_config']);
            $skin = HW_SKIN::resume_hwskin_instance($options['hw_skin']);
            if(!empty($skin)){
                $file = $skin->instance->get_skin_file($options['hw_skin']['hash_skin']);
                if(file_exists($file)){
                    $theme_setting = $skin->instance->get_file_skin_setting();  //extract theme setting from skin

                    //load theme setting
                    if(file_exists($theme_setting)) include($theme_setting);

                    //custom wp_pagenavi output by skin
                    $html = hw_paginav_output($file, $html);

                    //valid theme setting
                    /*if(!isset($theme['styles']) || !is_array($theme['styles'])) $theme['styles'] = array();
                    if(!isset($theme['scripts']) || !is_array($theme['scripts'])) $theme['scripts'] = array();

                    if(count($theme['styles']) || count($theme['scripts'])) {
                        //put css within head tag
                        $skin->instance->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                    }*/
                    $skin_assets = array('skin_file' => $file );
                    if(isset($theme)) $skin_assets['theme_settings'] = $theme;
                    HW_SKIN::enqueue_skin_assets(array_merge($skin_assets, (array)$skin));

                    return isset($theme) && is_array($theme)? $theme : array();
                }
            }
        }
    }
}

/**
 * filter pagenavi output
 */
add_filter('wp_pagenavi', 'hw_paginav_filter_pagenavi_output', 10, 2);
function hw_paginav_filter_pagenavi_output($html){

    HWPageNavi_Core::render_pagination_skin($html);
    return '<div class="hw-pagenavi-container">'.$html.'</div>';
}
if(hw_is_active_module('product')) :

endif;
/**
 * output wp_pagenavi content & custom by skin
 * @param $skin_file: skin file to override wp_pagenavi content
 * @param $html: default wp_pagenavi output
 */
function hw_paginav_output($skin_file, $html){
    if(file_exists($skin_file)) {
        return include($skin_file);
    }
}

/**
 * register plugin help
 */
add_action('init', '_hwpagenavi_init');
function _hwpagenavi_init(){
    if(class_exists('HW_HELP')){
        HW_HELP::set_helps_path('pagenavi', HW_PAGENAVI_PATH.'helps');
        HW_HELP::register_help('pagenavi');
        HW_HELP::load_module_help('pagenavi');
    }
}