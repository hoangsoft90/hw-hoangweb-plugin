<?php 

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HWTPL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HWTPL_PLUGIN_URL', plugins_url('',__FILE__));
define('HWTPL_PLUGIN_FILE', __FILE__);

//require HW_HOANGWEB plugin
register_activation_hook( HWTPL_PLUGIN_FILE, 'hwtpl_require_plugins_activate' );
function hwtpl_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',
            'hw-skin/hw-skin.php' => 'hw-skin'
        ));
    }
    else{
        wp_die('Bạn cần kích hoạt plugin hw-hoangweb');
    }
}

require_once( dirname(__FILE__)."/includes/hw_cplw_ajax_functions.php" );

include_once("includes/functions.php");
include_once("includes/hwtpl-setup.php");
include_once("includes/shortcode.php");

#if(!class_exists('HW_SKIN')) include(WP_PLUGIN_DIR.'/hw-skin.php');	#use hw-skin plugin instead.
/**
 * @Class HW_Taxonomy_Post_List_widget
 */
class HW_Taxonomy_Post_List_widget extends WP_Widget
{
	private static $instance;
    /**
     * instance of HW_Taxonomy_Post_List
     * @var null
     */
    private $hwtpl = null;
    /**
     * hw skins folder
     */
    const SKINS_FOLDER = 'hw_tpl_skins';    #'wcp_hw_skins';

    /**
     * secret key to encrypt & decrypt
     */
    const ENCRYPTION_KEY = 'd0a7e7997b6d5fcd55f4b5c32611b87cd923e88837b63bf2941ef819dc8ca282';
    /**
     * manage current widget data
     * @var array
     */
    private $hw_widget = array();
    /**
     * stored widget instance options
     * @var array
     */
    private  $options = array();
    /**
     * shared data
     * @var array
     */
    public $shared = array();
    /**
     * carousel type
     * @var array
     */
    static private $scroll_types = array(
        'smoothdivscroll' => 'Cuộn liên tục',
        'jcarousellite' => 'Cuộn ngắt quãng',
    );
    /**
     * scroll direction
     * @var array
     */
    static private $scrolldirections = array(
        'smoothdivscroll' => array(
            'endlesslooptop' => 'cuộn liên tục xuống dưới',
            'endlessloopbottom' => 'cuộn liên tục lên trên',
            'right' => 'cuộn sang phải và dừng',
            'left' => 'cuộn sang trái và dừng',
            'backAndForth' => 'cuộn đảo qua lại',
            'endlessLoopRight' => 'cuộn liên tục sang phải',
            'endlessLoopLeft' => 'cuộn liên tục sang trái'
        ),
        'jcarousellite' => array(
            'vertical' => 'Cuộn dọc',
            'horizontal' => 'Cuộn ngang'
        )

    );

    /**
     * constructor
     */
    function HW_Taxonomy_Post_List_widget() {
		//Hoangweb Taxonomy Post List
		parent::WP_Widget(false,$name = "Truy xuất nội dung theo chuyên mục",array('description'=>'Hiển thị bài viết theo danh mục/taxonomy'));
		//you should check if whether this widget actived on frontend or neither maybe you can get widget data by get_option($this->option_name)
		if(!is_admin() && !is_active_widget( false, false, $this->id_base, true)) return;
		
		$this->setup_actions();
		
		//instance skin
		  if(class_exists('HW_SKIN')){
			   $this->skin = new HW_SKIN($this, plugin_dir_path(__FILE__),self::SKINS_FOLDER,'hw-category-posts.php','skins');
			   //$this->skin->skin_name='hw-category-posts.php';   #/wp-contents/hw_yahooskype/yahooskype.php
			   //$this->skin->skin_folder='wcp_hw_skins';   #create folder in /wp-contents/hw_yahooskype
			   $this->skin->plugin_url = plugins_url('',__FILE__);
			   $this->skin->enable_external_callback = false;
			   $this->skin->create('pagination','__paginations','hwtpl-pagination.php')->enable_external_callback = false;  //create new skin for pagination
              //create scrollbar skins
              $this->skin->create('scrollbar','__scrollbars','hwtpl-scrollbar.php')->enable_external_callback = false;
              $this->skin->enable_template_engine();
		  }
		  self::$instance = $this;    //save lastest instance of this widget
        //register widget features from HW_AWC
        if(class_exists('HW_AWC')) {
            /*HW_AWC::register_widget_feature($this,'grid_posts');  //depricated
            HW_AWC::register_widget_feature($this, 'saveconfig');
            HW_AWC::register_widget_feature($this, 'fancybox');*/
        }
        $this->hwtpl = new HW_Taxonomy_Post_List();
        $this->hwtpl->localize_object_callback = array($this, '_set_localize_scripts_data');
	}

    /**
     * callback for localizing objects data
     * @param $data
     * @return array
     */
    public function _set_localize_scripts_data($data) {
        $obj = array(
            'hw_change_pt_taxes_nonce' =>  wp_create_nonce("hw_change_posttype_taxonomies_nonce"),
            'hw_change_terms_tax_nonce' => wp_create_nonce("hw_change_terms_taxonomy_nonce"),
            'hw_query_posts_nonce' => wp_create_nonce("hw_query_posts_nonce"),
            'widget_id_base' => $this->id_base,

        );
        return array_merge($obj, $data);
    }
    /**
     * setup actions
     */
    private function setup_actions(){
        //pagination ajax
        add_action("wp_ajax_htpl_ajax", array(&$this,"_hw_load_htpl_ajax"));
        add_action("wp_ajax_nopriv_htpl_ajax", array(&$this,"_hw_load_htpl_ajax")); //also for frontend

        //get custom fields associate with post type using ajax
        add_action("wp_ajax_load_customfields_from_pt", array(&$this,"_hw_load_customfields_from_pt")); //only for admin page

        add_action("wp_ajax_hw_change_terms_taxonomy", array(&$this,"_hw_change_terms_taxonomy"));
        add_action("wp_ajax_hw_change_posttype_taxonomies", array(&$this,"_hw_change_taxonomies_posttype"));

        add_action("wp_ajax_hw_query_posts", array(&$this , "_hw_query_posts"));

        //redirect
        add_action( 'template_redirect', array(&$this,'_my_taxonomy_template_redirect' ));

        //post content
        add_filter('the_title', array($this,'_modify_post_title'));
    }
	/**
	 * return current one instance of widget
	 */
	static function get_instance(){
		return self::$instance;
	}
	/**
	* get widget data
	* @param array $instance: pass reference widget instance data in array
	*/
	function get_widget_instance(&$instance= null){
		if(is_array($instance)) {
			$this->options = &$instance;
			$this->options['widget_id'] = $this->number;	//assign widget id to instance
		}
        //$this->shared['instance']   //warning: don't get it by shared data

		//get current widget instance
		if(!isset($this->options) || !is_array($this->options) || count($this->options) == 0){
			$options = get_option($this->option_name);
			$this->options = $options[$this->number];
			$this->options['widget_id'] = $this->number;	//assign widget id to instance
		}
		
		if(!isset($this->hw_widget['instance']) ) $this->hw_widget['instance'] = $this->options;
		return $this->options;
	}

    /**
     * limit string by characters
     * @param string $str: input string
     * @param int $limit: number of characters allow (default: 100) 
     */
    static function limit_str($str,$limit=100){
        if(strlen($str)<=$limit) return $str;
        return mb_substr($str,0,$limit-3,'UTF-8').'...';
    }

