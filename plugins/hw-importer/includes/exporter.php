<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/11/2015
 * Time: 12:22
 */
//cover from file: wp-admin/includes/export.php
define( 'HW_WXR_VERSION', '1.0' );
/**
 * Interface HW_Export_Interface
 */
interface HW_Export_Interface {
    /**
     * @param $xml
     * @return mixed
     */
    public function export_wxr_data($xml=null);
}

/**
 * Class HW_Export
 */
class HW_Export extends HW_WXR_Parser implements HW_Export_Interface{
    /**
     * XML version
     */
    const XML_VERSION = '1.0';  //note i use xml version 1.0, syntax for 2.0 i not family with in current time, so it will not work if change to 2.0
    /**
     * XML encoding
     */
    const XML_ENCODING= 'UTF-8';
    /**
     * wxr data
     * @var null
     */
    public $xml_data = null;
    /**
     * avaiable namespaces
     * @var null
     */
    public $namespaces = null;
    /**
     * module object
     * @var null
     */
    protected $module = null;
    /**
     * HWIE_Posts_Group
     * @var
     */
    public $posts;
    /**
     * HWIE_Options_Group
     * @var
     */
    public $options;
    /**
     * @var
     */
    public $widgets;
    /**
     * HWIE_Skins_Group
     * @var
     */
    public $skins;
    /**
     * main class constructor
     * @param $module
     */
    public function __construct($module = null) {
        parent::__construct();
        if($module instanceof HW_Module) $this->module = $module;
        $this->posts = new HWIE_Posts_Group($this);  //posts
        $this->options = new HWIE_Options_Group($this);  //options
        $this->skins = new HWIE_Skins_Group($this);  //skins mananger
        $this->widgets = new HWIE_Widgets_Group($this);  //widgets data
    }

    /**
     * return current module object
     */
    public function get_module() {
        return $this->module;
    }
    /**
     * export to wxr file
     * @param $args
     */
    public function export($args = array()) {
        global $wpdb, $post;

        $defaults = array( 'content' => 'all', 'author' => false, 'category' => false,
            'start_date' => false, 'end_date' => false, 'status' => false,
        );
        $args = wp_parse_args( $args, $defaults );

        /**
         * Fires at the beginning of an export, before any headers are sent.
         *
         * @since 2.3.0
         *
         * @param array $args An array of export arguments.
         */
        do_action( 'export_wp', $args );

        $sitename = sanitize_key( get_bloginfo( 'name' ) );
        if ( ! empty($sitename) ) $sitename .= '.';
        $filename = $sitename . 'hoangweb.' . date( 'Y-m-d' ) . '.xml';

        header( 'Content-Description: File Transfer' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

        if ( 'all' != $args['content'] && post_type_exists( $args['content'] ) ) {
            $ptype = get_post_type_object( $args['content'] );
            if ( ! $ptype->can_export )
                $args['content'] = 'post';

            $where = $wpdb->prepare( "{$wpdb->posts}.post_type = %s", $args['content'] );
        } else {
            $post_types = get_post_types( array( 'can_export' => true ) );
            $esses = array_fill( 0, count($post_types), '%s' );
            $where = $wpdb->prepare( "{$wpdb->posts}.post_type IN (" . implode( ',', $esses ) . ')', $post_types );
        }

        if ( $args['status'] && ( 'post' == $args['content'] || 'page' == $args['content'] ) )
            $where .= $wpdb->prepare( " AND {$wpdb->posts}.post_status = %s", $args['status'] );
        else
            $where .= " AND {$wpdb->posts}.post_status != 'auto-draft'";

        $join = '';
        if ( $args['category'] && 'post' == $args['content'] ) {
            if ( $term = term_exists( $args['category'], 'category' ) ) {
                $join = "INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
                $where .= $wpdb->prepare( " AND {$wpdb->term_relationships}.term_taxonomy_id = %d", $term['term_taxonomy_id'] );
            }
        }

        if ( 'post' == $args['content'] || 'page' == $args['content'] ) {
            if ( $args['author'] )
                $where .= $wpdb->prepare( " AND {$wpdb->posts}.post_author = %d", $args['author'] );

            if ( $args['start_date'] )
                $where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date >= %s", date( 'Y-m-d', strtotime($args['start_date']) ) );

            if ( $args['end_date'] )
                $where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date < %s", date( 'Y-m-d', strtotime('+1 month', strtotime($args['end_date'])) ) );
        }

        // Grab a snapshot of post IDs, just in case it changes during the export.
        $post_ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} $join WHERE $where" );

        /*
         * Get the requested terms ready, empty unless posts filtered by category
         * or all content.
         */
        $cats = $tags = $terms = array();
        if ( isset( $term ) && $term ) {
            $cat = get_term( $term['term_id'], 'category' );
            $cats = array( $cat->term_id => $cat );
            unset( $term, $cat );
        } elseif ( 'all' == $args['content'] ) {
            $categories = (array) get_categories( array( 'get' => 'all' ) );
            $tags = (array) get_tags( array( 'get' => 'all' ) );

            $custom_taxonomies = get_taxonomies( array( '_builtin' => false ) );
            $custom_terms = (array) get_terms( $custom_taxonomies, array( 'get' => 'all' ) );

            // Put categories in order with no child going before its parent.
            while ( $cat = array_shift( $categories ) ) {
                if ( $cat->parent == 0 || isset( $cats[$cat->parent] ) )
                    $cats[$cat->term_id] = $cat;
                else
                    $categories[] = $cat;
            }

            // Put terms in order with no child going before its parent.
            while ( $t = array_shift( $custom_terms ) ) {
                if ( $t->parent == 0 || isset( $terms[$t->parent] ) )
                    $terms[$t->term_id] = $t;
                else
                    $custom_terms[] = $t;
            }

            unset( $categories, $custom_taxonomies, $custom_terms );
        }

        echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";
        the_generator( 'export' );
        include ('wxr-template.pxml');
    }

