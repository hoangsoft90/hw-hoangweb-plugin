<?php

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * activation hook
 * require HW_HOANGWEB plugin
 */
//register_activation_hook( __FILE__, 'hw_lct_require_plugins_activate' );
function hw_lct_require_plugins_activate(){
    $message = 'Xin lỗi, yêu cầu cài đặt & kích hoạt plugin "%s". <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>';
    //list required plugin before you can use this plugin correctly
    $required_plugins = array(
        'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
        'hw-skin/hw-skin.php' => 'hw-skin',
        'hw-any-widget-classes/hw-any-widget-classes.php'
    );
    foreach($required_plugins as $plugin_path => $name){
        // Require parent plugin
        if ( ! is_plugin_active( $plugin_path ) and current_user_can( 'activate_plugins' ) ) {
            // Stop activation redirect and show error
            wp_die(sprintf($message,$name));
        }
    }
}

// Register 'List Custom Taxonomy' widget
#add_action( 'widgets_init', 'hwlct_init_lc_taxonomy' );
add_action( 'hw_widgets_init', 'hwlct_init_lc_taxonomy' );
function hwlct_init_lc_taxonomy() {
    return register_widget('HW_LC_Taxonomy');
}

/**
 * Class HW_LC_Taxonomy
 */
class HW_LC_Taxonomy extends WP_Widget {
    /**
     * twig template
     * @var
     */
    private $twig = null;

	/**
     * constructor
     */
	public function HW_LC_Taxonomy() {
		parent::WP_Widget( 'hwlct_taxonomy', $name = 'HW Liệt kê categories/taxonomy terms' );
		//you should check if whether this widget actived on frontend or neither maybe you can get widget data by get_option($this->option_name)
		if(!is_admin() && !is_active_widget( false, false, $this->id_base, true)) return;
        if(!is_admin()) {
            HW_HOANGWEB::load_class('hwArray');
        }

		//init skins
		if(class_exists('HW_SKIN') ){
		    $this->skin = new HW_SKIN($this,plugin_dir_path(__FILE__),'hw_lct_skins','hwlct-skin.php','skins');
		    #$this->skin->set_group('group1');
		    $this->skin->plugin_url = plugins_url('',__FILE__);
		    $this->skin->enable_external_callback = false;
		    #$this->skin->getSavedCallbacksJs_data(array($this,'get_callbacks_data'));

		    #$this->skin->create('t1')->files_skin_folder = 'images';    //set skin folder files
		    #$this->skin->create('t2')->files_skin_folder = 'image1';    //set skin folder files
		    #$this->skin->create('t3','group2','hwlct-pagi-skin.php');    //set skin folder files
		    
		    #$this->skin->get_skin_instance('t1')->getSavedCallbacksJs_data(array($this,'get_callbacks_data'));
            $this->skin->registerExternalStorage_JSCallback(array($this, 'save_callback_event'));
            $this->skin->enable_template_engine(1,0);
		    $this->skin->init();
		}

        if(class_exists('HW_AWC')) {
            /*HW_AWC::register_widget_feature($this,'grid_posts');  //dynamic config
            HW_AWC::register_widget_feature($this, 'fancybox');*/
        }
        $this->setup_actions();
	}

