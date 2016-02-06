<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 30/10/2015
 * Time: 10:52
 */
/**
 * Class HW_Module_Config_general
 */
class HW_Module_Config_general extends HW_Config_Module_Section{

    /**
     * initial
     * @return mixed|void
     */
    function start() {

        $this->register_command('save_custom_wp_menus', array($this, 'save_custom_wp_menus'));
        $this->register_command('refresh_caches', array($this, 'refresh_caches'));
        #$this->register_command('install', array($this, '_one_click_installation'));
        $this->register_command('install', array($this, 'test1') /*, array('init',15,0)*/);
        $this->register_command('resetwp', array($this, 'reset_wp_data') );

        add_filter('hw_list_to_do', array($this, '_filter_list_to_do'), 10,2) ;
        add_filter('hw_import_posts', array($this, '_import_posts'));

    }
    /**
     * first segment for command
     * @param $command
     * @param $args
     */
    public function do_install_first_segment($command, $args) {
        $this->command_log('do_install_first_segment');
        #file_put_contents(get_home_path().'/.maintenance', 'Xin loi! He thong dang cap nhat.');    //please don't while installer working
    }
    /**
     * end segment
     * @param $command
     * @param $args
     */
    public function do_install_end_segment($command, $args) {
        $this->command_log('do_install_end_segment');
        #rename(get_home_path(). '/.maintenance', get_home_path(). '/1.maintenance');
    }
    /**
     * @filter hw_list_to_do
     * @param $data
     * @param $args
     */
    public function _filter_list_to_do($data, $args) {
        if($args['module']->get_config()!==$this) return $data;

        $theme_config = $this->get_option('theme_config');  //or $this->theme_config
        $configuration =$theme_config->item('configuration');
        if(empty($configuration['sample_data'])) {
            $list_demo = array();
            foreach (HW_Sample_Data::get_all_demos() as $name => $demo) {
                $list_demo[$name] = $demo->info('title');
            }
            $data[] = array('type' => 'sample_data', 'value' => $list_demo, 'display' => 'Demo data', 'field' => 'select');
        }
        $data[] = array(
            'type' => 'dynamic-segment', 'value'=>'', 'display'=> 'Test dynamic segment', 'field'=>'checkbox',
            'callback' => array($this, 'test_dynamic_segment'), 'nest_segment'=>false
        );
        return $data;
    }
    /**
     * @param $data
     * @param $command
     * @param $segments
     * @param $ajax
     * @return mixed|void
     */
    public function list_to_do($data, $command, $segments, $ajax) {
        if($command =='install') {
            //$main_segments = $segments->get_main_segments_name();
            $data[] = array('type'=> 'other','name'=> 'clear_data_opt', 'value'=> '', 'display' => 'Clear old wp posts', 'field' => 'checkbox');
            return $data;
        }
    }

