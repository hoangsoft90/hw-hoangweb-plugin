<?php
#/site.class.php
#/root>includes/functions-template.php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 16:44
 */

/**
 * Class HW__Theme_Options
 */
abstract class HW__Theme_Options extends HW_Core{

}

/**
 * Class HW__Template_Condition
 */
abstract class HW__Template_Condition extends HW__Theme_Options{
    /**
     * load conditions
     * @var null
     */
    private static $parse_conditions = null;
    /**
     * whether template is changing
     * @param $bind_data
     * @return bool
     */
    public static function check_template_changing($bind_data, $relation = 'AND') {
        return APF_hw_condition_rules::check_fields_condition($bind_data, $relation);
    }

    /**
     * parse template conditions
     * @param array $dynamic_settings
     */
    public static function parse_template_conditions($dynamic_settings = array()) {
        $pages_condition_and = array();
        $pages_condition_or = array();

        foreach ($dynamic_settings as $id => $setting) {
            $result = $result_or = array();
            //$and = APF_hw_condition_rules::parseQuery($setting['query_data_and']); //AND relation
            //$or = APF_hw_condition_rules::parseQuery($setting['query_data_or']);  //OR relation
            if(!empty($setting['query_data_and'])) {
                $_result = self::check_template_changing($setting['query_data_and'], 'AND');
                list($k, $v) = each($_result);
                $result[] = array('template' => $k, 'result' => $v, 'setting' => $setting); //$result[$k]

                $pages_condition_and = array_merge($pages_condition_and, $result);      //override page result for AND relation
            }
            if(!empty($setting['query_data_or'])) {
                $_result_or = self::check_template_changing($setting['query_data_or'], 'OR');
                list($k, $v) = each($_result_or);
                $result_or[] = array('template' => $k, 'result' => $v, 'setting' => $setting);      //$result_or[$k]

                $pages_condition_or = array_merge($pages_condition_or, $result_or);     //override page result for OR relation
            }
        }

        return array('pages_condition_and' => $pages_condition_and, 'pages_condition_or' => $pages_condition_or);
        #return self::$parse_conditions;
    }
    /**
     * get active dynamic templates setting
     * @return array|mixed
     */
    public static function get_active_templates_settings(){
        $result = get_transient('hw_dynamic_templates_settings');
        if(!$result) {
            $args = array(
                'post_type' => HW_Templates_Manager::post_type,
                'showposts' => -1,
                'orderby' => 'menu_order',
                'order' => 'asc',
                'meta_key' => 'enable',
                'meta_query' => array(
                    //list enable sidebar settings
                    array(
                        'key'       => 'enable',
                        'value'     => '1',
                        /*'compare'   => '==',
                        'type'      => 'NUMERIC',*/
                    ),
                )
            );
            $result = array();
            $query = new WP_Query($args);
            while($query->have_posts()){
                $query->the_post();//$query->next_post();
                $query_data_and = get_post_meta(get_the_ID(), 'query_data_and', true);
                $query_data_or = get_post_meta(get_the_ID(), 'query_data_or', true);

                $result[get_the_ID()] = array(
                    'title' =>get_the_title(),
                    'query_data_and' => $query_data_and,
                    'query_data_or' => $query_data_or,
                    'post_ID' => get_the_ID()
                );
            }
            $query->reset_postdata();   //reset query
            set_transient('hw_dynamic_templates_settings', $result);     //set cache fetch from database
        }

        return $result;
    }
}

/**
 * Class HW__Template_Configuration
 */