    /**
     * setup actions
     */
    public function setup_actions() {
        //add_filter('wp_list_categories', array($this, '_filter_wp_list_categories'));       //filter output categories
        add_filter('hwlct_wp_list_categories', array($this, '_hwlct_wp_list_categories'), 10, 3);

        //add_filter('widget_categories_args', array($this, '_filter_widget_categories_args'), 10, 2);    //categories query args for wp categories widget but i never use
        add_filter('list_cats', array($this, '_list_cats') ,10,2 );     //filter category name
        add_action('init', array($this, '_init'));  //admin initial
        add_filter('hwlct_wp_list_categories_args', array($this, '_hwlct_wp_list_categories_args'), 10,2);

        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, '_wp_enqueue_scripts'));

        //ajax
        add_action("wp_ajax_hw_change_taxonomy", array(&$this,"_ajax_hw_change_taxonomy"));
    }

    /**
     * @hook wp_enqueue_scripts
     */
    public function _wp_enqueue_scripts() {
        wp_enqueue_script('hwclt', plugins_url('assets/hwlct.js',__FILE__)); //used for frontend
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts() {
        if(is_admin()) {
            $data = array(
                'hw_change_tax_nonce' =>  wp_create_nonce("hw_change_tax_nonce"),
                'ajax_url' => admin_url('admin-ajax.php')
            );
            wp_enqueue_style('hwclt-admin-style', plugins_url('assets/admin-style.css', __FILE__));
            wp_enqueue_script('hwclt-admin',plugins_url('assets/hwlct-admin.js',__FILE__)); //used for admin page
            wp_localize_script('hwclt-admin', '__hwlct_object', $data);
        }
    }
    /**
     * admin init
     * @hook init
     */
    public function _init() {
        if(class_exists('HW_HELP')) {
            HW_HELP::set_helps_path('lct', plugin_dir_path(__FILE__).'helps');
            HW_HELP::set_helps_url('lct', plugins_url('',__FILE__) .'/helps' );
            HW_HELP::register_help('lct');
            HW_HELP::load_module_help('lct');
        }
    }
    /**
     * filter wp_list_categories output (moved to hook 'hwlct_wp_list_categories')
     * @param $output: content of wp_list_categories function
     */
    public function _filter_wp_list_categories($output) {
        $out = '<div id="hw-wp_list_categories">';
        $out .= '<ul>';
        $out .= $output;
        $out .= '</ul>';
        $out .= '</div>';
        return $out;
    }

    /**
     * yet other filter 'wp_list_categories' design for this plugin
     * note: use default output without using skin
     * @param $output
     * @param $args
     * @param $instance
     * @return mixed
     */
    public function _hwlct_wp_list_categories($output, $args, $instance) {
        if(isset($args['mydata']['skin_setting'])) {
            $skin_setting = $args['mydata']['skin_setting'];
            #if(isset($skin_setting['remove_wrap'])) ;
        }
        $options = (object)$args['options'];    //options

        $out = '<div id="hw-wp_list_categories">';
        $class = 'hwlct-'.$args['taxonomy'];
        if(!empty($options->ul_classes)) $class  .= ' '. $options->ul_classes;

        if(isset($args['title_li']) && $args['title_li'] == '') $out .= '<ul class="'.$class.'">';
        $out .= $output;
        if(isset($args['title_li']) && $args['title_li'] == '') $out .= '</ul>';
        $out .= '</div>';
        return $out;
    }
    /**
     * filter args data
     * @param $args
     * @param $instance: widget instance
     * @return mixed
     */
    public function _hwlct_wp_list_categories_args($args, $instance) {
        $skin_setting = isset($instance['skin_settings'])? $instance['skin_settings'] : ''; //get current skin settings

        $skin_setting_file = $this->skin->get_file_skin_setting(empty($instance['skin'])? 'default':$instance['skin']); //current skin setting
        $skin_options = $this->skin->get_file_skin_options();      //current skin options

        if(file_exists($skin_setting_file)) include ($skin_setting_file);
        if(file_exists($skin_options)) include ($skin_options);

        if(isset($theme) && isset($theme['options'])) $default_options = $theme['options'];
        else $default_options = array();

        if(isset($default_options) && isset($theme_options)) {
            $skin_setting = HW_SKIN::merge_skin_options_values($skin_setting, $default_options, $theme_options);
        }
        //but note you give space value to 'title_li' like that 'title_li'=' ', then you will get duplicate ul tag around li
        //if(!empty($skin_setting['remove_wrap']) )
            $args['title_li'] = '';
        //remind options
        $args['options'] = $skin_setting;

        //register twig loader
        if( class_exists('HW_Twig_Template')){
            $this->twig = HW_Twig_Template::create($this->skin->get_file_skin_resource('tpl'));
            #$this->twig = new Twig_Environment($loader);
            $args['twig'] = $this->twig->get();    //reference twig object
        }
        return $args;

    }
    /**
     * hook 'widget_categories_args' for default wp categories widget (no longer use)
     * @param $cat_args
     * @return mixed
     */
    public function _filter_widget_categories_args($cat_args) {
        return $cat_args;
    }

    /**
     * filter category name
     * @param $cat_name: category name
     * @param $category: current category item object
     * @return mixed
     */
    public function _list_cats($cat_name ,$category = '') {
        return $cat_name;
    }
    /**
     * save skin callback change event
     * @param $code
     */
    public function save_callback_event($code){
        $this->update_widgets_ids_with_names($code);
    }

    /**
     * get callback data
     * @return mixed
     */
    public function get_callbacks_data(){
        $widget_options_all = get_option( $this->option_name );
       return  $widget_options_all[ $this->number ]['eventsJS'];
        
    }

    /**
     * ajax handle
     * list terms taxonomy
     * @ajax hw_change_taxonomy
     */
    public function _ajax_hw_change_taxonomy() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_change_tax_nonce")) {
            exit("No naughty business please");
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            HW_HOANGWEB::load_class('HW_POST');
            if(!isset($_REQUEST['_tax'])) $_REQUEST['_tax'] = 'category';   //default tax
            $data =  HW_POST::list_tax_terms($_REQUEST['_tax'] ,array('hide_empty' => 0));

            $result['data'] = $data;
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
    /**
     * @param $str
     */
    private function update_widgets_ids_with_names( $str ) {
    
        /*
         * Get this instances options
         */
        $widget_options_all = get_option( $this->option_name );
        $old_instance = &$widget_options_all[ $this->number ];
    
        $new_instance = &$old_instance;
        if(isset($new_instance['eventsJS']) && $new_instance['eventsJS'] == $str) return;   //old data
        $new_instance['eventsJS'] = $str;#_print($new_instance);
        update_option($this->option_name,$widget_options_all);
      #  $this->update_callback(); // this processes the widget settings prior to the update function, it is needed.
    
       # return $this->update( $new_instance, $old_instance );
        
    }


	/**
	 * This is the Widget
     * @param $args
     * @param $instance
	 */
	public function widget( $args, $instance ) {

		global $post;
		extract($args);

		// Widget options
		$title 	 = apply_filters('widget_title', $instance['title'],$instance, $this->id_base ); // Title
		$this_taxonomy = $instance['taxonomy']; // Taxonomy to show
		$hierarchical = !empty( $instance['hierarchical'] ) ? '1' : '0';
		$showcount = !empty( $instance['count'] ) ? '1' : '0';
        $hide_empty = !empty( $instance['hide_empty'] ) ? '1' : '0';
        $depth = !empty( $instance['depth'] ) ? $instance['depth'] : 0;

        //skin
        $use_skin = !empty( $instance['use_skin'] ) ? 1 : 0;
        $use_skin_options = !empty( $instance['extend_skin_options'] ) ? 1 : 0;
        $skin = isset($instance['skin'])? $instance['skin'] : '';       //saved active hash skin
        //skin settings
        $skin_setting = isset($instance['skin_settings'])? $instance['skin_settings'] : '';
        if($skin) {
            $skin_setting_file = $this->skin->get_file_skin_setting($skin); //current skin setting
            $skin_options = $this->skin->get_file_skin_options();      //current skin options

            if(file_exists($skin_setting_file)) include ($skin_setting_file);
            if(file_exists($skin_options)) include ($skin_options);

            if(isset($theme) && isset($theme['options'])) $default_options = $theme['options'];
            if(isset($default_options) && isset($theme_options)) {
                $skin_setting = HW_SKIN::merge_skin_options_values($skin_setting, $default_options, $theme_options);
            }
        }

        //widget feature field: grid_posts
        $grid_posts = HW_AWC::get_widget_feature($this, 'grid_posts');
        $awc_enable_grid_posts = false;  //disable by default

        if($grid_posts && HW_AWC::check_widget_feature($this, 'grid_posts') ) {
            $awc_enable_grid_posts = $grid_posts->is_active($instance);
            if($awc_enable_grid_posts) $awc_grid_posts_cols = $grid_posts->get_field_value('awc_grid_posts_cols');
        }


        $show_option_none = !empty( $instance['show_option_none'] ) ? $instance['show_option_none'] : '';
          
		if( array_key_exists('orderby',$instance) ){
			$orderby = $instance['orderby'];
		}
		else{
			$orderby = 'count';
		}
		if( array_key_exists('ascdsc',$instance) ){
			$ascdsc = $instance['ascdsc'];
		}
		else{
			$ascdsc = 'desc';
		}
		if( array_key_exists('exclude',$instance) ){
			$exclude = join(',', (array)$instance['exclude']);
		}
		else {
			$exclude = '';
		}

		if( array_key_exists('dropdown',$instance) ){
			$dropdown = $instance['dropdown'];
		}
		else {
			$dropdown = false;
		}
        #child of arg
        if( array_key_exists('childof',$instance) ){
            $childof = $instance['childof'];
        }
        else {
            $childof = '';
        }
        //get current category/taxonomy
        if(isset($instance['childof_current_term']) && $instance['childof_current_term'] && (is_category() || is_tax())){
            $obj = get_queried_object();    //get current page data
            $childof = $obj->term_id;   //get current term in template
        }
        $show_subcat_by_parent = isset($instance['show_subcat_by_parent'])? (bool) $instance['show_subcat_by_parent'] : false;
        $categories_by_current_post = isset($instance['categories_by_current_post'])? (bool) $instance['categories_by_current_post'] : false;

        $args['skin'] = $skin;
        $args['skin_setting'] = $skin_setting;

        $data = array(
            'show_subcat_by_parent' => $show_subcat_by_parent,
            'categories_by_current_post' => $categories_by_current_post,
            'skin' => $skin,
            'skin_setting' => $skin_setting
        );
        // Output
		$tax = $this_taxonomy;
        if($dropdown){
            $taxonomy_object = get_taxonomy( $tax );
            $args = array(
                'show_option_all'    => false,
                'show_option_none'   => '',
                'orderby'            => $orderby,
                'order'              => $ascdsc,
                'show_count'         => $showcount,
                'hide_empty'         => $hide_empty,
                'child_of'           => $childof,
                'exclude'            => $exclude,
                'echo'               => 0,
                //'selected'           => 0,
                'hierarchical'       => $hierarchical,
                'name'               => $taxonomy_object->query_var,
                'id'                 => 'hwlct-widget-'.$tax,
                //'class'              => 'postform',
                'depth'              => $depth,
                //'tab_index'          => 0,
                'taxonomy'           => $tax,
                'hide_if_empty'      => (bool)$hide_empty,
                'walker'			=> new hwlctwidget_Taxonomy_Dropdown_Walker(),
                'mydata' => $data
            );

        }
        else {
            $args = array(
                'show_option_all'    => false,
                'orderby'            => $orderby,
                'order'              => $ascdsc,
                'style'              => 'list',
                'show_count'         => $showcount,
                'hide_empty'         => $hide_empty,
                'use_desc_for_title' => 1,
                'child_of'           => $childof,
                //'feed'               => '',
                //'feed_type'          => '',
                //'feed_image'         => '',
                'exclude'            => $exclude,
                //'exclude_tree'       => '',
                //'include'            => '',
                'hierarchical'       => $hierarchical,
                'title_li'           => '',
                'show_option_none'   => apply_filters('hwlct_show_option_none', $show_option_none),
                'number'             => null,
                'echo'               => 0,
                'depth'              => $depth,
                //'current_category'   => 0,
                //'pad_counts'         => 0,
                'taxonomy'           => $tax,
                'mydata' => $data
                //'walker'             => new hwlctwidget_Taxonomy_Walker($args)
            );
            $args['walker'] = new hwlctwidget_Taxonomy_Walker($args);
        }
        $args = apply_filters('hwlct_wp_list_categories_args', $args, $instance);   //filter args

        if($use_skin && $this->skin){
            //load skin
            $skin = $this->skin;
            //$file = $this->skin->get_skin_instance('x')->get_skin_link(empty($instance['skin1'])? 'default':$instance['skin1']);
            $file = $this->skin->get_skin_file(empty($instance['skin'])? 'default':$instance['skin']);
            if(file_exists($file)){
                HW_POST::reset_item_counter();

                $terms_data = get_categories($args);    //get terms data
                foreach($terms_data as $id => &$term) { //wrap with timber
                    $term = new HW_TimberTerm($term->term_id, $term->taxonomy);
                    //class
                    $classes= array('item-box');
                    if($awc_enable_grid_posts && isset($awc_grid_posts_cols) && class_exists('HW_POST')) {
                        $classes = HW_POST::get_item_class($awc_grid_posts_cols,$classes);
                    }
                    if(class_exists('HW_POST')) $term->add_class( HW_POST::item_class($classes, false));
                    //term image custom field
                    /*if(function_exists('get_field')) $image = get_field('image',$term);
                    else $image = '';
                    if(!$image) $image = HW_SKIN::current()->get_skin_url('images/placeholder.png');*/
                }
                //skin template
                $content = $this->skin->render_skin_template(compact('terms_data', 'tax'),false);
                if($content!==false) echo $content;

                //init theme setting
                $theme['styles'] = array();   //declare stylesheets file
                $theme['scripts'] = array();  //declare js files
                if($this->skin->allow_skin_file()) include($file);

                //enqueue stuff from skin
                HW_SKIN::enqueue_skin_assets(array(
                    'instance' => $this->skin,
                    'hash_skin' => $instance['skin'],
                    'skin_file' => $file,
                    'skin_settings' => $theme,
                    'skin_options' => $skin_setting
                ));
            }
        }
        else {
            echo $before_widget;
            if ( $title ) echo $before_title . $title . $after_title;
            if($dropdown) {
                echo '<form action="'. get_bloginfo('url'). '" method="get">';
                echo apply_filters('hwlct_wp_dropdown_categories', wp_dropdown_categories($args), $args, $instance);
                echo '<input type="submit" value="go &raquo;" /></form>';

            }
            else {
                echo apply_filters('hwlct_wp_list_categories', wp_list_categories($args), $args, $instance);
            }
            echo $after_widget;
        }
        if($use_skin && !class_exists('HW_SKIN')) echo 'HW_SKIN class không tìm thấy.'; //notice

	}
	/**
     * Widget control update
     * @param $new_instance
     * @param $old_instance
     */
	function update( $new_instance, $old_instance ) {
		$instance    = $new_instance;
		
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );
		$instance['orderby'] = $new_instance['orderby'];
		$instance['ascdsc'] = $new_instance['ascdsc'];
		$instance['exclude'] = $new_instance['exclude'];
		
		$instance['childof'] = $new_instance['childof'];
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
        $instance['count'] = !empty($new_instance['count']) ? 1 : 0;
        $instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
        $instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
        $instance['childof_current_term'] = !empty($new_instance['childof_current_term']) ? 1 : 0;
        $instance['show_subcat_by_parent'] = !empty($new_instance['show_subcat_by_parent']) ? 1 : 0;

        $instance['categories_by_current_post'] = !empty($new_instance['categories_by_current_post']) ? 1 : 0;

        $instance['hide_empty'] = !empty($new_instance['hide_empty']) ? 1 : 0;
        $instance['depth'] = !empty($new_instance['depth']) ? $new_instance['depth'] : 0;
        $instance['show_option_none'] = !empty($new_instance['show_option_none']) ? $new_instance['show_option_none'] : '';

        $instance['skin'] = $new_instance['skin'];  //save skin
        $instance['use_skin'] = !empty($new_instance['use_skin'])? 1:0;  //save skin
        $instance['extend_skin_options'] = !empty($new_instance['extend_skin_options'])? 1:0;  //skin options
        //$instance['skin1'] = $new_instance['skin1'];  //save skin
        //$instance['skin2'] = $new_instance['skin2'];  //save skin
        
        $instance['eventsJS'] = $new_instance['eventsJS'];
        //save current skin to db for this widget
        $this->skin->save_skin_assets(array(
            'skin' => array(
                'hash_skin' => $instance['skin'],
                'hwskin_config' => $this->skin->get_config(),
                'hwskin_condition' => $instance['skin_condition'],
                'skin_options' => $instance['skin_settings']
            ),
            'status' => $instance['use_skin']
        ));

		return $instance;
	}
	
	/**
	 * Widget settings
     * @param $instance
	 */
	function form( $instance ) {
	    
	    //$this->skin->saveCallbackJs4SkinChangeEvent('console.log(skin);');
	    //$this->skin->saveCallbackJs4SkinChangeEvent('console.log("jkehrerdgb");');
		//for showing/hiding advanced options; wordpress moves this script to where it needs to go
        wp_enqueue_script('jquery');

        //create feature tog
        if(class_exists('HW_ButtonToggle_widget')) {
            $btn_tog = new HW_ButtonToggle_widget($this,$instance);
        }
        ?>

        <?php
        $terms_data = array();
      // instance exist? if not set defaults
        if ( $instance ) {
            $title  = $instance['title'];
            $this_taxonomy = $instance['taxonomy'];
            $orderby = $instance['orderby'];
            $ascdsc = $instance['ascdsc'];
            $exclude = $instance['exclude'];

            $childof = $instance['childof'];

            $showcount = isset($instance['count']) ? (bool) $instance['count'] :false;
            $hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
            $dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
            $childof_current_term = isset($instance['childof_current_term'])? (bool) $instance['childof_current_term'] : false;
            $show_subcat_by_parent = isset($instance['show_subcat_by_parent'])? (bool) $instance['show_subcat_by_parent'] : false;
            $categories_by_current_post = isset($instance['categories_by_current_post'])? (bool) $instance['categories_by_current_post'] : false;
            $hide_empty = isset($instance['hide_empty'])? (bool)$instance['hide_empty'] : false;
            $depth = isset($instance['depth'])? $instance['depth'] : 0;

            $use_skin = isset($instance['use_skin'])? (bool)$instance['use_skin'] : false;
            $extend_skin_options = isset($instance['extend_skin_options'])? (bool)$instance['extend_skin_options'] : false;
            $skin = isset($instance['skin'])? $instance['skin'] : '';       //saved active hash skin
            //total skin setting
            $skin_setting = isset($instance['skin_settings'])? $instance['skin_settings'] : '';
            $skin_condition = isset($instance['skin_condition'])? $instance['skin_condition'] : '';


            $show_option_none = isset($instance['show_option_none'])? $instance['show_option_none'] : '';
            //get terms by select tax
            if(!empty($this_taxonomy)) {
                $terms_data = HW_POST::list_tax_terms($this_taxonomy);
            }

        } else {
            //These are our defaults
            $title  = '';
            $orderby  = 'count';
            $ascdsc  = 'desc';
            $exclude  = '';
            $childof  = '';
            $this_taxonomy = 'category';//this will display the category taxonomy, which is used for normal, built-in posts
            $hierarchical = true;
            $showcount = true;
            $dropdown = false;
            $childof_current_term = false;
            $show_subcat_by_parent = false;
            $categories_by_current_post = false;
            $hide_empty = false;    //default hide if empty
            $depth = 0;    //unlimit nested level

            $use_skin = false;
            $skin = '';
            $skin_setting = '';
            $skin_condition = '';
            $extend_skin_options = false;

            $show_option_none = '';
        }

		// The widget form
        $terms_holder_id= 'holder-' . $this->get_field_id('terms');
        $exclude_terms_holder_id = 'holder-' . $this->get_field_id('exclude_terms');
        ?>
		
		
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Tiêu đề:' ); ?></label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php echo __( 'Chọn Taxonomy:' ); ?></label>
				<select name="<?php echo $this->get_field_name('taxonomy'); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>" class="widefat" style="height: auto;" size="4" onchange="__hwlct_object.change_taxonomy(this, '#<?php echo $terms_holder_id?>', 'hwlct_change_taxonomy_cbs','<?php //echo $link?>')">
			<?php 
			$args=array(
			  'public'   => true,
			  '_builtin' => false //these are manually added to the array later
			); 
			$output = 'names'; // or objects
			$operator = 'and'; // 'and' or 'or'
			$taxonomies=get_taxonomies($args,$output,$operator); 
			$taxonomies[] = 'category';
			$taxonomies[] = 'post_tag';
			$taxonomies[] = 'post_format';
			foreach ($taxonomies as $taxonomy ) { 
			?>
				<option value="<?php echo $taxonomy; ?>" <?php if( $taxonomy == $this_taxonomy ) { echo 'selected="selected"'; } ?>><?php echo $taxonomy;?></option>
			<?php }	?>
			</select>
			</p>
			<?php if(class_exists('HW_SKIN')){			
			    ?>
			    <p><?php 
			    //echo $this->skin->get_skins_select_tag('skin');
			    //$this->skin->get_skins_listview('skin1',$instance['skin1']);
                //$this->skin->get_skin_instance('t1')->get_skins_select_tag('skin2',null,array('class'=>'widefat'),HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_LINKS);
			    
			    ?></p>
            <p>
                <input type="checkbox" name="<?php echo $this->get_field_name('use_skin')?>" id="<?php echo $this->get_field_id('use_skin')?>" <?php checked($use_skin)?>/>
                <label for="<?php echo $this->get_field_id('use_skin')?>"><?php _e('Kích hoạt Giao diện')?></label>

            </p>
            <p>
                <input type="checkbox" name="<?php echo $this->get_field_name('extend_skin_options')?>" id="<?php echo $this->get_field_id('extend_skin_options')?>" <?php selected($extend_skin_options? 1 : 0)?> />
                <label for="<?php echo $this->get_field_id('extend_skin_options')?>"><?php _e('Cho phép sử dụng skin options')?></label>
                <br/>
                <span><em>Cho phép sử dụng skin options mặc dù không kích hoạt sử dụng giao diện riêng.</em></span>
            </p>
			<p>
			     <label for="<?php echo $this->get_field_id('skin')?>"><strong><?php _e('Giao diện')?></strong></label>
				  <?php echo $this->skin->get_skins_select_tag('skin',null, array('class'=>'widefat'),false);
				  #$this->skin->addCallbackSkinChangeEvent('console.log(3);');
			    ?>
                <!-- show skin options,skin condition field if exists -->
                <?php
                echo $this->skin->get_skin_template_condition_selector('skin_condition', $skin_condition);
                echo $this->skin->prepare_skin_options_fields('skin_settings', $skin_setting, $skin);

                /*echo $this->skin->create_total_skin_selector('skin', array('hash_skin' => $skin, 'skin_settings' => $skin_setting), null,array(
                    'show_main_skin' =>0,
                    'show_condition_field'=>1,
                    'show_skin_options' => 1
                ));*/
                ?>
			</p>
			
			<?php }?>
			<?php if(isset($btn_tog)) $btn_tog->set_button_toggle_start_wrapper('Nâng cao...');?>
				
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $showcount ); ?> />
				<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Hiển thị số lượng posts bên cạnh' ); ?></label><br />
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
				<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Hiển thị đa tầng' ); ?></label><br/>

        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
        <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Hiển thị dạng Dropdown' ); ?></label><br/>

        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" <?php checked( $hide_empty ); ?> />
        <label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e( 'Ẩn mục không có posts' ); ?></label><br/>

        <input type="text" class="text" size="5" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>" value="<?php echo isset($instance['depth'])? $instance['depth'] : '0'?>" />
        <label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e( 'depth' ); ?></label><br/>
        <span><em>=1: Nếu chỉ muốn hiện tất cả những danh mục chính.</em></span><br/>
        <span><em>Yêu cầu: bật tùy chọn "Hiển thị đa tầng".</em></span>

				<p>
					<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php echo __( 'Xắp xếp:' ); ?></label>
					<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat" >
						<option value="ID" <?php if( $orderby == 'ID' ) { echo 'selected="selected"'; } ?>>ID</option>
						<option value="name" <?php if( $orderby == 'name' ) { echo 'selected="selected"'; } ?>>Name</option>
						<option value="slug" <?php if( $orderby == 'slug' ) { echo 'selected="selected"'; } ?>>Slug</option>
						<option value="count" <?php if( $orderby == 'count' ) { echo 'selected="selected"'; } ?>>Count</option>
						<option value="term_group" <?php if( $orderby == 'term_group' ) { echo 'selected="selected"'; } ?>>Term Group</option>
					</select>
				</p>
				<p>
					<label><input type="radio" name="<?php echo $this->get_field_name('ascdsc'); ?>" value="asc" <?php if( $ascdsc == 'asc' ) { echo 'checked'; } ?>/> Tăng dần</label><br/>
					<label><input type="radio" name="<?php echo $this->get_field_name('ascdsc'); ?>" value="desc" <?php if( $ascdsc == 'desc' ) { echo 'checked'; } ?>/> Giảm dần</label>
				</p>
				<p id="<?php echo $exclude_terms_holder_id?>">
					<label for="<?php echo $this->get_field_id('exclude'); ?>">Loại trừ ID (danh sách IDs ngăn cách dấu phẩy)</label><br/>
					<select multiple class="widefat" name="<?php echo $this->get_field_name('exclude'); ?>[]" >
                        <?php
                        if(!empty($terms_data)) {
                            foreach($terms_data as $id => $name) {
                                $selected = in_array($id, $exclude)? 'selected="selected"' : '';
                                printf('<option %s value="%s">%s</option>', $selected, $id, $name);
                            }
                        }
                        ?>
					</select>
				</p>
        <hr/>
				<p id="<?php echo $terms_holder_id?>">
					<label for="<?php echo $this->get_field_id('childof'); ?>">Chỉ hiển thị sub-category của category mẹ (category id)</label><br/>
                    <?php //echo $childof; ?>
					<select class="widefat" data-id="<?php echo $this->number?>" name="<?php echo $this->get_field_name('childof'); ?>" id="<?php echo $this->get_field_id('childof')?>">
                        <option value="">---- Chọn ----</option>
                        <?php
                        if(!empty($terms_data)) {
                            foreach($terms_data as $id => $name) {
                                printf('<option %s value="%s">%s</option>', selected($id, $childof,false), $id, $name);
                            }
                        }
                        ?>
					</select>

				</p>
                <p>
                    <input type="checkbox" name="<?php echo $this->get_field_name('childof_current_term')?>" id="<?php echo $this->get_field_id('childof_current_term')?>" <?php checked($childof_current_term? 1:0)?>/>
                    <label for="<?php echo $this->get_field_id('childof_current_term'); ?>"><?php _e('Lấy category mẹ hiện tại(category id)')?></label><br/>
                    <span><em>Chỉ hiển thị sub-category của category hiện tại.</em></span>
                </p>
        <hr/>
                <p>
                    <input type="checkbox" name="<?php echo $this->get_field_name('show_subcat_by_parent')?>" id="<?php echo $this->get_field_id('show_subcat_by_parent')?>" <?php checked($show_subcat_by_parent? 1 : 0)?>/>
                    <label for="<?php echo $this->get_field_id('show_subcat_by_parent')?>" style="text-decoration: line-through;"><?php _e('Hiển thị sub-category khi vào trang mẹ')?></label><br/>

                    <span><em>Hiển thị sub-category con của trang category mẹ nhưng vẫn dữ toàn bộ danh mục</em></span>
                </p>
        <hr/>
                <p>
                    <label for="<?php echo $this->get_field_id('show_option_none'); ?>"><strong><?php _e('Nội dung không có dữ liệu')?></strong></label><br/>
                    <input type="text" name="<?php echo $this->get_field_name('show_option_none')?>" id="<?php echo $this->get_field_id('show_option_none')?>" value="<?php echo $show_option_none;?>"/>

                </p>
            <p>
                <input type="checkbox" name="<?php echo $this->get_field_name('categories_by_current_post')?>" id="<?php echo $this->get_field_id('categories_by_current_post')?>" <?php checked($categories_by_current_post? 1 : 0)?>/>
                <label for="<?php echo $this->get_field_id('categories_by_current_post')?>"><?php _e('Hiển thị cho post hiện tại')?></label><br/>
                <span><em>Hiển thị tất cả những taxonomies, taxonomies nào thuộc về post hiện tại.</em></span>
            </p>

			<script>
                /**
                 * hwlct_change_taxonomy_cbs callbacks
                 */
                if(typeof __hwlct_object.add_callback == 'function')
                __hwlct_object.add_callback('<?php echo $this->number?>', 'hwlct_change_taxonomy_cbs', {
                    before_ajax : function() {
                        var select_tag = jQuery('#<?php echo $this->get_field_id('childof')?>:eq(0)'),
                            exclude_select_tag = jQuery('#<?php echo $this->get_field_id('exclude')?>:eq(0)');

                        select_tag.html(' ').append(jQuery('<option>', {value:'',text : 'Loading..'}));
                        exclude_select_tag.html(' ').append(jQuery('<option>', {value:'',text : 'Loading..'}));
                    },
                    after_ajax : function(data) {
                        var select_tag = jQuery('#<?php echo $this->get_field_id('childof')?>:eq(0)'),
                            exclude_select_tag = jQuery('#<?php echo $this->get_field_id('exclude')?>:eq(0)');

                        jQuery(select_tag).add(exclude_select_tag).html(' ').append(jQuery('<option>', {
                            value: "",
                            text: "------Select------"  //.data[value]
                        }));

                        if(data.posts)
                            jQuery.each(data.terms, function(value, text) {
                                jQuery(select_tag).add(exclude_select_tag).append(jQuery('<option>', {
                                    value: value,
                                    text: text  //.data[value]
                                }));
                            });

                    }
                });
			</script>
			<?php if(isset($btn_tog)) $btn_tog->set_button_toggle_end_wrapper(); //close feature tog ?>
<?php 
	}

} // class lc_taxonomy
include_once ('includes/functions.php');
include_once('includes/hwlct_taxonomies_walker.php');
?>