    /**
     * clear old wp posts option
     */
    private  function clear_old_posts() {return;
        static $clear=false;
        if($clear==false) {
            global $wpdb;
            $this->command_log('Clear all old posts.');
            $removed = array();
            if($wpdb->query("TRUNCATE TABLE $wpdb->posts")) $removed[] = 'Posts removed';
            if($wpdb->query("TRUNCATE TABLE $wpdb->postmeta")) $removed[] = 'Postmeta removed';
            if($wpdb->query("TRUNCATE TABLE $wpdb->comments")) $removed[] = 'Comments removed';
            if($wpdb->query("TRUNCATE TABLE $wpdb->commentmeta")) $removed[] = 'Commentmeta removed';
            if($wpdb->query("TRUNCATE TABLE $wpdb->links")) $removed[] = 'Links removed';
            if($wpdb->query("TRUNCATE TABLE $wpdb->terms")) $removed[] = 'Terms removed';
            if($wpdb->query("TRUNCATE TABLE $wpdb->term_relationships")) $removed[] = 'Term relationships removed';
            if($wpdb->query("TRUNCATE TABLE $wpdb->term_taxonomy")) $removed[] = 'Term Taxonomy removed';
            if($wpdb->query("DELETE FROM $wpdb->options WHERE `option_name` LIKE ('_transient_%')")) $removed[] = 'Transients removed';
            $wpdb->query("OPTIMIZE TABLE $wpdb->options");
        }
    }
    //dynamic segment
    public function test_dynamic_segment($command, $args) {
        $this->command_log('test_dynamic_segment');
        //HW_Logger::log_file('test_dynamic_segment');
    }
    //test
    public function test1($command, $parent_segment=''){
        //run other jobs in list to do
        //clear old posts
        if($command->get_option('clear_data_opt')) $this->clear_old_posts();

        #$this->activate_plugins();
        #$this->activate_modules();
        $command->add_segment(array($this, 'activate_modules'), 'active modules');
        $id = $command->add_segment(array($this, 'install_sample_data'), 'install sample data.', true);
        $command->add_segment(array($this, 'setup_site_meta'),'setup site meta');
        $command->add_segment(array($this, 'test2'),'setup modules', true);
        $command->add_segment(array($this, 'setup_sidebars'), 'adding sidebars');

        if($command->get_option('sample-data')) {
            $command->add_segment_data($id, array('sample_data' => $command->get_option('sample-data')));
        }

        /*$modules = $this->theme_config->item('modules');
        foreach ($modules as $module) {
            if($module['status'] && $module['name']!=='') {
                $this->setup_module_data($module['name']) ;
            }
        }*/
        /*$this->installer->call_by_hook('init', function() use($module){
            HW_TGM_Module_Activation::get_register_modules(array('active'=>1));
        });*/
    }
    public function test2($command, $parent_segment=''){
        #$modules= $this->get_option('theme_config')->item('modules');
        $modules = $this->theme_config->item('modules');
        //$command->add_segment(function() use($modules){
            foreach ($modules as $module) {
                if($module['status'] && in_array($module['name'],array(
                        //'hw-list-custom-taxonomy-widget','hw-yahoo-and-skype-status','hw-widget-weather-exchangeRate','gallery',
                        'theme-options'
                    ))) {
                    //$this->setup_module_data($module['name']) ;continue;
                    $command->add_segment(function() use($module ){
                            $this->setup_module_data($module['name']) ;
                            $this->command_log( $module['name']);
                        }, 'setup module '. $module['name'],0, $parent_segment);
                }
            }

        //}, 'XX');

    }
    public function _import_posts($posts) {

        return $posts;
    }
    /**
     * reset wp
     * @command resetwp
     */
    public function reset_wp_data() {
        $this->get_cli_cmd('resetwp', array(), 'hoangweb');
    }

    /**
     * @param $command
     * @param $args exist if have no segments inside
     */
    public function install_sample_data($command, $parent='') {
        $data = $command->get_segment_data() ;  //current segment data
        //get segment result
        if($command->get_option('result')) {
            $data['result'] = $command->get_option('result');
        }
        $start_num = 2;
        /*$configuration = $this->theme_config->item('configuration');
        if(empty($configuration['sample_data'])) {
            $configuration['sample_data'] = $command->get_segment_data('sample_data');
        }
        if(!empty($configuration['sample_data'])) {
            $demo = HW_Sample_Data::get_demo($configuration['sample_data']);
            //$this->theme_config->import($demo->get_demo_config_file());#do not need
            if($demo) {
                $demo->set_active();
                //$demo->install();
                $this->command_log('Install sample data for "'.$demo->info('title').'".');

            }

            #else {HW_Logger::log_file('sdfdt3496749656');

                //$demo->install();
                #$command->bundle_segments(array($this,'install_sample_data_branch'), 2, array('demo'=> $demo)); //min 2 , u=final to find new segments
            #}

            if($demo) {
                //return array('posts_count' => $demo->importer->get_import_results('posts') );
            }
        }*/
        if(isset($data['result']) && isset($data['result']['posts_count'])) {
            $num_segments =$data['result']['posts_count'];
            $command->bundle_segments(array($this,'install_sample_data_branch'), $num_segments+$start_num);
        }
        else {
            //$demo->install();
            $command->bundle_segments(array($this,'install_sample_data_branch'), $start_num); //min 2 , u=final to find new segments
        }
        #$command->add_segment(array($this,'install_sample_data_branch'), 'sddgd',0, $parent);
    }