    /**
     * fetch terms data
     * @param $xml
     */
    protected function fetch_terms($xml) {
        if(empty($xml)) $xml = $this->xml_data;
        // grab cats, tags and terms
        if($this->get_terms_xml('category', $xml))
            foreach ( $this->get_terms_xml('category', $xml) as $term_arr ) {    #posts/wp:category
                $t = $term_arr->children( $this->namespaces['wp'] );
                $args = array(
                    'slug' => (string) $t->category_nicename,
                    'name' =>  (string) $t->cat_name,
                    'taxonomy' => 'category',
                    'description' => (string) $t->category_description
                );
                if(isset($t->category_parent)
                    && (count($t->category_parent[0]->children()) || count($t->category_parent[0]->xpath('hw:params'))) ) {
                    $args['parent'] = HWIE_Param::get_hw_element($t->category_parent[0], false);
                }
                else $args['parent'] = (string) $t->category_parent;
                $this->posts->add_term($args);

            }
        if( $this->get_terms_xml('post_tag', $xml))
            foreach ( $this->get_terms_xml('post_tag', $xml) as $term_arr ) { #posts/wp:tag
                $t = $term_arr->children( $this->namespaces['wp'] );
                $this->posts->add_term(array(
                    'slug' => (string) $t->tag_slug,
                    'name' => (string) $t->tag_name,
                    'taxonomy' => 'post_tag',
                    'description' => (string) $t->tag_description
                ));

            }
        if( $this->get_terms_xml('term', $xml))
            foreach ( $this->get_terms_xml('term', $xml) as $term_arr ) {    #/rss/posts/wp:term
                $t = $term_arr->children( $this->namespaces['wp'] );
                $args = array(
                    'slug' => (string) $t->term_slug,
                    'name' =>  (string) $t->term_name,
                    'taxonomy' => (string) $t->term_taxonomy,
                    'description' =>  (string) $t->term_description
                );
                if(isset($t->term_parent)
                    && ($t->term_parent[0]->children() || count($t->term_parent[0]->xpath('hw:params'))) ) {
                    $args['parent'] = HWIE_Param::get_hw_element($t->term_parent[0], false);
                }
                else $args['parent'] = (string) $t->term_parent;

                if($args['taxonomy'] =='nav_menu') $args['menu_location'] = (string) $t->menu_location; //for nav menu
                $this->posts->add_term($args);
            }
    }

    /**
     * fetch authors data
     * @param $xml
     */
    protected function fetch_authors($xml) {
        if(empty($xml)) $xml = $this->xml_data;
        if($this->get_authors_xml( $xml))
        foreach ( $this->get_authors_xml( $xml) as $author ) {
            $u = $author->children( $this->namespaces['wp'] );
            $this->posts->add_author(array(
                'login' => (string) $u->author_login,
                'email' => (string) $u->author_email,
                'display_name' => (string) $u->author_display_name,
                'first_name' => (string) $u->author_first_name,
                'last_name' => (string) $u->author_last_name,
            ));
        }

    }
    /**
     * export wxr data for the module
     * @param null $xml
     */
    public function _export_wxr_data($xml= null) {
        //update new data belong to this module into pre-shortcodes using by installer
        add_shortcode('hw_image', array($this, '_parse_image_shortcode'));
        try{
            //fetch category, tag,terms
            $this->fetch_terms($xml);
            $this->fetch_authors($xml); //fetch authors to create new
            $this->export_wxr_data($xml);
        }
        catch(Exception $e){
            HW_Logger::log_file($e->getMessage());
            HW_Logger::log_file($e->getFile().':' . $e->getLine());
            HW_Logger::log_file($e->getTraceAsString() );   //An array of the backtrace()
        }
    }
    /**
     * export wxr data for the module
     * @return mixed|void
     */
    public function export_wxr_data($xml=null){}