class HW__Template_Configuration /*extends HW_SimpleXMLElement*/{
    /**
     * theme configuration file
     */
    const DEFAULT_THEME_CONFIG_FILE = 'theme.xml';
    /**
     * @var
     */
    public  $configuration = array();
    /**
     * current theme configuration
     * @var
     */
    public static $theme_config = array();
    /**
     * parse theme config using wrx format
     * @param $file
     */
    public function parse($file) {
        $site = $menus = $assets = $libs = $modules = $plugins = $positions = $configuration = array();

        $xml = HW_WXR_Parser_SimpleXML::read_simplexml_object($file);
        $namespaces = $xml->namespaces;
        $xml = $xml->xml;

        $xml_parser = new HW_WXR_Parser();//::get_instance();
        $simplexml_parser = $xml_parser->simplexml_parser;

        //site meta
        if(isset($xml->site)) {
            $hw = $xml->site->children($namespaces['hw']);
            if(isset($hw->name)) $site['name'] = (string)$hw->name;
            if(isset($hw->description)) $site['description'] = (string)$hw->description;
            if(isset($hw->logo)) $site['logo'] = (string)$hw->logo;
            if(isset($hw->banner)) $site['banner'] = (string)$hw->banner;
            if(isset($hw->phone)) $site['phone'] = (string)$hw->phone;
            if(isset($hw->email)) $site['admin_email'] = (string)$hw->email;
            if(isset($hw->address)) $site['address'] = (string)$hw->address;
            if(isset($hw->testimonials)) $site['testimonials'] = (string)$hw->testimonials;
            if(!empty($hw->footer_text)) $site['footer'] = (string) $hw->footer_text;
        }
        //configuration
        if(isset($xml->configuration)) {
            $hw = $xml->configuration->children($namespaces['hw']);
            $configuration['sample_data'] =(string) $hw->sample_data;
            $media = $hw->media->children('hw');

            $configuration['media'] = array();
            foreach($media as $image) {
                foreach($image as $size) {
                    $atts = $size->attributes();
                    $configuration['media'][$size->getName()] = array(
                        'width'=> (string)$atts->width, 'height' => (string)$atts->height,
                        'crop' => (string) $atts->crop
                    );
                }
            }
            /*if(!empty($media->thumbnail)) $configuration['media']['thumbnail'] = (string) $media->thumbnail;
            if(!empty($media->medium)) $configuration['media']['medium'] = (string) $media->medium;
            if(!empty($media->large)) $configuration['media']['large'] = (string) $media->large;*/

            $configuration['locale'] = (string)$hw->locale;
        }
        //fetch menus
        foreach ($xml->xpath('/theme/menus/hw:menu') as $menu) {
            $atts = $menu->attribute();
            $menus[(string) $atts['slug']] = (string)$menu;
        }
        //fetch sidebars
        $sidebars = array();#$simplexml_parser->grab_sidebars($xml->xpath('/theme')[0], $namespaces);
        foreach ($xml->xpath('/theme/sidebars/hw:sidebar') as $sidebar_widgets) {
            $atts = $sidebar_widgets->attributes();
            $skin = (string) $atts['skin']; //sidebar skin
            $sidebar_name = (string)$atts['name'];
            if(!isset($sidebars[(string)$atts['name']] )) {
                $sidebars[$sidebar_name] = array('skin' => $skin, 'widgets' => array(),'params'=>array() );
            }

            //get widgets in sidebar
            $hw = $sidebar_widgets->children($namespaces['hw']);
            foreach ($hw->widget as $widget) {
                $name = (string) $widget->attributes()->name;
                $sidebars[$sidebar_name]['widgets'][] = $name;
            }
            //sidebar params
            $sidebars[$sidebar_name]['params'] = array('name' => $sidebar_name);
            if(!empty($hw->params)) {
                $sidebars[$sidebar_name]['params'] = array_merge($sidebars[$sidebar_name]['params'], $simplexml_parser->recursive_option_data($hw->params[0]->children())->option );
            }

        }
        //fetch assets
        foreach ($xml->xpath('/theme/assets') as $items) {
            $atts = $items->attributes();
            $page = isset($atts['page'])? (string) $atts['page'] : '__all__';
            //group by page
            if( !isset($assets[$page])) {
                $assets[$page] = array();
            }
            foreach($items as $item) {
                $atts = $item->attributes();
                $file = array();
                $file['type'] = !empty($atts['type'])? (string) $atts['type'] : '';
                //dependencies
                if(!empty($atts['depends'])) {
                    $file['depends'] = explode(',', (string) $atts['depends']);
                }
                else $file['depends'] = false;
                //handle
                if(!empty($atts['handle'])) $file['handle'] = (string) $atts['handle'];
                if(!empty($atts['ver'])) $file['ver'] = (string) $atts['ver']; //version

                $file['name'] = (string) $item;
                $assets[$page][] = $file;
            }
        }

        //fetch libs
        foreach ($xml->xpath('/theme/libs/lib') as $item) {
            $atts = $item->attributes();
            $lib = isset($atts['name'])? (string) $atts['name'] : (string) $item;
            $libs[] = $lib;
        }
        //fetch modules
        $modules_list = $xml->xpath('/theme/modules');
        if($modules_list) $only_list = (string)$modules_list[0]->attributes()->only_list;
        else $only_list = 0;

        if(!$only_list) {
            $installed_modules =HW_TGM_Module_Activation::get_register_modules(array('active'=>1,'core'=>1), 'or');#HW_Logger::log_file($installed_modules);   //get installed modules, refer to core modules
            foreach ($installed_modules as $slug=>$module) {
                $modules[$slug] = array('name' => $slug, 'status'=>1, 'core'=> !empty($module['force_activation']), 'active'=>1 );
            }
        }

        foreach ($xml->xpath('/theme/modules/module') as $item) {
            $atts = $item->attributes();
            $module['name'] = isset($atts['name'])? (string) $atts['name'] : (string) $item;
            $module['status'] = isset($atts['status'])? (string) $atts['status'] : 1;   //active module as default
            $modules[$module['name']] = $module;
        }
        //fetch wp plugins
        foreach ($xml->xpath('/theme/plugins/plugin') as $item) {
            $atts = $item->attributes();
            $plugin['name'] = isset($atts['name'])? (string) $atts['name'] : (string) $item;
            $plugin['status'] = isset($atts['status'])? (string)$atts['status'] : 1;   //active plugin as default
            $plugins[$plugin['name']] = $plugin;
        }
        //positions
        foreach ($xml->xpath('/theme/positions/position') as $item) {
            $atts = $item->attributes();
            //valid hook name
            $name = isset($atts['name'])? (string) $atts['name'] : (string) $item;
            $name = strtolower(HW_Validation::valid_objname($name));
            //display text
            $text = (string) $item;
            if(empty($text) && $name) $text = $name;

            $positions[] = array('name' => $name, 'text' => $text);
        }
        unset($xml);
        return array(
            'assets' => $assets,
            'libs' => $libs,
            'modules' => $modules,
            'plugins' => $plugins,
            'positions' => $positions,
            'sidebars' => $sidebars,
            'menus' => $menus,
            'site' => $site,
            'configuration' => $configuration
        );
    }