    /**
     * @param $command
     * @param $args
     */
    public function install_sample_data_branch($command, $args ) {
        //$demo = $command->get_segment_data('demo');
        $page = $command->get_segment_data('page');

        $configuration = $this->theme_config->item('configuration');
        if(empty($configuration['sample_data'])) {
            $configuration['sample_data'] = $command->get_segment_data('sample_data');
        }
        if(!empty($configuration['sample_data'])) {
            $demo = HW_Sample_Data::get_demo($configuration['sample_data']);
            //$this->theme_config->import($demo->get_demo_config_file());#do not need
            if($demo) {
                $demo->set_active();
                $demo->install($page);
                $this->command_log('Install sample data for "'.$demo->info('title').'".');

                return array('posts_count' => count($demo->importer->get_import_results('posts'))+2 );
            }
        }

    }
    /**
     * setup site metadata
     * @param $command
     */
    public function setup_site_meta($command, $args) {#$this->command_log('setup site meta');return;
        //get current template context
        $theme_config = $this->get_option('theme_config');  //parse config file for current theme
        #site meta

        if($theme_config->item('site')) {
            $site = $theme_config->item('site');

            HW_NHP_Main_Settings::update_data(array('last_tab' => '1'));   //fix current tab
            //update site info (name & description, phone...)
            HW__Site::update_site_info($site);
            //update logo
            if(!empty($site['logo'])) {
                //get relative path from current theme directory
                if(!HW_URL::valid_url($site['logo'])) {
                    $site['logo'] = get_stylesheet_directory_uri() . '/'. $site['logo'];
                }
                HW__Site::set_logo($site['logo']);
            }
            //set banner if exists
            if(!empty($site['banner'])) {
                //get relative path from current theme directory
                if(!HW_URL::valid_url($site['banner'])) {
                    $site['banner'] = get_stylesheet_directory_uri() . '/'. $site['banner'];
                }
                HW__Site::set_banner($site['banner']) ;
            }
            //update user info
            $user = _hw_global('admin')->load_api('HW_WP_User');
            $user->update_user(array(
                'user_email' => $site['email'],
                'user_url' => 'http://hoangweb.com',
                'description' => $site['footer_text'],
                //'user_pass' => '' //do not change user pass while installation processing
            ));
        }
        if($theme_config->item('configuration')) {

        }
        $this->command_log('setup site meta');

    }

    /**
     * general options
     * @param $command
     * @param $args
     */
    public function setup_general_settings($command, $args) {

        if($this->theme_config->item('configuration')) {
            $general = $this->theme_config->item('configuration');

            //set locale
            if(empty($general['locale'])) $general['locale'] = 'vi';
            $languages = _hw_global('admin')->load_api('HW_WP_Languages');
            $languages->change_site_language($general['locale']);

            //set thumbnail size
            $media = _hw_global('admin')->load_api('HW_WP_Media');
            if(!empty($general['media']))
            foreach ($general['media'] as $name => $size) {
                $media->set_image_size($size, $name);
            }

        }

    }
    /**
     * finish setup
     */
    private function setup_finish() {
        //build custom admin menus
        $this->refresh_wp_menus_cache();
        $this->save_custom_wp_menus();
    }

    /**
     * config modules
     * @Param $command
     * @param $parent
     */
    public function setup_modules($command, $parent='') {
        //config modules
        $modules = $this->theme_config->item('modules');
        foreach ($modules as $module) {
            if($module['status']) {

                $command->add_segment(function() use($module ){
                    $this->setup_module_data($module['name']) ;
                    //$this->command_log( $module['name']);
                }, 'setup module '. $module['name'], 0, $parent);
            }
        }
    }
    /**
     * install site
     * @command install
     * @param $args
     */
    public function _one_click_installation($command, $args) {

        //parse config file for current theme
        $theme_config = $this->theme_config;//empty($this->theme_config)? HW__Template::get_theme_config() : $this->theme_config;
        if(empty($theme_config)) {
            $this->command_log('Sory, we can`t done task because Invalid theme configuration file.');
            return; //invalid theme config for current theme
        }
        //clear old posts
        if($command->get_option('clear_data_opt')) $this->clear_old_posts();

        //note: do not put your job here because everytime call segment get there place
        $id = $command->add_segment(array($this, 'install_sample_data'), 'install sample data.', true); //install sample data
        $command->add_segment(array($this, 'setup_site_meta'), 'config site meta');
        $command->add_segment(array($this, 'setup_general_settings'), 'config general settings');
        //register sidebars
        $command->add_segment(array($this, 'setup_sidebars'), 'adding sidebars');

        //active modules
        $command->add_segment(array($this, 'activate_modules'), 'active modules');
        //active plugins
        $command->add_segment(array($this, 'activate_plugins'), 'active plugins') ;

        //config modules
        $command->add_segment(array($this, 'setup_modules'), 'setup modules', true);
        $command->add_segment(array($this, 'setup_finish'), 'Final step.');
        //
        if($command->get_option('sample-data')) {
            $command->add_segment_data($id, array('sample_data' => $command->get_option('sample-data')));
        }
    }