    /**
     * @param $value
     */
    public static function cdata($value) {
        if ( seems_utf8( $value ) == false )
            $value = utf8_encode( $value );

        // $str = ent2ncr(esc_html($str));
        return '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $value ) . ']]>';
    }
    /**
     * Wrap given string in XML CDATA tag.
     *
     * @since 2.1.0
     *
     * @param string $str String to wrap in XML CDATA tag.
     * @return string
     */
    private function wxr_cdata( $value ) {
        if(maybe_serialize($value) && is_array(unserialize($value))) { //for encrypt data
            $data = unserialize($value);
            if(is_array($data)) {
                $value = self::array_to_xml_params($data);

            }
        }
        else {
            // $str = ent2ncr(esc_html($str));
            $value = $this->cdata($value) ;
        }

        return $value;
    }
    /**
     * function defination to convert array to xml
     * @param array $data
     * @param DOMDocument $docu
     * @param bool $asHTML
     * @return string|DOMElement
     */
    public static function array_to_xml_params($data = array(), $docu =null, $asHTML=true) {
        #if(!$doc)
            $doc = new DOMDocument(self::XML_VERSION, self::XML_ENCODING) ;
        $root = $doc->createElement('hw:params');
        $doc->appendChild($root);
        self::array_to_hw_wxr($data, $root, $doc );#($doc->documentElement);
        if($asHTML) {
            return HW_XML::output_dom_to_string($doc);
        }
        else return $docu? $docu->importNode($doc->documentElement, true) : $doc->documentElement;    //wrong $doc->ownerDocument->documentElement
    }

    /**
     * convert element to wxr params
     * @param DOMElement $element
     * @param null $docu
     * @return DOMElement
     */
    public static function element_to_wxr_params(DOMElement $element, DOMDocument $docu = null) {
        if($element->tagName !== 'hw:params') {
            $doc = new DOMDocument(self::XML_VERSION, self::XML_ENCODING) ;
            $parent = $doc->createElement('hw:params');
            if($element->hasChildNodes()) {
                foreach ($element->childNodes as $item) {
                    $parent->appendChild($doc->importNode($item, true));
                }
            }
            else $parent->appendChild($doc->importNode($element, true));
        }
        else $parent = $element;
        return $docu instanceof DOMDocument? $docu->importNode($parent, true) : $parent;
    }
    /**
     * convert array to domdocument
     * @param array $data
     * @param DOMElement $element
     * @param DOMDocument $doc
     * @param string $single_tag
     * @param string $plural_tag
     */
    public static function array_to_hw_wxr($data = array(), $element = null, DOMDocument &$doc=null ) {
        if($doc == null) {
            $doc = ($element && $element instanceof DOMElement)? $element->ownerDocument : new DOMDocument(HW_Export::XML_VERSION, HW_Export::XML_ENCODING) ;
        }
        if($element == null) $element = $doc;
        elseif($element instanceof HWIE_Param) $element = $element->get();

        #if(empty($xml)) $xml = $this;
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                #$dom = new DOMDocument('1.0', 'utf-8');
                $ele=$doc->createElement('params');
                $subnode = $element->appendChild($element->ownerDocument->importNode($ele, true));#dom_import_simplexml, simplexml_import_dom
                if(!is_numeric($key)) $subnode->setAttribute('name', $key);
                self::array_to_hw_wxr($value, $subnode, $doc);
            }
            elseif(is_object($value) && ($value instanceof DOMElement || $value instanceof HWIE_Param)) {
                if($value instanceof DOMElement) $ele = $value;//->get();
                elseif($value instanceof HWIE_Param) $ele = $value->get();

                $subnode = $element->appendChild($doc->importNode($ele, true));
                if(!is_numeric($key)) $subnode->setAttribute('name', $key);
            }
            elseif( is_string($value)){
                //$xml->appendChild("$key",htmlspecialchars("$value"));
                $value_ele = $value? self::build_special_params($value, false) : false;
                if($value_ele === false ) {
                    $value_ele = $doc->createElement('param', /*self::cdata*/($value)) ;
                }
                if(!is_numeric($key)) $value_ele->setAttribute('name', $key);
                $element->appendChild($element->ownerDocument->importNode($value_ele, true));
            }
        }
        return $doc;
    }
    /**
     * @param $data
     * @param bool $str_output
     */
    protected  static function build_special_params($str = array(), $str_output=false) {
        $dom = new DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        if(HW_Encryptor::is_serialize_base64($str)) {
            $data = unserialize(base64_decode($str));
            if(is_array($data)/*is_serialized($data)*/) {
                #$data = unserialize($data);#
                //belong to skin config
                if(is_array($data) && isset($data['skin_name']) && isset($data['default_skin_path'])
                    && isset($data['apply_current_path']) && isset($data['plugin_url'])) {
                    $what_skin = rtrim($data['skin_name'], '.php'); //get skin information

                    $pskin = new HWIE_Skin_Params('skin', $what_skin);
                    $pskin->add_skin_config('hwskin_config', array(
                        'group' => !empty($data['group'])? $data['group'] : ''
                    ));
                    /*$skin_instance = $dom->createElement('params:skin_instance');
                    $skin_instance->setAttribute('name', 'hwskin_config');
                    $skin_instance->setAttribute('instance', $what_skin);
                    if(!empty($data['group'])) {
                        $skin_instance->appendChild($dom->createElement('skin:group', $data['group']));
                    }
                    $dom->appendChild($skin_instance);*/
                    $dom->appendChild($dom->importNode($pskin->get_skinconfig(0), true));
                }
            }
            elseif(is_string($data) && count(explode('|', $data)) >=8 ) {
                $skin = explode('|', $data);
                if(in_array(end($skin), array('theme','plugin','wp_content'))) {
                    $what_skin = rtrim($skin[6], '.php');
                    //create skin instance
                    $pskin = new HWIE_Skin_Params('skin', $what_skin);
                    $pskin->add_hash_skin('hash_skin', array(
                        'skin' => trim($skin[1], '\/'),
                        'skin_type' => $skin[3],
                        'source' => end($skin)
                    ));
                    /*$hash_skin = $dom->createElement('params:skin_encoded');
                    $hash_skin->setAttribute('name', 'hash_skin');
                    $hash_skin->setAttribute('instance', $what_skin);
                    //skin ns
                    $hash_skin->appendChild($dom->createElement('skin:skin', trim($skin[1], '\/')));
                    //skin_type ns
                    $hash_skin->appendChild($dom->createElement('skin:skin_type', $skin[3]));
                    $dom->appendChild($hash_skin) ;*/
                    $dom->appendChild($dom->importNode($pskin->get_hash_skin(0), true));
                }

            }
            return $str_output? HW_XML::output_dom_to_string($dom, true) : $dom->documentElement;
        }
        return false;
    }
    /**
     * Return the URL of the site
     *
     * @since 2.5.0
     *
     * @return string Site URL.
     */
    private function wxr_site_url() {
        // Multisite: the base URL.
        if ( is_multisite() )
            return network_home_url();
        // WordPress (single site): the blog URL.
        else
            return get_bloginfo_rss( 'url' );
    }

    /**
     * Output a cat_name XML tag from a given category object
     *
     * @since 2.1.0
     *
     * @param object $category Category Object
     */
    private function wxr_cat_name( $category ) {
        if ( empty( $category->name ) )
            return;

        echo '<wp:cat_name>' . wxr_cdata( $category->name ) . '</wp:cat_name>';
    }

    /**
     * Output a category_description XML tag from a given category object
     *
     * @since 2.1.0
     *
     * @param object $category Category Object
     */
    private function wxr_category_description( $category ) {
        if ( empty( $category->description ) )
            return;

        echo '<wp:category_description>' . wxr_cdata( $category->description ) . '</wp:category_description>';
    }

    /**
     * Output a tag_name XML tag from a given tag object
     *
     * @since 2.3.0
     *
     * @param object $tag Tag Object
     */
    private function wxr_tag_name( $tag ) {
        if ( empty( $tag->name ) )
            return;

        echo '<wp:tag_name>' . wxr_cdata( $tag->name ) . '</wp:tag_name>';
    }

    /**
     * Output a tag_description XML tag from a given tag object
     *
     * @since 2.3.0
     *
     * @param object $tag Tag Object
     */
    private function wxr_tag_description( $tag ) {
        if ( empty( $tag->description ) )
            return;

        echo '<wp:tag_description>' . wxr_cdata( $tag->description ) . '</wp:tag_description>';
    }

    /**
     * Output a term_name XML tag from a given term object
     *
     * @since 2.9.0
     *
     * @param object $term Term Object
     */
    private function wxr_term_name( $term ) {
        if ( empty( $term->name ) )
            return;

        echo '<wp:term_name>' . wxr_cdata( $term->name ) . '</wp:term_name>';
    }

    /**
     * Output a term_description XML tag from a given term object
     *
     * @since 2.9.0
     *
     * @param object $term Term Object
     */
    private function wxr_term_description( $term ) {
        if ( empty( $term->description ) )
            return;

        echo '<wp:term_description>' . wxr_cdata( $term->description ) . '</wp:term_description>';
    }

    /**
     * Output list of authors with posts
     *
     * @since 3.1.0
     *
     * @param array $post_ids Array of post IDs to filter the query by. Optional.
     */
    private function wxr_authors_list( array $post_ids = null ) {
        global $wpdb;

        if ( !empty( $post_ids ) ) {
            $post_ids = array_map( 'absint', $post_ids );
            $and = 'AND ID IN ( ' . implode( ', ', $post_ids ) . ')';
        } else {
            $and = '';
        }

        $authors = array();
        $results = $wpdb->get_results( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status != 'auto-draft' $and" );
        foreach ( (array) $results as $result )
            $authors[] = get_userdata( $result->post_author );

        $authors = array_filter( $authors );

        foreach ( $authors as $author ) {
            echo "\t<wp:author>";
            echo '<wp:author_id>' . $author->ID . '</wp:author_id>';
            echo '<wp:author_login>' . $author->user_login . '</wp:author_login>';
            echo '<wp:author_email>' . $author->user_email . '</wp:author_email>';
            echo '<wp:author_display_name>' . wxr_cdata( $author->display_name ) . '</wp:author_display_name>';
            echo '<wp:author_first_name>' . wxr_cdata( $author->user_firstname ) . '</wp:author_first_name>';
            echo '<wp:author_last_name>' . wxr_cdata( $author->user_lastname ) . '</wp:author_last_name>';
            echo "</wp:author>\n";
        }
    }

    /**
     * Ouput all navigation menu terms
     *
     * @since 3.1.0
     */
    private function wxr_nav_menu_terms() {
        $nav_menus = wp_get_nav_menus();
        if ( empty( $nav_menus ) || ! is_array( $nav_menus ) )
            return;

        foreach ( $nav_menus as $menu ) {
            echo "\t<wp:term><wp:term_id>{$menu->term_id}</wp:term_id><wp:term_taxonomy>nav_menu</wp:term_taxonomy><wp:term_slug>{$menu->slug}</wp:term_slug>";
            wxr_term_name( $menu );
            echo "</wp:term>\n";
        }
    }

    /**
     * Output list of taxonomy terms, in XML tag format, associated with a post
     *
     * @since 2.3.0
     */
    private function wxr_post_taxonomy() {
        $post = get_post();

        $taxonomies = get_object_taxonomies( $post->post_type );
        if ( empty( $taxonomies ) )
            return;
        $terms = wp_get_object_terms( $post->ID, $taxonomies );

        foreach ( (array) $terms as $term ) {
            echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . wxr_cdata( $term->name ) . "</category>\n";
        }
    }
    /**
     * get terms by xml
     * @param $tax
     * @param null $xml
     */
    private function get_terms_xml($tax='category', $xml = null) {
        if($xml == null) $xml = $this->xml_data ;
        $module = $this->get_module()->option('module_name');
        if($tax == 'category') $tag = 'wp:category';
        elseif($tax == 'post_tag') $tag = 'wp:tag';
        else $tag = 'wp:term';

        return $xml->xpath('/rss/hw:'.$module.'/posts/'. $tag);
    }

    /**
     * get authors data by xml
     * @param null $xml
     */
    public function get_authors_xml($xml= null) {
        if($xml == null) $xml = $this->xml_data ;
        $module = $this->get_module()->option('module_name');
        return $xml->xpath('/rss/hw:'.$module.'/posts/wp:author');
    }
}