    /**
     * parse theme config data
     * @return object
     */
    public static function parse_theme_config($config = '') {
        //static $theme_config;
        //if(!empty(self::$theme_config[$config])) return self::$theme_config[$config];

        if(!$config ) $config = self::get_config_file();
        if(file_exists($config)  ) {
            if(!isset(self::$theme_config[$config])) self::$theme_config[$config] = new self(null) ;
            //because call the hook of after_setup_theme before do init hook we open all which to call this method
            //and after_setup_theme cause data load a half so data must tobe refresh, all i know about that
            if(1||!HW_TGM_Module_Activation::is_complete_load() ||empty(self::$theme_config[$config]->configuration)) {
                self::$theme_config[$config]->configuration = self::$theme_config[$config]->parse( $config );
            }
        }
        return self::valid_theme_config(self::$theme_config[$config]->configuration)? self::$theme_config[$config] : null;
    }

    /**
     * get theme configs item
     * @param $item
     */
    public function item($item='') {
        if(!$this->configuration || ($item && empty($this->configuration[$item]))) return;
        if($item ) return $this->configuration[$item] ;
        else return $this->configuration;
    }

    /**
     * import theme configuration
     * @param $data
     */
    public function import($data) {
        if(is_string($data)) $data = $this->parse($data);
        if(is_array($data));
        foreach($data as $item => $config) {
            if(isset($this->configuration[$item]) && is_array($this->configuration[$item])) {
                $this->configuration[$item] = array_merge($this->configuration[$item], $config);
            }
            else $this->configuration[$item]= $config;
        }
    }
    /**
     * return current theme config file
     * @Param $page theme config file for specific page
     * @return string
     */
    public static function get_config_file($page = '') {
        if(!empty($page)) $page = '-'. trim($page);
        return get_stylesheet_directory(). '/config/theme'.$page.'.xml';
    }

    /**
     * validate theme configuration file
     * @param $file
     */
    public static function valid_theme_config($file='') {
        if(empty($file)) $file = self::get_config_file();
        if(is_array($file)) $config_data = $file;
        else {
            $config =new self();
            $config_data = $config->parse($file);
        }

        //if(!isset($config_data['configuration']['sample_data'])) return false;    //available options if not found
        return true;
    }
}
/**
 * Class HW__Template
 */
abstract class HW__Template extends HW__Template_Configuration{
    /**
     * text domain
     */
    const DOMAIN = 'hoangweb';
    /**
     * store all instances with fragments (singleton)
     * @var array
     */
    private static  $instances = array();
    /**
     * @var
     */
    private static $layouts;

    /**
     * current template object
     * @var null
     */
    protected static $current_template = null;
    /**
     * active theme config
     * @var null
     */
    protected static $current_theme_config = null;
    /**
     * theme config for certain page
     * @var array
     */
    public $config_data = array();

    /**
     * return parent class instance
     * @return HW__Theme_Options
     */
    public static function getInstance() {
        $parent = get_called_class();
        //if(property_exists(get_called_class(), 'instance')) {
            if( !isset(self::$instances[$parent])) {

                self::$instances[$parent] = new $parent;
            }
            return self::$instances[$parent];
        //}
    }
    /**
     * print all tags from wp blog
     * @param $context: current post or get all tags, accept: 'all','current_post'
     */
    public static function print_all_tags($context = 'all'){
        //get all tags from wp
        if($context == 'all'){
            $tags = get_tags();
            $html = '<div class="post_tags">';
            foreach ( $tags as $tag ) {
                $tag_link = get_tag_link( $tag->term_id );

                $html .= "<a href='{$tag_link}' title='{$tag->name} Tag' class='{$tag->slug}'>";
                $html .= "{$tag->name}</a>,";
            }
            $html = trim($html,',').'</div>';
        }
        //get tags assign to current post
        elseif($context == 'current_post') {
            $html = get_the_tag_list('<p>Tags: ',', ','</p>');
        }
        echo $html;
    }