    /**
     * preparing sidebars, because this data maybe used for any module
     */
    protected function setup_sidebars() {
        hwawc_unregister_all_sidebars();    //remove all sidebars for first
        $sidebars = $this->theme_config->item('sidebars');
        foreach($sidebars as $name=> $item) {
            hwawc_register_sidebar($item['params']);
        }
        $this->command_log('Setup sidebars successful');
    }

    /**
     * setup module data
     * @param $module
     */
    protected function setup_module_data($module) {
        $module_inst = HW_Module_Settings_page::get_modules($module);    #(new HW_Module())->get_config();
        if(!$module_inst) {  //module not found
            $this->command_log(sprintf('Module %s chưa được kích hoạt.', $module));
            return ;
        }
        #$module_config = $module_inst->get_config();

        $wxr_files = $module_inst->get_module_wxr_files();
        if(!isset($wxr_files['export']) ) return; //config file not exists
        $file = $wxr_files['export'] ;

        $module_inst->setup_demo($file);
        $this->command_log('Cài đặt module '.$module.'.');
    }
    /**
     * active modules
     */
    protected function activate_modules() {
        $modules = $this->theme_config->item('modules');
        if(is_array($modules)) {
            foreach($modules as $module) {
                if((isset($module['core']) && $module['core'])    //ignore for core modules
                    || !empty($module['active'])    //actived module
                ) continue;

                if($module['status'] == 1) {
                    //hw_activate_modules();
                    $this->run_cli_cmd('activate',array('name' => $module['name']), 'hw-module');
                }
                else $this->run_cli_cmd('deactivate',array('name' => $module['name']), 'hw-module');
            }
        }
    }

    /**
     * active plugins
     */
    protected function activate_plugins() {
        $plugins = $this->theme_config->item('plugins');
        $cmd = '';
        foreach ($plugins as $plugin) {
            if($plugin['status'] == 1) {
                //$cmd .= 'wp plugin install '.$plugin['name'].';';
                $cmd .= $this->get_cli_cmd('install', array($plugin['name'], '--activate'), 'plugin') .'&&';
            }
            else $cmd .= $this->get_cli_cmd('deactivate', array($plugin['name']), 'plugin') .'&&';//"wp plugin deactivate {$plugin['name']};" ;
        }
        if($cmd) $this->installer->realtime_exec(trim($cmd, '&&'));
    }
    /**
     * delete all caches
     */
    public function refresh_caches() {
        $this->refresh_wp_menus_cache();
    }
    /**
     * refresh cache settings, we need to declare public because it call from out of this class (ie: hw-caches.php)
     * @param bool $output
     */
    public function refresh_wp_menus_cache($output=true) {
        //custom wp menus cache
        HW_HOANGWEB::del_wp_option('hw_custom_wp_menu');
        HW_HOANGWEB::del_wp_option('hw_custom_wp_submenu');
        HW_HOANGWEB::del_wp_option('other_modules_submenus');

        if($output) $this->command_log( 'deleted all menus caches !');
    }
    /**
     * build & save custom wp menus
     */
    public function save_custom_wp_menus() {
        //refresh cache for first
        $this->refresh_wp_menus_cache();

        $submenu = (HW_SESSION::get_session('submenu'));
        $menu = (HW_SESSION::get_session('menu'));

        #HW_Modules_Manager::build_modules_wp_menu();
        if($menu) HW_HOANGWEB::add_wp_option('hw_custom_wp_menu', $menu);
        if($submenu) HW_HOANGWEB::add_wp_option('hw_custom_wp_submenu', $submenu);
        //remove sessions
        HW_SESSION::del_session('submenu', 'menu');

        $this->command_log('Build & Save custom wp menus !');
    }
    public function test() {
        $this->save_custom_wp_menus();
        echo 'sddhfgh46';
    }
}
HW_Module_Config_general::register_config_page();