/**
 * Class HW_Module_Export
 */
abstract class HW_Module_Export extends HW_Export {
    /**
     * default skins data
     * @var
     */
    private static $default_skins;
    /**
     * @var
     */
    protected $export_data;
    /**
     * widgets data
     * @var
     */
    protected $export_widgets;
    /**
     * @var null
     */
    public $importer = null;
    /**
     * extensions
     * @var array
     */
    private $extensions = array();
    /**
     * class constructor
     * @param string $module
     */
    public function __construct($module) {
        parent::__construct($module);

        $this->export_data = new DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        $this->export_widgets = HWIE_Param::new_root_doc('widgets', null ,false);
        $this->importer = new HW_Import($this);//HW_Import::get_instance($this );  //create importer instance for each module export class
        //load skins
        #$this->load_skins();
        $this->load_module_skins();

        //preload data
        //do not load all widgets because no enough skins data and this should load separate in method of add_export_widgets for each module
        #$this->load_widgets();

        #$this->add_export_widgets();   //sory you should include in method of module export for 'export_wxr_data'
        //set parser data, use in pre-shortcode... ($this->importer->parser-> = $this->)
        $this->update_variables(array(
            'import_path1' => $module->get_module_file_url(''),  //or $module->option('module_url')
            'import_dir1' => $module->option('module_path'),

            'import_path' => get_stylesheet_directory_uri(). '/modules/'.$module->option('module_name'),
            'import_dir' => get_stylesheet_directory() . '/modules/' .$module->option('module_name')
        ));

    }