    /**
     * get all pages
     * @param bool $empty_item
     * @return array
     */
    public static function get_pages_select($empty_item = true) {
        $pages = get_pages();
        $options = array();
        foreach ($pages as $page) {
            $options[$page->ID] = $page->post_title;
        }
        if($empty_item) HW_UI_Component::empty_select_option($options);
        return $options;
    }
    /**
     * return all templates working on frontend
     * @return array
     */
    public static  function getTemplates() {
        self::$layouts = array(
            'single'=> 'Single',
            'archive' => 'Archive',
            'taxonomy'=>'Category/Taxonomy',
            'home' => 'Home',
            'page' => 'Page',
            '404' => '404',
            'author' => 'Author'
        );
        return self::$layouts ;
    }
    /**
     * get layouts templates
     */
    /*function getLayouts() {
        if(empty($this->layouts)) {
            $this->layouts = self::getTemplates();

        }
        return $this->layouts;
    }*/
    /**
     * return current list of loaded templates, used by get_template_part
     * @return string[]
     */
    static public function get_current_template_file() {
        if(isset($GLOBALS['hw_current_theme_template'])) return $GLOBALS['hw_current_theme_template'];  //get current template file in hook 'template_include'

        $included_files = get_included_files();
        $stylesheet_dir = str_replace( '\\', '/', get_stylesheet_directory() );
        $template_dir   = str_replace( '\\', '/', get_template_directory() );

        foreach ( $included_files as $key => $path ) {

            $path   = str_replace( '\\', '/', $path );

            if ( false === strpos( $path, $stylesheet_dir ) && false === strpos( $path, $template_dir ) )
                unset( $included_files[$key] );
        }

        return ( $included_files );
    }
    /**
     * check current page
     * @param $page
     * @param $result: return result of checking current context
     * @return bool
     */
    public static function check_template_page ($page, $result = true){
        //get template class name
        $template_class = HW_HOANGWEB::get_class_by_alias('hw-template_'.$page);
        if(empty($template_class)) $template_class = 'HW__Template_'.$page;

        $data = array();

        switch($page) //return is_single();
        {
            case 'single':
                $data['result'] = is_single();break;
                //return array('result' => is_single(), 'object' => new HW__Template_category());

            case 'home': $data['result'] = is_home();break;   //return array('result' => is_home() || is_front_page());
            case 'page': $data['result'] = is_page();break;   //return array('result' => is_page() );
            case 'taxonomy':
                global $wp_query;
                $data['result']= $wp_query->tax_query || is_category() || is_tax();
                break;
            case 'archive': $data['result'] = is_archive();
                break;
            case 'author': $data['result'] = is_author(); break;
            case '404': $data['result'] = is_404();break;
            case 'admin': $data['result'] = is_admin() ; break;
        }
        if($data['result'] && class_exists($template_class) ) {
            $data['object'] = $template_class::getInstance();//new $template_class();
        }

        return $result == true? $data['result'] : $data;
    }

    /**
     * get current template name
     */
    public static function get_current_template_name() {
        /*foreach(self::getTemplates() as $name => $text) {
            $result = self::check_template_page($name);
            if($result == true) return $name;
        }*/
        $data = self::get_current_template(false);
        return isset($data['name'])? $data['name'] : '';
    }

    /**
     * determine current template
     * @param bool $main_template
     * @return self::$current_template
     */
    public static function get_current_template($main_template=true) {
        if(empty(self::$current_template)) {
            //determine current template
            $pages = array_keys(self::getTemplates()) ;
            $pages[] = 'admin';
            foreach(array_filter($pages) as $page) {
                $temp_result = self::check_template_page($page, false);
                //main config
                $config = HW__Template_Configuration::parse_theme_config();//->configuration;

                if( ($temp_result['result'] == true && isset($temp_result['object'])) ) {
                    $temp_result['name'] = $page;
                    $child_config = array();
                    //parse theme config
                    if(file_exists(self::get_config_file($page))) {
                        $child_config = HW__Template_Configuration::parse_theme_config(self::get_config_file($page))->configuration;
                    }
                    //main config
                    foreach($config->configuration as $name=> &$data) {
                        if(!empty($child_config[$name]) && is_array($data)) {
                            $data = array_merge($data, $child_config[$name]);   //override
                        }
                    }
                    unset($child_config);   //free memory
                    $temp_result['object']->config_data= $config;

                    self::$current_template = $temp_result;
                    break;
                }
            }
        }
        return $main_template? (isset(self::$current_template['object'])? self::$current_template['object']:null) : self::$current_template;
    }