    /**
     * add to data sharing
     * @param $name
     * @param $var
     * @param string $value
     */
    private function share_data($name, $var, $value = '') {
        if(!isset($this->share_data[$name])) $this->share_data[$name] = array();    //init
        if(is_array($var)) {
            $this->share_data[$name] = array_merge($this->share_data[$name], $var);
        }
        elseif(is_string($var)) $this->share_data[$name][$var] = $value;
    }

    /**
     * get shared data
     * @param $name
     * @param $var
     */
    private function get_share_data($name, $var = '') {
        if(!empty($var)  ) {
            return isset($this->share_data[$name][$var])? $this->share_data[$name][$var]: '';
        }
        else {
            return isset($this->share_data[$name])? $this->share_data[$name] : array();
        }
    }
    /**
     * limit post title length
     * @param string $title: current post title
     */
    public function _modify_post_title($title){
        $instance = $this->get_widget_instance();
        if(isset($instance['posttype']) && in_array(get_post_type(),explode(',',$instance['posttype']))){
            if(isset($instance['post_title_leng']) && $instance['post_title_leng']) {
                return self::limit_str($title,$instance['post_title_leng']);
            }
        }
        return $title;
    }
    /**
     * custom post excerpt
     * @param number $post_id
     * @param number $length
     * @return Ambigous <string, string, mixed>
     */
    public function the_excerpt( $post_id = 0, $length = 0 ) {
        $instance = $this->get_widget_instance();
        
        $length = $instance['excerpt_length'];
        
        if ( $post_id == 0 ) $post_id = get_the_ID();
        $post = get_post( $post_id );
        $text = $post->post_excerpt;
        $text = apply_filters( 'the_excerpt', $text );
        $see_points = false;
        if ( strlen( $text ) == 0 ) {
            $text = get_the_content( $post_id );
            $text = strip_tags( $text, '<p><br><style>' ); // use ' $text = strip_tags($text,'&lt;p&gt;&lt;a&gt;'); ' if you want to keep some tags
            $see_points = true;
        }
        $text = strip_shortcodes( $text ); // optional, recommended
    
        //     if ( $length > 0 ) $text = substr( $text, 0, $length );
        if ( $length > 0 ) {
            $initial_length = strlen( $text );
            $text = explode( ' ', $text );
            array_splice( $text, $length );
            $text = implode( ' ', $text );
            $see_points = $initial_length > strlen( $text );
        }
        if ( $see_points && strlen( $text ) ) $text .= sprintf( ' <a href="%s">[...]</a>', get_permalink( $post_id ) );
        return $text;
    }
    
    /**
     * change taxonomy event
     */
	public function _hw_change_terms_taxonomy(){
	    if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_change_terms_taxonomy_nonce")) {
		  exit("No naughty business please");
	    }

	   if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
           $out=  hw_get_terms_list($_REQUEST['tax'],false, array(
                    'name' => hwtpl_mc_decrypt($_REQUEST['name'],self::ENCRYPTION_KEY),
                    'id' => hwtpl_mc_decrypt($_REQUEST['id'],self::ENCRYPTION_KEY)
               ));