    /**
     * get result content
     * @param $item
     * @deprecated deprecated
     */
    public function get_result_content($item) {
        $module_ie = new HWIE_Module_Import_Results($item);
        $module_ie->init($this);
        return $module_ie->parse_data()->value;
    }

    /**
     * read options from wxr format
     * @param $xml
     * @return array
     */
    public function recursive_option_data($xml) {
        $skins_data = HWIE_Param::dom_to_simplexml($this->skins->documentElement,'SimpleXMLElement', 'params', 0); //get skins data for the module
        $this->simplexml_parser->gather_skins_data($skins_data);  //preload some data before parse options from wxr
        //HW_Logger::log_file($this->simplexml_parser->skins_data);
        return $this->simplexml_parser->recursive_option_data($xml);
    }

    /**
     * get import result
     * @param $import_item
     * @param $type accept value in post,term,attachment
     * @return object|string
     * @example ->get_import_result(array('name'=> 'ax', 'filter'=>'callback'), 'post')
     * @example ->get_import_result('ax')
     * @example ->get_import_result($item->children())  //DOMElement/SimpleXMLElement
     */
    public function get_import_result($import_item, $type='post') {
        $tags = array('post' => 'hw:import_post', 'term' => 'hw:import_term','attachment' => 'hw:attachment');
        if((is_array($import_item) || is_string($import_item))  ) {
            if($type =='') $type = 'post';  //default get import post
            if(is_string($import_item)) $import_item = array('name' => $import_item);
            if(!is_array($import_item) || (!isset($import_item['name']) && !isset($import_item['_id'])) ) { //invalid arguments
                return ;
            }
            if(isset($tags[$type])) $parse = new HWIE_Module_Import_Results($import_item, $tags[$type]);
        }
        elseif($import_item instanceof SimpleXMLElement || $import_item instanceof DOMElement) {
            if($import_item instanceof SimpleXMLElement) $import_item = dom_import_simplexml($import_item);
            $parse = new HWIE_Module_Import_Results($import_item);
        }
        if(isset($parse)) {
            $parse->init($this);
            return $parse->parse_data()->value;
        }
    }
    /**
     * import data
     * @param $extra
     */
    public function do_import($extra = null) {
        #$importer = HW_Import::get_instance();  //create importer instance
        $root = HWIE_Param::new_root_doc('rss', array('version' => '2.0') ,true);
        $xml = HW_XML::mergeDom(array($this->skins, $this->posts, $this->options,$this->export_data,$this->export_widgets, $extra), $root);
        //HW_Logger::log_file(HW_XML::output_dom_to_string($xml));
        $this->importer->import_file($xml);
    }