    /**
     * get current theme config data
     * @param string $item
     * @return array|null
     */
    public static function get_active_theme_config_data($item='') {
        $current = self::get_current_template();
        if(empty($current)) return;
        $config = $current->get_config_data();
        //return $item? (isset($config[$item])? $config[$item]: null) : $config;
        if(!$config) return null;
        return $item? ($config->item($item)? $config->item($item): null) : $config->item();
    }
    /**
     * get current context loop template
     */
    public static function get_current_context_loop_template (){
        //$context = self::get_current_template_file();   //get current template
        //get loop templates options
        $loop_temps = hw_get_setting(array('my_templates','main_loop_content_style'));
        if(is_array($loop_temps))
        foreach ($loop_temps as $item) {
            $is_temp = self::check_template_page($item['layout'],false);
            if($is_temp['result'] ) {
                if(isset($is_temp['object'])) $item['template_object'] = $is_temp['object'];
                return $item;
                break;
            }
        }

    }
    /**
     * HW_SKIN::apply_skin_data callback
     * @param $args
     */
    public static function _hw_skin_resume_skin_data($args) {
        extract($args);
        global $wp_registered_sidebars;
        if(isset($sidebar) && isset($skin)) $wp_registered_sidebars[$sidebar]['skin'] = $skin;     //bring skin object into params
        /**
         * override sidebar param
         */
        $sidebar_params = &$args['sidebar_params'];
        if(isset($theme['params']) && is_array($theme['params'])){
            $sidebar_params = array_merge($sidebar_params,$theme['params']);
        }
        //$sidebar_params['xxxxx']= '1';  //just test
    }
    /**
     * main loop
     */
    public static function hw_theme_get_main() {
        //init
        //hw_taxonomy_post_list_widget widget setting
        $setting = array();

        //default sidebar params
        $sidebar_params = array(
            'before_widget' => '<div id="%1$s" class="hw-widget %2$s *1" >',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title %1$s {css_title}">',
            'after_title' => '</h3>',
        );

        //override loop template base context
        $loop_temp = HW__Template::get_current_context_loop_template();

        global $wp_registered_sidebars;
        global $wp_registered_widgets;

        if(!empty($loop_temp)) {

            //$widg = AWC_WidgetFeature_saveconfig::get_widget_setting($loop_temp['widget_config']);

            $setting = AWC_WidgetFeature_saveconfig::get_widget_setting_instance($loop_temp['widget_config'],
                array(
                    'widget' => 'hw_taxonomy_post_list_widget',
                    'group' => 'main_loop'
                ));

            //valid data
            if(! isset($setting['query_data'])) $setting['query_data'] = 'current_context';     //get global $wp_query
            //$setting['widget_title'] = 'sdfgdg';  //set widget title by skin

            //get sidebar box
            if($loop_temp['sidebar'] && isset($wp_registered_sidebars[$loop_temp['sidebar']]) ) {
                $sidebar = $loop_temp['sidebar'];   //sidebar id

                $sidebar_params = $wp_registered_sidebars[$sidebar];
                if(isset($loop_temp['sidebar_widget_skin'])) {
                    $sidebar_widget_skin = $loop_temp['sidebar_widget_skin'];
                    //get change default sidebar skin, here we create 4 holder: sidebar_default, skin1,skin2,skin3
                    $skin_data = HW_AWC_Sidebar_Settings::get_sidebar_setting($sidebar_widget_skin,$sidebar);

                    HW_SKIN::apply_skin_data($skin_data,  'HW__Template::_hw_skin_resume_skin_data' ,array(
                        'sidebar' => $sidebar,
                        'sidebar_widget_skin' => $sidebar_widget_skin,
                        'sidebar_params' => &$sidebar_params
                    ));

                }

            }

            list($widget_id,$t) = each($wp_registered_widgets); //get first widget as demo
            $sidebar_params = HW_AWC::format_widget_sidebar_params($widget_id, $sidebar_params, array(
                'classname' => 'main-content hw-awc-override',
                'widget_id' => 'hw-main-loop-content'
            ));
            //override sidebar params from awc feature assign to widget
            if(isset($sidebar_params) && isset($sidebar)) {
                $sidebar_params = HW_AWC::format_sidebar_params($sidebar, $sidebar_params, $setting);
            }

            //important: since any sidebar skin use wrapper class 'hw-awc-override'
            //$sidebar_params['before_widget'] = sprintf($sidebar_params['before_widget'], /*$widget_id, $classname*/'hw-main-loop-content','main-content hw-awc-override');

            do_action ('hw_before_loop');
            /**
             * output widget, when using the_widget to show widget content,
             *
             * note that you set up & enable sidebar skin at /admin.php?page=hw_sidebar_widgets_settings this mean sidebar apply to yarpp it work for that skin
             * And no related to active sidebar that using on website, which call by function 'dynamic_sidebar' /hw_dynamic_sidebar
             */
            if(class_exists('HW_Taxonomy_Post_List_widget')) {
                the_widget('HW_Taxonomy_Post_List_widget',($setting), $sidebar_params);
            }
            do_action ('hw_after_loop');
        }
        elseif(APF_Page_Templates::get_instance()->hw_loop_template()){
            //do nothing
        }
        //default
        else{
            $context = HW__Template::get_current_template_file();
            //determine current template
            $current = self::get_current_template();
            if($current && method_exists($current, 'Main')) {
                $current->Main(); //display main content of current template
            }

            //filter default template
            do_action('hw_theme_get_main_default', $context);
        }

    }
    /**
     * loop template
     * @param $loop_file
     */
    public static function the_loop($loop_file = 'content') {
        APF_Page_Templates::getInstance()->hw_the_loop($loop_file);
    }
    /**
     * get footer, instead of calling get_footer()
     * @param $slug from get_footer() param
     */
    public static function get_footer($slug=''){
        $footer_skin = hw_option('footer_skin');    //get footer skin
        //skin options
        if(!empty($footer_skin) && !empty($footer_skin['skin_options'])) {
            $skin_options = $footer_skin['skin_options'];
            extract($skin_options);
        }

        if(isset($footer_skin['hash_skin']) && isset($footer_skin['hwskin_config'])){
            $skin = APF_hw_skin_Selector_hwskin::resume_hwskin_instance($footer_skin);

            $file = ($skin->instance->get_skin_file($skin->hash_skin));
            //get theme setting file
            $setting = $skin->instance->get_file_skin_setting() ;//(new HW_SKIN);
            if(file_exists($setting)) include($setting);

            $skin->instance->render_skin_template(compact('col','col_1','col_2','col_3'),true); //render skin twig template

            //load footer template
            if($skin->instance->allow_skin_file() && file_exists($file)) {
                include_once($file);
            }
            //valid
            if(!isset($theme['styles'])) $theme['styles'] = array();
            if(!isset($theme['scripts'])) $theme['scripts'] = array();
            //put stuff from skin
            /*if(count($theme['styles']) || count($theme['scripts'])) {
                $skin->instance->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
            }
            */
            if(isset($theme['filters'])) $skin->instance->do_filters($theme['filters'], $theme);    //do skin filter/action

            //enqueue stuff from skin using new way
            HW_SKIN::enqueue_skin_assets(array_merge(array(
                'skin_file' => $file,
                'theme_settings' => $theme
            ), (array)$skin));
        }
        else {
            get_footer($slug);  //include default footer-$slug.php
        }

        do_action('hw_get_footer'); //modify footer output, you can add close html tag for example to make fit your wp theme
    }