           $result['html'] = isset($out)? $out :'';
		  $result = json_encode($result);
		  echo $result;
	   }
	   else {
		  header("Location: ".$_SERVER["HTTP_REFERER"]);
	   }
	 
	   die();
	}

    /**
     * ajax callback to get taxonomies for specific post type
     */
    public function _hw_change_taxonomies_posttype() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_change_posttype_taxonomies_nonce")) {
            exit("No naughty business please");
        }
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $post_types = $_REQUEST['posttype'];
            #if(is_string($post_types)) $post_types = preg_split('#[\s,]+#',$post_types);

            HW_HOANGWEB::load_class('HW_POST');

            $data = HW_POST::get_posttypes_taxonomies($post_types); //get all taxonomies assigned to post types
            //get all posts by post types
            $posts_result = HW_POST::get_all_posts_by_posttypes($post_types);

            //$html = '<select';
            $result['data'] = $data;
            $result['posts'] = $posts_result;
            $result = json_encode($result);
            echo $result;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

    /**
     * ajax handle for quering posts
     */
    public function _hw_query_posts() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_query_posts_nonce")) {
            exit("hacked!");
        }
        //valid
        if(!isset($_REQUEST['widget_id_base']) || !isset($_REQUEST['widget_id'])) return;
        //get params
        $id_base = isset($_REQUEST['widget_id_base'])? $_REQUEST['widget_id_base'] : '';
        $id = isset($_REQUEST['widget_id'])? $_REQUEST['widget_id'] : '';

        //data
        $args = array(
            'showposts' => '-1',
        );

        if(!empty( $_REQUEST['widget-'. $id_base][$id]) ) {
            $data = $_REQUEST['widget-'. $id_base][$id];     //query posts data

            if(isset($data['posttype'])) $args['posttype'] = join(',', $data['posttype']); //post types
            if(isset($data['tax'])) $args['taxonomy'] = $data['tax'];       //filter by taxonomy
            if(isset($data['cat_']) && isset($args['taxonomy'])) {  //filter by taxonomy
                $args['tax_query'] = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => $args['taxonomy'],
                        'field' => 'slug',
                        'terms' => array($data['cat_']),
                        'operator' => 'IN'
                    )
                );
            }
            //filter by author
            if(isset($data['author'])) {
                if(is_numeric($data['author']) && $data['author'] != '-1') {
                    $args['author'] = $data['author'];
                }
                elseif($data['author'] == 'logined_user' && is_user_logged_in()) {
                    $args['author'] = get_current_user_id();
                }

            }

        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = array();
            $data = array();

            //query posts
            $query = new WP_Query($args);
            while($query->have_posts()) {
                $query->the_post();
                //get post title if the plugin qtranslate is installed
                if(function_exists('qtrans_use')) {
                    if(!isset($currentLang)) $currentLang = qtrans_getLanguage();
                    $title = qtrans_use($currentLang, get_the_title(), false);
                }
                else $title = get_the_title();

                $data[get_the_ID()] = $title;
            }
            wp_reset_query();
            $result['posts'] = $data;
            $result['args'] = $args;
            echo json_encode($result);
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
    /**
     * load custom fields associate with post type
     */
    public function _hw_load_customfields_from_pt(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "load_customfields_from_pt_nonce")) {
            exit("unauthorize");
        }
        global $wpdb, $wp_registered_widgets;
        $customfields = array();    //all custom fields get from post type
        $sql = array();  //prepare sql command
        $pt = explode(',',$_GET['pt']);
        /**
         * get widget object by id
         */
        $widget_id = $_GET['widget'];   //get widget id
        $widget_obj = $wp_registered_widgets[$widget_id];
        $widget_obj = $widget_obj['callback'][0]; //widget object instance

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            $mt_keys = HW_POST::generate_posttypes_meta_keys($pt);

            $out = sprintf( '<select name="%s" id="%s" multiple style="max-height:500px;width:200px">', esc_attr( $widget_obj->get_field_name('more_meta_keys').'[]' ), esc_attr( $widget_obj->get_field_id('more_meta_keys') ));
            // Holds the HTML markup.
            $structure = array();

            foreach ( $mt_keys as $key ) {
                $structure[] = sprintf(
                    '<option value="%s" key="%s" >%s</option>',
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_html( $key )
                );
            }
            $out .= join( "\n", $structure );
            $out .= '</select>';

            echo $out;
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
	/**
	* load ajax pagination
	*/
	public function _hw_load_htpl_ajax(){
		if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_htpl_nonce")) {
		  exit("unauthorize");
	    }
		//turn on caching
		header("Cache-Control: max-age=2592000"); //30days (60sec * 60min * 24hours * 30days)
		$d = null;
		$this->number = $_REQUEST['w_id'];    //update widget id
		$data = $this->hw_parse_data($d,$this->number);
		if(!isset($_GET['paged'])) $_GET['paged']= 1;
		if(isset($_GET['paged'])) $data['query_args']['paged'] = $_GET['paged'];
		
		$instance = $data['instance'];
		$arrExlpodeFields = $data['arrExlpodeFields'];
        $metaFields = $data['metaFields'];  //meta fields
		
		$cat_posts = new WP_Query($data['query_args']);
		//update shared data
		$this->shared = compact('cat_posts','instance','arrExlpodeFields','metaFields');

		if(1||!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		  //$result = json_encode($result);
            $this->load_widget_template($instance,$this->shared);
	   }
	   else {
		  header("Location: ".$_SERVER["HTTP_REFERER"]);
	   }

	   die();
	}
	/**
	 * template redirect when get next page from pagination links
	 */
	public function _my_taxonomy_template_redirect(){
		if( isset($_GET['referer']) && $_GET['referer']=='hwtpl' && isset($_GET['ss']) && $_GET['ss'])
		{
			  $p1 = get_query_var('paged')? get_query_var('paged') : '1';
			  $link = get_term_link(get_term_by('slug',$_GET['ss'],$_GET['type']));
			  $link .= '/page/'.$p1;
			wp_redirect( $link );
			exit();
		}
	}
	/**
	 * get current widget number
	 */
	public function get_widget_num(){
		$instance = $this->get_widget_instance();
		return isset($_GET['w_id'])? $_GET['w_id'] : (isset($instance['widget_id'])? $instance['widget_id']: $this->number);
	}
	/**
	 * return id attribute for pagination links
	 */
	public function get_wrap_links_id($id=''){
        return 'hwtpl_pagination_'.($id? $id : $this->get_widget_num());
    }
	/**
	 * generate id attribute for holder
	 */
	public function get_holder_id($id=''){
        return 'hwtpl_holder_'.($id? $id : $this->get_widget_num());
    }
	
	/**
     * render pagination links
	 * @show pagination
     * @param $output
	 */
	public function show_ajax_pagination(/*$extract=array(),*/$output=true){
		extract(/*$extract*/$this->shared);

        //valid
        if(!isset($cat_posts)) return;

		if(isset($_GET['w_id'])) $w_id = $_GET['w_id'];
		else $w_id = $this->number;
		
		//show prev_next button
		$show_prev_next = isset($instance['show_prev_next']) && $instance['show_prev_next'];
		$big = 999999999;
		if(isset($instance['enable_ajax_pagination']) && $instance['enable_ajax_pagination']):
			$paged = 1; //hoặc 0
			if(isset($_GET['paged'])) {
			  $paged = $_GET['paged'];
			}
		$pagination = paginate_links( array(
			#'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),  
			'base' => admin_url('admin-ajax.php?action=htpl_ajax&nonce='.wp_create_nonce("hw_htpl_nonce").'&w_id='.$w_id.'&paged=%#%'),
			'format' => '?paged=%#%',
			'current' => max($paged,1), 
			'total' =>$cat_posts->max_num_pages,
		      'prev_next' => $show_prev_next,
			'prev_text'=> '«',
			'next_text'=> '»',
			//thêm tham số vào liên kết phân trang.
			'add_args'=>array('abc'=>1)       //result: /page/2/?s=xx&abc=1
		) );
		else:
			#if(isset($instance['cat_']) && $instance['cat_']){	//this ensured that you choose term taxonomy or selected current context
			//if current taxonomy page
			$paged = (intval(get_query_var('paged'))) ? intval(get_query_var('paged')) : 1;
			$pagination = paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),  
				'format' => '?paged=%#%',
				'current' => max($paged,1), 
				'total' =>$cat_posts->max_num_pages,
			      'prev_next' => $show_prev_next,
				'prev_text'=>'«',
				'next_text'=>'»',
				#thêm tham số vào liên kết phân trang.
				'add_args'=>array('referer'=>'hwtpl','type' => $instance['tax'],'ss'=>$instance['cat_'])       //result: /page/2/?s=xx&abc=1
			) );
			#}
		endif;
		if(isset($pagination)){
		    //default filter pagination output
			$pagination = str_replace('<span','<a',$pagination);
			$pagination = str_replace('</span','</a',$pagination);
			$pagination = str_replace('<a','<li><a',$pagination);
			$pagination = str_replace('</a','</a><li',$pagination);
			//filter pagination
			$pagination = apply_filters('hwtpl_pagination', $pagination, $this);

			if($output) echo $pagination;else return $pagination;
		}
	}
	/**
	 * display pagination links
	 * //@param array $data: array data contain variables to extract into other place (depricated)
	 * @param string $class: set class for pagination holder (depricated)
	 */
	public function display_pagination(/*$data,$class=''*/){
        /*old way:
         if(!isset($this->shared['instance']))
            $instance = $this->get_widget_instance();  //get instance
        else $instance = $this->shared['instance'];*/
        $instance = $this->get_widget_instance();  //get instance for new way
        if(!$this->enable_pagination()
             ) return; //make sure turn on show_pagination option

        if($this->enable_scrolling() ) { //pagination & scrolling content not work together
            echo 'Tắt chế độ cuộn nội dung, để sử dụng phân trang.';
            return;
        }
        //extract pagination args
        $pagination_class = '';
        $pagination_container_class = 'hw-pagenavi-container';
        extract($this->get_share_data('pagination'));

        echo '<div class="'.$pagination_container_class.'">';
	    echo '<ul class="'.$pagination_class.'" id="'.$this->get_wrap_links_id().'">';
	    $pagenav = $this->show_ajax_pagination(/*$data,*/false);
	    echo $pagenav;
	    echo '<li class="ajax-loader"></li>';
	    echo '</ul>';
        echo '</div>';
	    //init ajax for pagination links
	    if(isset($instance['enable_ajax_pagination']) && $instance['enable_ajax_pagination']==1){
	       echo "<script>hwtpl_setup_ajax_pagination('#".$this->get_wrap_links_id()."','#".$this->get_holder_id()."')</script>";
	    }
	    
	}
	/**
	 * load pagination
	 * @param string $class: pagination container class (depricated)
	 */
	public function load_pagination(/*$class*/){
	    //extract data
	    //if(isset($this->shared)) extract($this->shared);
	    
	    //$class = apply_filters('hwtpl_pagination_container_class', ''); 
	    //display pagination
	    $this->display_pagination(/*$class*/);
	}
	/**
	 * parse common data
     * @param $instance
     * @param $widget_id
	 */
	public function hw_parse_data(&$instance = array(),$widget_id=''){
		if(!is_numeric($widget_id)) $widget_id = $this->number;
		if(!is_array($instance) || count($instance)==0) $instance = $this->get_widget_instance($instance);
		else $this->get_widget_instance($instance);

		// Excerpt length
		if(isset($instance['excerpt_length'])) {
            $new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
            if ( $instance["excerpt_length"] > 0 )
                add_filter('excerpt_length', $new_excerpt_length);//_print($instance["excerpt_length"]);
        }

		$arrExlpodeFields = explode(',',$instance['display']);
        //meta fields for select post types
        if(!isset($instance['more_meta_keys'])) $instance['more_meta_keys']= array();
        $metaFields = is_string($instance['more_meta_keys'])? explode(',',$instance['more_meta_keys']): $instance['more_meta_keys'];
		
		//sort by
		$valid_sort_by = array('date', 'title', 'comment_count', 'rand');
		if ( in_array($instance['sort_by'], $valid_sort_by) ) 
		{
		    $sort_by = $instance['sort_by'];

		    $sort_order = isset($instance['sort_order']) ? $instance['sort_order'] : 'DESC'; 
		} else 
		{
		    // by default, display latest first
		    $sort_by = 'date';
		    $sort_order = 'DESC';
		}
		// Get effect for front end
		$effects = $instance['effects']	;
		$effects_time = $instance['effects_time'];
		
		$taxes = array();
		if(isset($instance['cat_']) && $instance['cat_']){
			$taxes[] = array(
				'taxonomy'=>$instance['tax'],
				'field' => 'slug',
				'terms'=>array($instance['cat_']),
				 'operator'=>'IN'
			);
		}
		// Get  post info.
		$args = array(
			"numberposts" => $instance["num"] , #showposts, note: don;t combine posts_per_page & showposts
			"orderby"=> $sort_by ,
			"order"=> $sort_order,
			"posts_per_page" => -1
		);
		if(count($taxes)) $args['tax_query'] = $taxes;
		if(isset($instance['nav_posts_num']) && is_numeric($instance['nav_posts_num']) 
			#&& (!isset($instance['enable_scrolling']) || $instance['enable_scrolling'] === 'off')	//make sure scrolling mode is disabled
		)
		{
			$args['paged']= 1;	//alway show first page 
			$args['posts_per_page'] = $instance['nav_posts_num'];
			/*if($instance['enable_ajax_pagination'] || (isset($instance['cat_']) && $instance['cat_'])) 
				$args['posts_per_page'] = $instance['nav_posts_num'];*/
		}
			
		if(isset($instance['tax']) && $instance['tax']) $args['taxonomy']=   $instance['tax'];	#taxonomy
		if(isset($instance['posttype']) && $instance['posttype']) $args['post_type']=   explode(',',$instance['posttype']);	#post type
        #filter by user/author
        if(isset($instance['author'])) {
            if(is_numeric($instance['author']) && $instance['author'] != '-1') {
                $args['author'] = $instance['author'];
            }
            elseif($instance['author'] == 'logined_user' && is_user_logged_in()) {
                $args['author'] = get_current_user_id();
            }
        }

		//to return
		$result = array(
				'query_args' => apply_filters('htpl_query_args',$args, $instance),
				'instance' => $instance,
				'arrExlpodeFields' => $arrExlpodeFields,
                'metaFields' => $metaFields,
			);
        if(isset($new_excerpt_length)) $result['new_excerpt_length'] = $new_excerpt_length;
        return $result;
	}
	/**
	 * format number
     * @param $value: value to format
     * @param $unit: unit in px or %, em...(default px)
	 */
	public static function format_unit($value,$unit = 'px'){
		return substr(trim($value),-1) == '%'? $value : floatval($value).$unit;
	}
	/**
	 * init scrolling
     * @param $theme: user theme setting (depricated)
	 */
	private  function init_content_options($args = array()){
        extract($args);

		if(!isset($instance)) $instance = $this->get_widget_instance();
        $hwtpl_wrapper_id = $this->get_holder_id($instance['widget_id']);

		if($this->enable_scrolling() && $instance['scroll_type'] == 'smoothdivscroll'){ //for smoothdivscroll

            //validation
            if(!isset($instance['scroll_interval'])) $instance['scroll_interval'] = '10';  //default 10 of interval for smoothdivscroll

            if(!isset($instance['scroll_width']) || !$instance['scroll_width']) {   //full width
                $instance['scroll_width'] = '100%';
            }
			?>
			<style>
			#<?php echo $hwtpl_wrapper_id?> .smoothDivScroll
			{

				<?php if(isset($instance['scroll_height'])) echo 'height:'.$this->format_unit($instance['scroll_height']).';'?>
				<?php if(isset($instance['scroll_width'])) echo 'width:'.$this->format_unit($instance['scroll_width']).';';
				    else echo 'width: 100%;';
				?>
				position: relative;
			}
			</style>
			<script type="text/javascript">
			// Initialize the plugin with no custom options
             jQuery(document).ready(function ($) {
                // None of the options are set
                <?php if($this->is_vertical_scrolling()){?>
                    jQuery("#<?php echo $hwtpl_wrapper_id?> .smoothDivScroll").smoothDivScroll_vertical({
                <?php }else{?>
                    jQuery("#<?php echo $hwtpl_wrapper_id?> .smoothDivScroll").smoothDivScroll({
                <?php }?>
                    manualContinuousScrolling: false,
                    autoScrollingMode: "onStart",
                    mousewheelScrolling: "allDirections",
                    hotSpotScrolling:false,
                    <?php if(isset($instance['scroll_interval'])){?>
                    autoScrollingInterval:'<?php echo $instance['scroll_interval']?>',
                    <?php }?>
                    <?php if(isset($instance['scroll_direction'])){?>
                    autoScrollingDirection:'<?php echo $instance['scroll_direction']?>',
                    <?php }?>

                });
                // Mouse over
                jQuery("#<?php echo $hwtpl_wrapper_id?> .smoothDivScroll").bind("mouseover", function(){
                    <?php if($this->is_vertical_scrolling()){?>$(this).smoothDivScroll_vertical("stopAutoScrolling");<?php }else{?>
                        $(this).smoothDivScroll("stopAutoScrolling");
                    <?php }?>
                });

                // Mouse out
                jQuery("#<?php echo $hwtpl_wrapper_id?> .smoothDivScroll").bind("mouseout", function(){
                    <?php if($this->is_vertical_scrolling()){?>$(this).smoothDivScroll_vertical("startAutoScrolling");<?php }else{?>
                        $(this).smoothDivScroll("startAutoScrolling");
                    <?php }?>
                });
              });

			</script>
			<script type="text/javascript">
			
			</script>
			<?php
		}
        /**
         * for jcarousellite lib
         */
        if($this->enable_scrolling() && $instance['scroll_type'] == 'jcarousellite'){
            $option = array(
                'mouseWheel' => true,
                'start' => 0
            );
            //valid
            if(!isset($instance['scroll_width']) || !$instance['scroll_width']) {   //full width
                $instance['scroll_width'] = '100%';
            }
            if(!isset($instance['scroll_interval'])) $instance['scroll_interval'] = '800';  //default 800 of interval
            if(!isset($instance['scroll_delay'])) $instance['scroll_delay'] = '800';  //default 800 of time delay


            //auto scrolling & delay between 2 slides
            if(isset($instance['auto_scroll_mode']) && $instance['auto_scroll_mode'] && isset($instance['scroll_delay'])) {
                $option['auto'] = (int)$instance['scroll_delay'];
            }
            if(isset($instance['visible_scroll_num']) && is_numeric($instance['visible_scroll_num'])) { //visble
                $option['visible'] = (int)$instance['visible_scroll_num'];
            }
            if(isset($instance['scroll_interval']) && is_numeric($instance['scroll_interval'])) {   //scroll speed
                $option['speed'] = (int)$instance['scroll_interval'];
            }
            if(isset($instance['scroll_num'])) $option['scroll'] = (int)$instance['scroll_num']; // scroll number of slides
            if(isset($instance['scroll_easing']) && $instance['scroll_easing']) $option['easing'] = $instance['scroll_easing'];   //easing effects
            //scrolling direction
            if($this->is_vertical_scrolling()){
                $option['vertical'] = true;
            }
            else $option['vertical'] = false;

            //merge options
            if(isset($theme['scroll_options']['jcarousellite']) && is_array($theme['scroll_options']['jcarousellite'])) {
                $option = array_merge($option,$theme['scroll_options']['jcarousellite']);
            }
            if(!empty($skin_options)) $option =  array_merge($option, $skin_options);
            $option_obj = HW_SKIN_Option::build_json_options($option);  //json_encode($option)

            ?>
            <style>
                #<?php echo $hwtpl_wrapper_id?> .<?php echo $instance['scroll_type']?>
                {
                <?php if(isset($instance['scroll_height'])) echo 'height:'.$this->format_unit($instance['scroll_height']).';'?>
                <?php if(isset($instance['scroll_width'])) echo 'width:'.$this->format_unit($instance['scroll_width']).';';
                    else echo 'width: 100%;';
                ?>
                    position: relative;
                }
            </style>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var jcarousel_lite = $("#<?php echo $hwtpl_wrapper_id?> .<?php echo $instance['scroll_type']?>").jCarouselLite(<?php echo $option_obj?>);

            });
        </script>
            <?php
        }

        //$this->init_widget_features($args);   //call before include skin file
	}

    /**
     * implement widget features
     * @param array $args
     */
    public function init_widget_features($args = array()) {
        extract($args);
        if(!isset($instance)) $instance = $this->get_widget_instance();

        $features = array();

        //widget feature fancybox
        $fancybox = HW_AWC::get_widget_feature($this, 'fancybox');
        if($fancybox && HW_AWC::check_widget_feature($this, 'fancybox') && $fancybox->is_active($instance) && isset($skin_options)) {
            $features['fancybox'] = array('object' => $fancybox, 'data' => array());

            if(!empty($skin_options['fancybox_images_group'])) {
                $fancybox_group = $skin_options['fancybox_images_group'];
                $fancybox_group_rel = base64_encode($fancybox_group);
            }
            else {
                $fancybox_group = HW_String::generateRandomString();
                $fancybox_group_rel = HW_String::generateRandomString();
            }

            $fancybox->run('.'.preg_replace('#^[\s\.]#', '',$fancybox_group));

            $features['fancybox']['data'] = (object)array(
                'fancybox_group' => $fancybox_group,
                'fancybox_group_rel' => $fancybox_group_rel
            );
        }
        return $features;
    }

    /**
     * init scrollbar options from skin
     * @param $theme_options: user skin options
     * @param $theme: theme setting
     */
    public function init_scrollbar_options($theme_options, $theme) {
        $instance = $this->get_widget_instance();       //get widget data
        $hwtpl_wrapper_id = $this->get_holder_id($instance['widget_id']);
        $hwtpl_scrollbar_wrapper_class = isset($theme['scrollbar_css'])? $theme['scrollbar_css'] : '';

        //scrollbar
        if(isset($instance['enable_scrollbar']) && $instance['enable_scrollbar']) {
        ?>
            <style>

            </style>
            <?php
        }
    }

	/**
	 * load widget template
	 * @param array $instance: widget instance (optional)	 
	 * @param array $data: array of data need to be extract to current context
	 */
	private function load_widget_template(&$instance = array(), $data = array()){
        //extract data
		if(isset($this->shared) && is_array($this->shared)) extract($this->shared);
		else
		    if(is_array($data)) extract($data);

        //extract variables from array $args that get from 'widget' method
        if(isset($args)) extract($args);

		if(!is_array($instance) || !count($instance)) {
			$instance = $this->hw_parse_data();
			$instance = $instance['instance'];
		}
		else $this->get_widget_instance($instance);
		//by hoangweb.com
          if($this->skin){
               //load skin
               $file = $this->skin->get_skin_file(empty($instance['skin'])? 'default':$instance['skin']);             
               if(file_exists($file)) {

                   /**
                    * pagination skin
                    */
                   $paginav = $this->skin->get_skin_instance('pagination')->get_skin_file(empty($instance['pagination_skin'])? 'default':$instance['pagination_skin'] );
                   //skin settings
                   /*
                   $paginav_skin_setting = isset($instance['skin_setting'])? $instance['skin_setting'] : '';

                   $skin_setting_file = $this->skin->get_file_skin_setting(); //current skin setting
                   $skin_options = $this->skin->get_file_skin_options();      //current skin options

                   if(file_exists($skin_setting_file)) include ($skin_setting_file);
                   if(file_exists($skin_options)) include ($skin_options);

                   if(isset($theme) && isset($theme['options'])) $default_options = $theme['options'];
                   if(isset($default_options) && isset($theme_options)) {
                       $paginav_skin_setting = HW_SKIN::merge_skin_options_values($paginav_skin_setting, $default_options, $theme_options);
                   }
                   */

                   /**
                    * scrollbar skin
                    */
                   $scrollbar =  $this->skin->get_skin_instance('scrollbar')->get_skin_file(empty($instance['scrollbar_skin'])? 'default':$instance['scrollbar_skin'] );

                   //widget feature: grid_posts
                   $grid_posts = HW_AWC::get_widget_feature($this, 'grid_posts');
                   $awc_enable_grid_posts = false;  //disable by default

                   if($grid_posts && HW_AWC::check_widget_feature($this, 'grid_posts') ) {
                       $awc_enable_grid_posts = $grid_posts->is_active($instance);
                       if($awc_enable_grid_posts) $awc_grid_posts_cols = $grid_posts->get_field_value('awc_grid_posts_cols');
                   }


                   /**
                    * init some variables
                    */
                   $theme = array();   //declare stylesheets file
                   //content wrapper ID
                   $hwtpl_wrapper_id = $this->get_holder_id($instance['widget_id']);
                   $hwtpl_scrollbar_wrapper_class = '';    //scrollbar content wrapper css class
                   $hwtpl_pagination_class = '';    //for pagination

                   /**
                    * load pagination component
                    */ 
                   if(file_exists($paginav)) {
                       include($paginav);

                       //valid
                       if(!isset($theme['scripts'])) $theme['scripts'] = array();
                       if(!isset($theme['styles'])) $theme['styles'] = array();

                       //migrate skin from pagination module
                       if($instance['use_default_pagenav_skin'] && hw_is_active_module('pagination')) {
                           $theme = HWPageNavi_Core::render_pagination_skin();
                       }
                       elseif(count($theme['styles']) || count($theme['scripts'])) {
                           $this->skin->get_skin_instance('pagination')->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                           //enqueue stuff from skin
                           /*HW_SKIN::enqueue_skin_assets(array(
                               'instance' => $this->skin,
                               'hash_skin' => $instance['skin'],
                               'skin_file' => $file,
                               'skin_settings' => $theme,
                               'skin_options' => $skin_setting
                           ));*/
                       }
                       //parse template tags
                       $hwtpl_pagination_class = isset($theme['pagination_class'])? $theme['pagination_class'] : '';
                       $this->share_data('pagination',  $theme);
                   }
                   //_print($this->skin->get_active_skin()); //enqueue stuff from skin
                   /**
                    * load scrollbar component
                    */
                   if(isset($instance['enable_scrollbar']) && $instance['enable_scrollbar']     //must active scrollbar option
                       && file_exists($scrollbar) ) {
                       //load skin resource
                       $scrollbar_options_config = $this->skin->get_skin_instance('scrollbar')->get_file_skin_options(empty($instance['scrollbar_skin'])? 'default':$instance['scrollbar_skin']);   //theme options configuration
                       $scrollbar_theme_setting = $this->skin->get_skin_instance('scrollbar')->get_file_skin_setting();     //load theme-setting.php

                       //theme options
                       $scrollbar_theme_options = isset($instance['scrollbar_skin_setting'])? $instance['scrollbar_skin_setting'] : array();   //scrollbar skin options
                        //reset
                       $theme['styles'] = array();   //declare stylesheets file
                       $theme['scripts'] = array();  //declare js files
                       $theme['options'] = array();

                       if(!empty($scrollbar_options_config) && file_exists($scrollbar_options_config)) {
                           include($scrollbar_options_config);      //include define scrollbar theme options
                       }
                       if(!empty($scrollbar_theme_setting) && file_exists($scrollbar_theme_setting)) {
                           include($scrollbar_theme_setting);   //scrollbar theme setting
                       }
                       if( isset($theme_options) && isset($theme)){
                           $default = isset($theme['options']) ? $theme['options'] : array();

                           $result = HW_SKIN::get_skin_options($scrollbar_theme_options, $default,$theme_options);
                           $scrollbar_theme_options = array_merge($scrollbar_theme_options, $result);
                       }
                       /*
                       //best way but use old way as above do
                       $scrollbar_skin_setting = isset($instance['scrollbar_skin_setting'])? $instance['scrollbar_skin_setting'] : '';

                   $scrollbarskin_setting_file = $this->skin->get_skin_instance('scrollbar')->get_file_skin_setting(); //current skin setting
                   $scrollbar_skin_options = $this->skin->get_skin_instance('scrollbar')->get_file_skin_options();      //current skin options

                   if(file_exists($scrollbarskin_setting_file)) include ($scrollbarskin_setting_file);
                   if(file_exists($scrollbar_skin_options)) include ($scrollbar_skin_options);

                   if(isset($theme) && isset($theme['options'])) $scrollbar_default_options = $theme['options'];
                   if(isset($scrollbar_default_options) && isset($theme_options)) {
                       $scrollbar_skin_setting = HW_SKIN::merge_skin_options_values($scrollbar_skin_setting, $scrollbar_default_options, $theme_options);
                   }
                       */
                       //parse template tags
                       $hwtpl_scrollbar_wrapper_class = isset($theme['scrollbar_css'])? $theme['scrollbar_css'] : '';

                       include($scrollbar);
                       //valid
                       /*if(!isset($theme['scripts'])) $theme['scripts'] = array();
                       if(!isset($theme['styles'])) $theme['styles'] = array();

                       if(count($theme['styles']) || count($theme['scripts'])) {
                           $this->skin->get_skin_instance('scrollbar')->enqueue_files_from_skin($theme['styles'], $theme['scripts']);
                       }*/
                       //enqueue stuff from skin
                       HW_SKIN::enqueue_skin_assets(array(
                           'instance' => $this->skin->get_skin_instance('scrollbar'),
                           'hash_skin' => $instance['scrollbar_skin'],
                           'skin_file' => $scrollbar,
                           'skin_settings' => $theme,
                           'skin_options' => $scrollbar_theme_options
                       ));
                       $this->init_scrollbar_options($scrollbar_theme_options, $theme);
                   }

                   /**
                    * load main skin
                    */
                   $options_config = $this->skin->get_file_skin_options();
                   $skin_setting = $this->skin->get_file_skin_setting();
                   if(file_exists($skin_setting)) include ($skin_setting);

                   //theme options
                   $skin_options = isset($instance['skin_setting'])? $instance['skin_setting'] : array();

                   $skin_options = HW_SKIN::merge_skin_options_values($skin_options, $skin_setting,$options_config);

                   //params
                   if(empty($instance['intermediate_image_sizes'])  //thumbnail size
                       && !empty($instance['thumb_w']) && !empty($instance['thumb_h']))
                   {
                       $image_size = array($instance['thumb_w'], $instance['thumb_h']);
                   }
                   elseif(!empty($instance['intermediate_image_sizes'])) {
                       $image_size = $instance['intermediate_image_sizes'];
                   }
                   else $image_size = 'thumbnail';

                    //reset
                    $theme['styles'] = array();   //declare stylesheets file
                    $theme['scripts'] = array();  //declare js files
                   $theme['options'] = array(); //refresh theme options

                   //widget features
                   $wfeatures = $this->init_widget_features(array(
                       'theme' => $theme,
                       'skin_options' => $skin_options,
                       'instance' => $instance
                   ));
                   //render skin template power by twig
                   $data=  array(
                       'hwtpl_scrollbar_wrapper_class','cat_posts','full_image_src','fancy_group','fancybox_g1','arrExlpodeFields',
                       'image_size','wfeatures','hwtpl_wrapper_id', 'args', 'instance',
                       'open_title_link','close_title_link'
                   );
                   $data = compact($data);
                   $data['context'] = $this;    //reference to this object
                   $content = $this->skin->render_skin_template(($data),false);
                   if($content!==false) echo $content;

					include($file);
                   //valid
                   /*if(!isset($theme['scripts'])) $theme['scripts'] = array();
                   if(!isset($theme['styles'])) $theme['styles'] = array();

                   if(count($theme['styles']) || count($theme['scripts'])) {
                       $this->skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']); //enqueue stuff from skin
                   }*/
                   //enqueue stuff from skin
                   HW_SKIN::enqueue_skin_assets(array(
                       'instance' => $this->skin,
                       'hash_skin' => $instance['skin'],
                       'skin_file' => $file,
                       'skin_settings' => $theme,
                       'skin_options' => $skin_options
                   ));
					//init scrolling
					//add_action('wp_footer',array(&$this, 'init_content_options'),1000);	//don't use wp hook to setup scroll
					$this->init_content_options(array(
                        'theme' => $theme,
                        'skin_options' => $skin_options,
                        'instance' => $instance
                    ));
			   }
          }
          else echo __('not found class HW_SKIN.');
	}


	/**
	 * detect vertical scrolling
	 */
	private function is_vertical_scrolling(){
		$instance = $this->get_widget_instance();
		$return = in_array($instance['scroll_direction'], array('endlesslooptop','endlessloopbottom','vertical'));
		return $return;
	}
	/**
	 * detect horizontal scrolling
	 */
	private function is_horizontal_scrolling(){
		return !is_vertical_scrolling();
	}

    /**
     * check whether use scrolling for user content
     * @return bool
     */
    private function enable_scrolling(){
        $instance = $this->get_widget_instance();
        return isset($instance['enable_scrolling']) && $instance['enable_scrolling'];
    }

    /**
     * whether display pagination is enabled?
     * @return bool
     */
    private function enable_pagination(){
        $instance = $this->get_widget_instance();
        return (isset($instance['show_pagination']) && $instance['show_pagination']) && (!empty($instance['cat_']) || (isset($instance['query_data']) && $instance['query_data'] == 'current_context') );
    }
	/**
	 * prepare settings
     * @param $instance: widget instance data
	 */
	private function init_settings($instance = array()){
		 if(!$instance || !is_array($instance) || !count($instance)) $instance = $this->get_widget_instance();	//get widget settings
		//scrolling content
        if($this->enable_scrolling()){
            $jcarousellite = HW_Libraries::get('sliders/jcarousellite');
            $smoothDivScroll = HW_Libraries::get('sliders/smoothDivScroll');

            $jcarousellite->enqueue_scripts('jquery.kinetic.min.js');   //add drag scrolling feature
            $jcarousellite->enqueue_scripts('jquery.mousewheel.min.js');

			#wp_enqueue_script('jquery.kinetic.min.js',plugins_url('js/jquery.kinetic.min.js',__FILE__),array('jquery'),0,true);
			#wp_enqueue_script('jquery.mousewheel.min.js',plugins_url('js/jquery.mousewheel.min.js',__FILE__),array('jquery'),0,true);

            if($instance['scroll_type'] == 'smoothdivscroll'){
                if($this->is_vertical_scrolling()){
                    //enqueue query.smoothDivScroll, note this modified version of original smoothDivScroll to support vertical scrolling direction
                    /*wp_register_script('jquery.smoothDivScroll-vertical-1.2.js',plugins_url('js/jquery.smoothDivScroll-vertical-1.2.js',__FILE__),array('jquery'));
                    wp_enqueue_script('jquery.smoothDivScroll-vertical-1.2.js');*/
                    $smoothDivScroll->enqueue_scripts('jquery.smoothDivScroll-vertical-1.2.js');
                }
                else{
                    //wp_enqueue_script('jquery.smoothdivscroll-1.3-min.js',plugins_url('js/jquery.smoothdivscroll-1.3-min.js',__FILE__),array('jquery'));
                    $smoothDivScroll->enqueue_scripts('jquery.smoothdivscroll-1.3-min.js');
                }
                //wp_enqueue_style('smoothDivScroll.css',plugins_url('css/smoothDivScroll.css',__FILE__));
                $smoothDivScroll->enqueue_styles('smoothDivScroll.css');
            }
            if($instance['scroll_type'] == 'jcarousellite'){
                #wp_enqueue_script('jquery.easing.1.3.js',plugins_url('js/jquery.easing.1.3.js',__FILE__),array('jquery'));
                #wp_enqueue_script('jquery.jcarousellite.min.js',plugins_url('js/jquery.jcarousellite.min.js',__FILE__),array('jquery'));
                $jcarousellite->enqueue_scripts('jquery.easing.1.3.js', 'jquery.jcarousellite.min.js', 'jquery.jcarousellite.pauseOnHover.js');
            }

		}
        //load scrollbar lib
        if(isset($instance['enable_scrollbar']) && $instance['enable_scrollbar']) {
            //wp_enqueue_script('jquery.lionbars.0.3.js', HWTPL_PLUGIN_URL.'/js/jquery.lionbars.0.3.js', array('jquery'));
        }
	 }

    /**
     * used by widget widget feature saveconfig to extract usefull data from instance
     * @param $instance this widget instance
     */
    public function AWC_WidgetFeature_saveconfig($instance){
        $data = $instance;
        if(!empty($instance['skin'])) {
            $data['skin_thumb'] = $this->skin->get_skin_thumb($instance['skin']);
            //unset($data['skin']); //do not remove skin the widget will use to display content
        }
        //parse pagination skin
        if(!empty($instance['pagination_skin'])) {
            $data['pagination_skin_thumb'] = $this->skin->get_skin_instance('pagination')->get_skin_thumb($instance['pagination_skin']);
            unset($data['pagination_skin']);
        }
        //parse scrollbar skin
        if(!empty($instance['scrollbar_skin'])) {
            $data['scrollbar_skin_thumb'] = $this->skin->get_skin_instance('scrollbar')->get_skin_thumb($instance['scrollbar_skin']);
            unset($data['scrollbar_skin']);
        }

        return $data;
    }

    /**
     * Displays category posts widget on blog.
     *
     * @param array $instance current settings of widget .
     * @param array $args of widget area
     */
    public function widget($args,$instance) {
        global $post;
        $post_old = $post; // Save the post object.
        extract($args);
        //valid data
        if(!isset($instance['view_all_pos'])) $instance['view_all_pos'] ='';

        // If not title, use the name of the category.
        if( !$instance["widget_title"] && isset($instance["cat_"]) && $instance["cat_"])
        {
            $category_info = get_category($instance["cat_"]);
            if(isset($category_info->name)) $instance["widget_title"] = $category_info->name;    //if choose term taxonomy
        }
        $instance['title'] = apply_filters('widget_title', $instance['widget_title'],$instance, $this->id_base ); // Title

        $data = $this->hw_parse_data($instance);
        $arrExlpodeFields = $data['arrExlpodeFields'];    //allow display post fields
        $metaFields = $data['metaFields'];    //allow display post meta fields

        //create WP_Query
        if(isset($instance['query_data']) && $instance['query_data'] == 'current_context') {
            global $wp_query;
            $cat_posts = $wp_query;
        }
        else {
            if(!empty($instance['specific_post']) ) {   //get one post
                $cat_posts = new WP_Query(array('p' => $instance['specific_post']));
            }
            else $cat_posts = new WP_Query($data['query_args']);    //normal query posts
        }

        //cat link
        if(isset($instance['page_cat_link']) && $instance['page_cat_link']){  //ensure that not link to current page if page_cat_link is empty
            $link = get_permalink($instance['page_cat_link']);
        }
        elseif(isset($instance['cat_']) && $instance['cat_']){
            $link = get_term_link(get_term_by('slug',$instance['cat_'],$instance['tax']));
        }
        if(is_wp_error($link)) $link = '';
        //open cat link
        $open_title_link = '';
        if(isset($instance['enable_cat_link']) && $instance['enable_cat_link']=='on' ){
            if(isset($link) && !is_wp_error($link)) $open_title_link = '<a href="'.$link.'">';
        }
        //close cat link
        $close_title_link = '';
        if(!empty($open_title_link) && isset($instance['enable_cat_link']) && $instance['enable_cat_link']=='on'){
            $close_title_link = '</a>';
        }
        //share data cross method
        $this->shared = compact(
             'cat_posts','instance','args','arrExlpodeFields','metaFields','open_title_link','close_title_link',
            'link'
        );

        $i = 0;
        //init js libraries
        $this->init_settings($instance);
        // Post list
        ?>
        <?php
        //show view all link
        if(isset($link) && !is_wp_error($link)) $view_all_link = '<a class="view-all" href="'.$link.'">'.$instance['view_all_text'].'</a>';
        if(($instance['view_all_pos'] == 'top' || $instance['view_all_pos'] == 'top_bottom') && isset($view_all_link))
        {
            echo $view_all_link;
        }
        //render posts list of taxonomy
        $this->load_widget_template($instance);
        //show view all link
        if(($instance['view_all_pos'] == 'bottom' || $instance['view_all_pos'] == 'top_bottom') && isset($view_all_link))
        {
            echo $view_all_link;
        }
        ?>
        <?php
        if(isset($data['new_excerpt_length'])) {
            //remove excerpt_length filter to reset to default excerpt behavoir
            remove_filter('excerpt_length', $data['new_excerpt_length']);
        }
        $post = $post_old; // Restore the post object.
    }
	/**
	 * Form processing...
	 *
	 * @param array $new_instance of widget .
	 * @param array $old_instance of widget .
	 */
	
	public function update($new_instance,$old_instance)
	{
		global $wpdb;
		$displayFields = array();
		if($_POST['display']){
			array_push($_POST['display'], 'title');
			$displayFields = array_unique($_POST['display']);
		}
		else
		{
			$displayFields = array('title');
		}
		$strImplodeFields = implode(',',$displayFields);
		$new_instance['display'] = $strImplodeFields;		
		//by hoangweb
        $new_instance['show_pagination'] = (strtolower($new_instance['show_pagination']) == 'on')? '1' : '0';   //enable pagination
        $new_instance['enable_ajax_pagination'] = (strtolower($new_instance['enable_ajax_pagination']) == 'on')? '1' : '0'; //allow ajax in pagination link
		$new_instance['show_prev_next'] = (strtolower($new_instance['show_prev_next']) == 'on')? '1' : '0';
        $new_instance['auto_scroll_mode'] = (strtolower($new_instance['auto_scroll_mode']) == 'on')? '1' : '0';
        //use default  skin with pagination module
        $new_instance['use_default_pagenav_skin'] = (strtolower($new_instance['use_default_pagenav_skin']) == 'on')? '1' : '0';

		//valid number
		$new_instance['scroll_width'] = $this->format_unit($new_instance['scroll_width'],'');
		$new_instance['scroll_height'] = $this->format_unit($new_instance['scroll_height'],'');
		//filter posts type
		$new_instance['posttype'] = is_array($new_instance['posttype'])? implode(',',$new_instance['posttype']) : $new_instance['posttype'];
        //session_start();$_SESSION['a5']=$new_instance;
        //scrollbar option
        $new_instance['enable_scrollbar'] = (strtolower($new_instance['enable_scrollbar']) == 'on')? '1' : '0';;
        //$new_instance['scrollbar_skin'];
//unset($new_instance['hw_widopt_setting']);    //heavy data ->remove low data to refresh wp cache
        //save current skin to db for this widget
        $this->skin->save_skin_assets(array(
            'skin' => array(
                'hash_skin' => $new_instance['skin'],
                'hwskin_condition' => '',#$new_instance['skin_condition'] ,
                'skin_options' => $new_instance['skin_setting'],

            )
        ));
        //save scrollbar skin
        $this->skin->get_skin_instance('scrollbar')->save_skin_assets(array(
            'skin' => array(
                'hash_skin' => $new_instance['scrollbar_skin'],
                'hwskin_condition' => '',#$new_instance['skin_condition'] ,
                'skin_options' => $new_instance['scrollbar_skin_setting'],

            ),
            'status' => ($new_instance['enable_scrollbar'])
        ));

		return $new_instance;
	}

	/**
	 * The configuration form.
	 *
	 * @param array $instance of widget to display already stored value .
	 * 
	 */
	public function form($instance)
	{ 	
		$displayFields = array();		
		$displayFields = isset($instance['display']) ? $instance['display'] : 'title';
		$display= isset($instance['display']) ? $instance['display'] : array();
		$arrExlpodeFields = explode(',', $displayFields);
		$instance["widget_w"] = isset($instance["widget_w"]) ? $instance["widget_w"] : '220';
		$instance["widget_h"] = isset($instance["widget_h"]) ? $instance["widget_h"] : '300';
		$instance["excerpt_length"] = isset($instance["excerpt_length"]) ? $instance["excerpt_length"] : '50';
		$instance["scroll_by"] = isset($instance["scroll_by"]) ? $instance["scroll_by"] : '3';
		$instance["date_format"] = isset($instance["date_format"]) ? $instance["date_format"] : 'F j, Y';
		$instance["effects_time"] = isset($instance["effects_time"]) ? $instance["effects_time"] : '3000';
		$instance["sort_order"] = isset($instance["sort_order"]) ? $instance["sort_order"] : 'desc';
		
		$instance["view_all_link"] = isset($instance["view_all_link"]) ? $instance["view_all_link"] : '';
		$instance["view_all_pos"] = isset($instance["view_all_pos"]) ? $instance["view_all_pos"] : '';
		$instance["view_all_text"] = isset($instance["view_all_text"]) ? $instance["view_all_text"] : 'Xem thêm';
		
		$instance["num"] = isset($instance["num"]) ? $instance["num"] : '50';
		$instance['sort_by'] = isset($instance['sort_by'])? $instance['sort_by'] : '';
		$taxonomy		= isset( $instance['tax'] )    ? esc_attr( $instance['tax'] )   : '';

		$current_pt		= isset( $instance['posttype'] )    ? esc_attr( $instance['posttype'] )   : 'page,post';
		$current_pt = explode(',',$current_pt);
		
		if(!isset($instance['nav_posts_num'])) $instance['nav_posts_num'] = get_option('posts_per_page');
		if(!isset($instance['page_cat_link'])) $instance['page_cat_link'] = '';
        /**
         * scrolling option
         */
        $instance['scroll_type'] = isset($instance['scroll_type'])? $instance['scroll_type'] : '';    //carousel type
        //direction
        $instance['scroll_direction'] = isset($instance['scroll_direction'])? $instance['scroll_direction'] : 'endlessloopbottom';
		$instance['scroll_interval'] = isset($instance['scroll_interval'])? $instance['scroll_interval'] : '10';	//scrollinh interval
		//dimention
		$instance['scroll_height'] = isset($instance['scroll_height'])? $instance['scroll_height'] : '200';	//scroll height area
		//restrict post title characters
		$instance["post_title_leng"] = isset($instance['post_title_leng'])? $instance['post_title_leng'] : '60';
		//valid
		if(!isset($instance["widget_title"])) $instance["widget_title"] = '';
		$expandoptions = isset($instance['expandoptions'])? $instance['expandoptions'] : 'contract';  //get visible status and hide default

        //create feature tog
        if(class_exists('HW_ButtonToggle_widget')) {
            $btn_tog = new HW_ButtonToggle_widget($this,$instance);
        }

        include(HWTPL_PLUGIN_PATH.'/includes/templates/widget-form.php');   //form template

        //close feature tog
        if(isset($btn_tog)) $btn_tog->set_button_toggle_end_wrapper();
	}
} 
#add_action('widgets_init', create_function('', 'return register_widget("HW_Taxonomy_Post_List_widget");'));
add_action('hw_widgets_init', create_function('', 'return register_widget("HW_Taxonomy_Post_List_widget");'));

/**
 * Below code is to display tinymce button on page.
 * @hook admin_init
 */
add_action( 'admin_init', 'hw_cplw_addTinyMCEButtons' );
function hw_cplw_addTinyMCEButtons() {
    add_filter("mce_external_plugins", "hw_cplw_add_TMCEbutton");
    add_filter('mce_buttons', 'hw_cplw_register_TMCEbutton');
}

/**
 * @hook mce_buttons
 * @param $buttons
 * @return mixed
 */
function hw_cplw_register_TMCEbutton($buttons) {
    array_push( $buttons, "separator", 'CPLWPosts' ); 
    return $buttons;
}

/**
 * @hook mce_external_plugins
 * @param $plugin_array
 * @return mixed
 */
function hw_cplw_add_TMCEbutton($plugin_array) {
    $plugin_array['cplwPosts'] = plugin_dir_url(__FILE__). 'js/tinymce_button.js';
    return $plugin_array;
}
?>