    /**
     * run cli command
     * @param string $name
     * @param int $param_id
     * @param string $command
     */
    public function run_cli($name = '', $param_id= 0, $command='') {
        $this->get_module()->get_config()->run_cli_cmd($name, $param_id, $command);
    }
    /**
     * add export widgets for the module
     */
    public  function add_export_widgets() {
        $theme_config = HW__Template::get_theme_config();
        if($theme_config && $theme_config->item('sidebars')) {
            foreach($theme_config->item('sidebars') as $sidebar => $item) {
                if(isset($item['widgets']))
                foreach($item['widgets'] as $widget) {
                    $this->load_widgets($widget);   //init widget
                    $wxr_widget = $this->widgets->get($widget);
                    if(!$wxr_widget) continue;  //widget not exists
                    $wxr_widget->get()->setAttribute('sidebar' , $sidebar);    //add widget to sidebar
                    if($this->widgets->get($widget)) $this->add_export($wxr_widget->get());
                }
            }
        }
    }
    /**
     * @param $data
     */
    public function add_export($data) {
        HW_XML::mergeDom( array($data), $this->export_widgets);
    }

    /**
     * check whether widget is allow to import
     * @param $id_base
     */
    public function check_allow_widgets($id_base) {
        $allow_widgets = array(
            'nav_menu', 'search','meta','calendar','categories' ,'recent-posts','rss',
            'archives','tag_cloud','recent-comments','pages','text'
        );
        $list_base = $this->get_module()->option('widgets_base');
        if(is_array($list_base)) $allow_widgets = array_merge($allow_widgets,$list_base);

        return in_array($id_base, $allow_widgets);
    }
    /**
     * load widgets data specific for module
     * @param $widget specific widget name for preloading
     */
    protected function load_widgets($widget='') {
        //$list_base = $this->get_module()->option('widgets_base');
        //load widgets data
        if($this->widgets->load_widgets())
        foreach($this->widgets->load_widgets() as $item) {
            $atts = $item->attributes();
            $id_base = (string) $atts['id_base'];
            $active = isset($atts['active'])? (string) $atts['active'] : 1;
            if(!$this->check_allow_widgets($id_base) || !$active) continue;

            $name = (string) $atts['name'];
            if($widget && $widget !==$name) continue;
            //$this->simplexml_parser->recursive_option_data($item->params[0]->children()); //other method
            $hw = $item->children($this->namespaces['hw']);
            //$params = HWIE_Param::get_hw_element($item->params[0]);
            #$params=$this->recursive_option_data($item->params)->option;
            $params =$item->xpath('hw:params');
            #if($params) $this->recursive_option_data($params[0]->children())->option;
            if($params) {
                $widget_instance = $this->recursive_option_data($params[0]->children())->option;    //not sure to work properly :$params[0]->children()
                $this->widgets->add_widget($name, $widget_instance, $id_base);
            }
        }
    }