    /**
     * get footer, instead of calling get_header()
     * @param $slug from get_header() param
     */
    public static function get_header($slug) {
        get_header($slug);
        do_action('hw_get_header'); //do before hook `hw_after_header`
    }
    /**
     * return template file match any standard file that refer to files theme
     * @param $header_data: file header data in array, if not given get default any template header file in wordpress
     */
    public static function list_active_theme_templates($header_data = array()){
        if(!empty($header_data)) $template_header_data = $header_data;
        else {
            $template_header_data = array(
                'name'          => 'Template Name',
                'description'   => 'Description',
                'author'        => 'Author',
                'uri'           => 'Author URI',
            );
        }
        $result = array();
        //iterator
        $theme_dir_path = get_stylesheet_directory();   //get current theme directory
        $skins_iterator = new RecursiveDirectoryIterator( $theme_dir_path );
        $RecursiveIterator = new RecursiveIteratorIterator( $skins_iterator );
        //$RecursiveIterator->setMaxDepth(1); //max depth to 1
        foreach ( $RecursiveIterator as $file ) {
            if(basename( $file ) == '.' || basename( $file ) == '..') continue;
            $data = get_file_data($file, $template_header_data);
            if(!$data['name']) continue;
            $data['path'] = (dirname( $file )).DIRECTORY_SEPARATOR.basename($file);
            $data['file'] = basename($file);
            $result[] = $data;
        }

        return $result;
        //other way
        /*$templates = wp_get_theme()->get_page_templates();
        foreach ( $templates as $template_name => $template_filename ) {
            echo "$template_name ($template_filename)<br />";
        }*/
    }

    /**
     * register theme deactivation hook
     * @param $code  Code of the theme. This must match the value you provided in wp_register_theme_activation_hook function as $code
     * @param $function Function to call when theme gets deactivated.
     */
    public static function register_theme_deactivation_hook($code, $function) {
        // store function in code specific global
        $GLOBALS["wp_register_theme_deactivation_hook_function" . $code]=$function;

        // create a runtime function which will delete the option set while activation of this theme and will call deactivation function provided in $function
        $fn=create_function('$theme', ' call_user_func($GLOBALS["wp_register_theme_deactivation_hook_function' . $code . '"]); delete_option("theme_is_activated_' . $code. '");');

        // add above created function to switch_theme action hook. This hook gets called when admin changes the theme.
        // Due to wordpress core implementation this hook can only be received by currently active theme (which is going to be deactivated as admin has chosen another one.
        // Your theme can perceive this hook as a deactivation hook.)
        add_action("switch_theme", $fn);
    }

    /**
     * register theme activation hook
     * @param $code Code of the theme. This can be the base folder of your theme. Eg if your theme is in folder 'mytheme' then code will be 'mytheme'
     * @param $function Function to call when theme gets activated.
     */
    public static function register_theme_activation_hook($code, $function) {
        $optionKey="theme_is_activated_" . $code;
        if(!get_option($optionKey)) {
            call_user_func($function);
            update_option($optionKey , 1);
        }
    }
    /**
     * init
     */
    public static function init() {
        //register template pages
        HW_HOANGWEB::register_class('HW__Template_taxonomy', HW_HOANGWEB_INCLUDES.'/layout-templates/template-category-taxonomy.php','hw-template_taxonomy');
        HW_HOANGWEB::register_class('HW__Template_404', HW_HOANGWEB_INCLUDES.'/layout-templates/template-404.php','hw-template-404');
        HW_HOANGWEB::register_class('HW__Template_page', HW_HOANGWEB_INCLUDES.'/layout-templates/template-page.php','hw-template-page');
        HW_HOANGWEB::register_class('HW__Template_single', HW_HOANGWEB_INCLUDES.'/layout-templates/template-single.php','hw-template-single');
        HW_HOANGWEB::register_class('HW__Template_admin', HW_HOANGWEB_INCLUDES.'/layout-templates/template-admin.php','hw-template-admin');

        //parse config file
        _hw_global('theme_config', self::get_theme_config());
        //$config['sidebars'];

        //determine current template
        _hw_global('current_template',self::get_current_template() );

        //register positions for current theme
        self::register_theme_positions();
    }
    /**
     * setup hooks
     */
    public static  function setup() {
        //init hooks
        add_action('init', array(__CLASS__,'init'), 11); //should be run after 'init' hook from class-tgm-hw-private-plugin-activation.php file
        add_action('wp_enqueue_scripts', array(__CLASS__,'_enqueue_scripts'));
        add_action('wp_head', array(__CLASS__, '_print_header'));
        add_action('wp_footer', array(__CLASS__, '_print_footer'));
        add_action( 'widgets_init', array(__CLASS__, '_widgets_init' ));
        add_action('after_setup_theme', array(__CLASS__, '_after_setup_theme'));
        //add_action('shutdown', array(__CLASS__, '_test'));
    }
    //just test
    static function _test(){
        HW_Logger::log_file('------------');
    }
    /**
     * return current template
     * @return null
     */
    public static function get_current() {
        return self::$current_template;
    }

    /**
     * load theme positions
     */
    protected static function register_theme_positions() {
        $positions = self::get_active_theme_config_data('positions');#__print(self::get_active_theme_config_data());
        if(!empty($positions) && is_array($positions)) {
            foreach($positions as $pos => $text) {
                register_position($pos, $text);
            }
        }
    }

    /**
     * register navmenus
     */
    protected static function register_navmenus() {
        $menus = self::get_active_theme_config_data('menus');
        if(!empty($menus))
        foreach($menus as $menu => $name) {
            // This theme uses wp_nav_menu() in one location.
            register_nav_menu( $menu, __( $name, self::DOMAIN ) );
        }
    }
    /**
     * setup wp theme
     * @hook after_setup_theme
     */
    public static function _after_setup_theme() {
        /*
         * Makes hoangweb available for translation.
         *
         * Translations can be added to the /languages/ directory.
         * If you're building a theme based on hoangweb, use a find and replace
         * to change 'hoangweb' to the name of your theme in all the template files.
         */
        load_theme_textdomain( self::DOMAIN, get_stylesheet_directory() . '/languages' );

        // This theme styles the visual editor with editor-style.css to match the theme style.
        add_editor_style();

        // Adds RSS feed links to <head> for posts and comments.
        add_theme_support( 'automatic-feed-links' );

        // This theme supports a variety of post formats.
        add_theme_support( 'post-formats', array( 'aside', 'image', 'link', 'quote', 'status' ) );
        // This theme uses a custom image size for featured images, displayed on "standard" posts.
        add_theme_support( 'post-thumbnails' );
        //register nav menus
        self::register_navmenus();
    }