    /**
     * get skins data
     * @return mixed
     */
    public function get_skins() {
        return self::$default_skins;
    }
    /**
     * load skins data
     */
    protected function load_skins() {
        if(self::$default_skins) {
            $this->skins = self::$default_skins; //merge default skins
            return;
        }
        //load skins data
        foreach($this->skins->load_skins() as $item) {
            $atts = $item->attributes();
            $name = (string) $atts['name'];
            //$skin = $item->children($this->namespaces['skin']);
            $skin = HW_WXR_Parser_SimpleXML::parse_skin_data($item);

            $this->skins->add_skin($name, $skin );
        }
        self::$default_skins = $this->skins;
    }

    /**
     * load module skins
     */
    protected function load_module_skins() {
        $skins_file = $this->get_module()->get_module_wxr_files('skins');
        if($skins_file) {

            $skins = HW_WXR_Parser_SimpleXML::read_simplexml_object($skins_file);
            if(!is_wp_error($skins) && !empty($skins) && isset($skins->xml))
            foreach($skins->xml->xpath('/rss/skins/hw:skin') as $item) {
                $name = (string) $item->attributes()->name;
                $skin = HW_WXR_Parser_SimpleXML::parse_skin_data($item);    //parse hw skin data
                if(!isset($skin['apply_plugin'])) $skin['apply_plugin'] = $this->get_module()->option('module_name');   //fix apply_module to current
                $this->skins->add_skin($name, $skin );
            }
        }
    }
    /**
     * add widgets base for the module
     */
    public function add_module_widgets() {
        $args = func_get_args();
        $args = hwArray::multi2single($args);
        foreach ($args as $id) {
            $this->get_module()->option('widgets_base', $id ,true);
        }

    }

    /**
     * fetch post metas element from xml
     * @param $xml
     * @return array
     */
    public function fetch_post_metas($xml) {
        //post meta
        $post_metas = array();
        if(!empty($xml) /*&& $xml instanceof SimpleXMLElement*/)
        foreach($xml as $meta) {
            $meta_key = (string)$meta->attributes()->name;
            if(count($meta->children())) {
                $meta_value= self::element_to_wxr_params(dom_import_simplexml($meta));
            }
            else $meta_value = (string) $meta;
            $post_metas[$meta_key] = $meta_value;
        }
        return $post_metas;
    }

    /**
     * get post meta values
     * @param $xml
     * @return array
     */
    public function fetch_post_metas_value($xml) {
        //post meta
        $post_metas = array();
        if(!empty($xml))
        foreach($xml as $meta) {
            $meta_key = (string) $meta->attributes()->name;
            if(count($meta->children())) $meta_value = $this->recursive_option_data($meta->children())->option;
            else $meta_value = (string) $meta;
            $post_metas[$meta_key] = $meta_value;
        }
        return $post_metas;
    }

    /**
     * fetch attachments for post
     * @param $xml
     */
    public function get_atachments($xml) {
        $data = array();
        if(!empty($xml))
        foreach ($xml as $item) {
            if($item->tagName =='attachment') $data[] = dom_import_simplexml($item);
        }
        return $data;
    }

    /**
     * load extension
     * @param $class
     * @param $data
     */
    final public function load_extension($class, $data = '') {
        if(class_exists($class) && !isset($this->extensions[$class])) {
            $this->extensions[$class] = new $class($this);
            $this->extensions[$class]->init($data);
        }
        if(isset($this->extensions[$class])) return $this->extensions[$class];
    }