    /**
     * Registers our main widget area and the front page widget areas.
     * @hook widgets_init
     */
    public static function _widgets_init() {
        $current = self::get_current_template();
        if(empty($current)) return;
        $config = $current->get_config_data();
        //get sidebars
        $sidebars = array();
        $sidebars = $config->item('sidebars');
        if($sidebars)
        foreach ($sidebars as $sidebar) {
            register_sidebar($sidebar);
        }
    }
    /**
     * enqueue assets for js+css file
     * @hook wp_enqueue_scripts
     */
    public static function _enqueue_scripts() {
        global $wp_styles;
        $current = self::get_current_template(false);
        $config = $current['object']->get_config_data();    #$current['object']
        $active_assets = array();
        $assets = $config->item('assets')? $config->item('assets'): array();//->item('assets');

        /*
         * From WP
         * Adds JavaScript to pages with the comment form to support
         * sites with threaded comments (when in use).
         */
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
            wp_enqueue_script( 'comment-reply' );

        //common assets
        if(!empty($assets['__all__'])) {
            $active_assets = array_merge($active_assets, $assets['__all__']);
        }

        if(!empty($assets[$current['name']])) {  //get assets for current context
            $active_assets = array_merge($active_assets, $assets[$current['name']]);
        }
        foreach($active_assets as $file) {
            //valid file path
            if(!is_dir($file['name']) && !file_exists($file['name'])) {
                $url = get_stylesheet_directory_uri() . '/' .$file['name'];
            }
            else $url = $file['name'];
            if(!HW_URL::valid_url($url)) continue;
            $handle = isset($file['handle'])? $file['handle'] : md5($file['name']);
            //for js
            if($file['type'] == 'js') {

                if(HW_URL::valid_url($url)) {
                    wp_enqueue_script($handle, $url, $file['depends']);
                }
                continue;
            }
            //for stylesheet
            elseif($file['type'] == 'css') {
                wp_enqueue_style(md5($file['name']),  $url, $file['depends']);
                continue;
            }
        }
        if(is_object($current) && method_exists($current, 'enqueue_scripts')) {
            call_user_func(array($current, 'enqueue_scripts')); //addition stuff
        }
        //default js
        if(!is_admin()) HW_Libraries::enqueue_jquery_libs('pageload/nprogress');    //show progressbar while page loading
    }

    /**
     * print some tags in head tag
     * @hook wp_head
     */
    public static function _print_header() {
        $current = self::get_current_template();
        if(is_object($current) && method_exists($current, 'wp_head')) {
            call_user_func(array($current, 'wp_head'));
        }
    }

    /**
     * print something at bottom of page
     * @hook wp_footer
     */
    public static function _print_footer(){
        $current = self::get_current_template();
        if(is_object($current) && method_exists($current, 'wp_footer')) {
            call_user_func(array($current, 'wp_footer'));
        }
        if(!is_admin()) self::init_page_progressbar();
    }
    //nprogress make page smoothly loading
    private static function init_page_progressbar() {
        ?>
        <script>
            // Show the progress bar
            NProgress.start();

            // Increase randomly
            var interval = setInterval(function() { NProgress.inc(); }, 1000);

            // Trigger finish when page fully loaded
            jQuery(window).load(function () {
                clearInterval(interval);
                NProgress.done();
            });

            // Trigger bar when exiting the page
            window.onbeforeunload = function() {
                console.log("triggered");
                NProgress.start();
            };
        </script>
<?php
    }

    /**
     * return config data for current context
     * @param string $item
     * @return array
     */
    public function get_config_data($item='') {
        return $item? ($this->config_data->item($item)? $this->config_data->item($item) : '') : $this->config_data;
    }

    /**
     * parse theme config file
     * @return null|object
     */
    public static function get_theme_config() {
        /*if(empty(self::$current_theme_config) || !HW_TGM_Module_Activation::is_complete_load()) {
            self::$current_theme_config = HW__Template_Configuration::parse_theme_config();
        }
        return self::$current_theme_config ;*/
        return HW__Template_Configuration::parse_theme_config();
    }

    /**
     * valid theme
     * @param $theme_name theme name
     */
    public static function validate_theme($theme_name='') {
        if(!$theme_name) {
            $theme_name = wp_get_theme()->get_template();#get_stylesheet_directory();
        }
        $my_theme = wp_get_theme( $theme_name );
        if ( $my_theme->exists() ) {
            #$my_theme->get( 'Name' );
            #$my_theme->get( 'Version' );
            $config_files = $my_theme->get_files('xml',0, true);
            if(count($config_files) && isset($config_files[self::DEFAULT_THEME_CONFIG_FILE])) {
                return true;
            }
        }
        return false;
    }
}

/**
 * load layouts
 */
HW__Template::setup();

//include_once('template-category.php');
#include_once('template-taxonomy.php');