    /**
     * fetch skins
     * @param $skins
     * @param string $fhash_skin
     * @param string $fconfig_name
     * @param array $extra
     * @param bool $compac_skin
     */
    public function fetch_skins($skins, $fhash_skin ='hash_skin', $fconfig_name ='hwskin_config', $extra = array(), $compac_skin= true) {
        $result = array();
        if(!empty($skins)) {
            foreach($skins as $skin) {
                $name = (string)$skin->attributes()->name;
                $_skin = $skin->children($this->namespaces['skin']);
                $skin_type = !empty($_skin->skin_type)? (string) $_skin->skin_type : 'file';
                //skin
                $pskin = new HWIE_Skin_Params(($name? $name : 'skin'), (string) $_skin->instance);
                //skins data for current module
                $pskin->skins_data = HWIE_Param::dom_to_simplexml($this->skins->documentElement,'SimpleXMLElement', 'params');

                $pskin->add_hash_skin($fhash_skin, array(
                    'skin' => (string)$_skin->skin_name,
                    'file' => (string) $_skin->file,
                    'source' => (string)$_skin->source,
                    'group' => (string) $_skin->group,
                    'skin_type' => $skin_type
                ));
                #$pskin->add_skin_config();
                $pskin->add_skin_config($fconfig_name, array('group' => (string) $_skin->group));
                $extra_params = array(
                    'hwskin_condition' => "",
                    'skin_options' => array(
                        'enqueue_css_position' => 'footer',
                        'enqueue_js_position' => 'footer'
                    )
                );
                if($skin_type == 'link') $pskin->add_skin_file('url', array('file' => (string) $_skin->file));
                if(!empty($_skin->params)) {
                    $skin_params = $this->simplexml_parser->recursive_option_data($_skin->params->children())->option;
                    if(!empty($skin_params)) $extra_params = array_merge($extra_params, $skin_params);
                }
                if(is_array($extra) && count($extra)) $extra_params = array_merge($extra_params, $extra);
                $pskin->extra_params($extra_params );

                $result[$name] = $compac_skin? $pskin->get_skin(false) : $pskin;
                #$pskin->parse_skin_values();
            }
        }
        return $result;
    }

    /**
     * extract hw_params element, ie import result item
     * @param $xml
     * @param $tag
     * @param bool $castStr
     * @return DOMElement
     */
    public function get_hw_params_element($xml, $tag='hw:params', $castStr=false) {
        if($xml && ($xml->xpath($tag))) {
            $item = $xml->xpath($tag);
            foreach($xml->xpath($tag) as $item){break;};
            return $this->convert_element($item);
        }
        elseif($castStr) return (string) $xml;
    }

    /**
     * @param $item
     * @return HWIE_Param|null
     */
    public function ie_param_object($item ) {
        $p = new HWIE_Param(0, array(), 'params');
        $p->add_child($item);
        return $p->get();
    }
    /**
     * convert xml to valid element known as domelement
     * @param $item
     * @return DOMElement|HWIE_Param
     */
    public function convert_element($item) {
        return HWIE_Param::get_hw_element($item, false);
    }
    /**
     * get posts in xml
     * @param SimpleXMLElement $xml
     * @return mixed
     */
    public function get_posts_xml($xml = null) {
        if($xml == null) $xml = $this->xml_data ;
        $module = $this->get_module()->option('module_name');
        return $xml->xpath('/rss/hw:'.$module.'/posts/item');
    }

    /**
     * @param null $xml
     * @return mixed
     */
    public function get_options_xml($xml = null) {
        if($xml == null) $xml = $this->xml_data ;
        $module = $this->get_module()->option('module_name');
        return $xml->xpath('/rss/hw:'.$module.'/options/option');
    }

    /**
     * widgets data
     * @param null $xml
     */
    public function get_widgets_xml($xml = null) {
        if($xml == null) $xml = $this->xml_data ;
        $module = $this->get_module()->option('module_name');
        return $xml->xpath('/rss/hw:'.$module.'/widgets/hw:widget');
    }

    /**
     * skins data
     * @param null $xml
     * @return mixed
     */
    public function get_skins_xml($xml = null) {
        if($xml == null) $xml = $this->xml_data ;
        $module = $this->get_module()->option('module_name');
        return $xml->xpath('/rss/hw:'.$module.'/skins/hw:skin');
    }
}

/**
 * Interface HW_Module_Export_Extension_Interface
 */
interface HW_Module_Export_Extension_Interface {
    /**
     * @param $data
     * @return mixed
     */
    public function init($data);
}
/**
 * Class HW_Module_Export_Extension
 */
abstract class HW_Module_Export_Extension implements HW_Module_Export_Extension_Interface{
    /**
     * HW_Module_Export
     * @var HW_Module_Export
     */
    public  $module_export;

    /**
     * @param $module_export
     */
    public function __construct(HW_Module_Export $module_export) {
        if($module_export instanceof HW_Module_Export) $this->module_export = $module_export;
    }
    public function get() {
        return $this->module_export;
    }

    /**
     * @param $data
     */
    final public function _init($data) {
        $this->init($data);
    }
}