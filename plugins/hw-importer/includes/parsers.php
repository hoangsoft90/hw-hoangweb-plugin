<?php
/**
 * WordPress eXtended RSS file parser implementations
 *
 * @package WordPress
 * @subpackage Importer
 */
include_once ('wxr-data-type.php');
/**
 * WordPress Importer class for managing parsing of WXR files.
 * @extend from wordpress-importer plugin
 */
if(!class_exists('HW_WXR_Parser')):
class HW_WXR_Parser extends HW_Core{
    /**
     * singleton
     * @var
     */
    public static $instance;
    /**
     * HW_WXR_Parser_SimpleXML
     * @var
     */
    public $simplexml_parser;
    /**
     * HW_WXR_Parser_XML
     * @var
     */
    public $xml_parser;
    /**
     * @var array
     */
    public  $data = array();
    /**
     * shortcodes used to import data
     * @var array
     */
    var $import_shortcodes = array('hw_image', 'hw_url');
    /**
     * HW_Import
     * @var null
     */
    public $importer =null;  //note: this member same name in HW_Export class
    /**
     * @param null $importer
     */
    public function __construct($importer = NULL) {
        if($importer) $this->importer = $importer;
        $this->simplexml_parser = new HW_WXR_Parser_SimpleXML($this);
        $this->xml_parser = new HW_WXR_Parser_XML($this);
        $this->xml_regex = new  HW_WXR_Parser_Regex($this);
        //register shortcodes
        add_shortcode('hw_image', array(&$this, '_parse_image_shortcode'));
        add_shortcode('hw_url', array(&$this, '_parse_internal_url_shortcode'));
        foreach($this->import_shortcodes as $sc) {
            add_filter('shortcode_atts_'. $sc, array($this, '_shortcode_atts') ,10,3);
        }
    }


    /**
     * @param $file
     * @param $num_posts
     * @param $page
     * @return array|WP_Error
     */
    public function parse( $file ,$num_posts=0, $page=0) {
		// Attempt to use proper XML parsers first
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = $this->simplexml_parser;//new HW_WXR_Parser_SimpleXML;
			$result = $parser->parse( $file , $num_posts, $page);

			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'SimpleXML_parse_error' != $result->get_error_code() )
				return $result;
		} else if ( extension_loaded( 'xml' ) ) {
			$parser = $this->xml_parser;//new HW_WXR_Parser_XML;
			$result = $parser->parse( $file );

			// If XMLParser succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'XML_parse_error' != $result->get_error_code() )
				return $result;
		}

		// We have a malformed XML file, so display the error and fallthrough to regex
		if ( isset($result) && defined('IMPORT_DEBUG') && IMPORT_DEBUG ) {
			echo '<pre>';
			if ( 'SimpleXML_parse_error' == $result->get_error_code() ) {
				foreach  ( $result->get_error_data() as $error )
					echo $error->line . ':' . $error->column . ' ' . esc_html( $error->message ) . "\n";
			} else if ( 'XML_parse_error' == $result->get_error_code() ) {
				$error = $result->get_error_data();
				echo $error[0] . ':' . $error[1] . ' ' . esc_html( $error[2] );
			}
			echo '</pre>';
			echo '<p><strong>' . __( 'There was an error when reading this WXR file', 'wordpress-importer' ) . '</strong><br />';
			echo __( 'Details are shown above. The importer will now try again with a different parser...', 'wordpress-importer' ) . '</p>';
		}

		// use regular expressions if nothing else available or this is bad XML
		return $this->xml_regex->parse( $file );
	}
    /**
     * pre shortcode tags by hoangweb
     * @param $content
     */
    public function pre_shortcode_tags($content) {
        if(has_shortcode($content, 'hw_image') || has_shortcode($content, 'hw_url')) {
            $content = do_shortcode($content);
        }
        return $content;
    }
    /**
     * parse image from post content
     * @shortcode hw_image
     * @param $atts
     * @param $content
     */
    public function _parse_image_shortcode($atts, $content) {
        $atts=shortcode_atts(array( //parse attributes
            //'foo' => 'no foo',
        ), $atts, 'hw_image');

        $src = isset($atts['src'])? $atts['src'] : $content;    //HW_Logger::log_file('shortcode:'.$this->data('import_path'));

        $src = $this->get_file_url($src,  HW_HOANGWEB_IMAGES. '/placeholder.png');

        $split_fname = array_filter(preg_split('%[/\\\]%', $src));
        $split_fname = HW_File_Directory::get_filename_withoutExt(end($split_fname));

        //if(!HW_URL::valid_url($src)) $src = HW_File_Directory::generate_url($this->data('import_path'), $src);

        return sprintf('<a href="%s"><img class="alignnone wp-image" src="%s" alt="%s" /></a>', $src, $src, $split_fname);
    }

    /**
     * parse file url from module config
     * @param $atts
     * @param $content
     */
    public function _parse_internal_url_shortcode($atts, $content) {
        $atts=shortcode_atts(array( //parse attributes
            //'foo' => 'no foo',
        ), $atts, 'hw_url');
        $file = isset($atts['file'])? $atts['file'] : $content; //get file name

        return $this->get_file_url($file);
    }

    /**
     * valid file url
     * @param $file
     * @param $default
     * @return string
     */
    protected function get_file_url($file, $default='') {
        if($this->data('import_dir') && !HW_URL::valid_url($file) && file_exists($this->data('import_dir'). '/'. $file)) {
            $file= HW_File_Directory::generate_url($this->data('import_path'), $file);
        }
        elseif($this->data('import_dir1') && !HW_URL::valid_url($file) && file_exists($this->data('import_dir1'). '/'. $file)) {
            $file = HW_File_Directory::generate_url($this->data('import_path1'), $file);
        }
        elseif(!HW_URL::valid_url($file) && $default) $file = $default; //default file
        return $file;
    }
    /**
     * @param $out
     * @param $pairs
     * @param $atts
     * @hook shortcode_atts
     */
    public function _shortcode_atts($out, $pairs, $atts) {
        //$out['paths'] = $this->data;
        return $out;
    }

    /**
     * @param $data
     */
    public function update_variables($data) {
        if(!empty($data)) $this->_option('data', (array)$data, true) ;
        //update shortcode callback from this point to update new data use in shortcode callback
        add_shortcode('hw_image', array($this, '_parse_image_shortcode'));
    }

    /**
     * return data item
     * @param $key
     * @param $default
     * @return mixed
     */
    public function data($key, $default='') {
        $data = $this->_get_option('data', array());
        return isset($data[$key])? $data[$key] : $default;
    }

    /**
     * fetch item id from import result
     * @param array $item
     */
    public static function get_import_id($item) {
        if(isset($item['_id'])) $key = $item['_id'];   //<hw:attachment><hw:_id></hw:_id>...
        elseif(!empty($item['hw_attributes']['name'])) {
            $key = $item['hw_attributes']['name'];
        }
        elseif($item['post_title']) $key = sanitize_title($item['post_title']); //for post
        //category/tag/term
        elseif(isset($item['slug'])) $key = $item['slug'];
        //for post
        elseif(!empty($item['post_id'])) $key = intval($item['post_id']);   //otherwise get id but not recommended

        return isset($key)? $key : md5(serialize($item));
    }
}
endif;
/**
 * WXR Parser that makes use of the SimpleXML PHP extension.
 */
if(!class_exists('HW_WXR_Parser_SimpleXML')):
class HW_WXR_Parser_SimpleXML {
    /**
     * @var
     */
    private $namespaces = null;
    /**
     * @var
     */
    protected $skins_data = array();
    /**
     * HW_WXR_Parser
     * @var
     */
    var $parser;
    /**
     * @var array
     */
    public $import_results = array();
    /**
     * @param $wxr_parser
     */
    public function __construct(HW_WXR_Parser $wxr_parser=null) {
        $this->parser = $wxr_parser;
    }
    /**
     * read xml from string
     * @param $file
     * @return SimpleXMLElement|WP_Error
     */
    public static function convert_simplexml($file) {
        $dom = new DOMDocument;
        $success = $dom->loadXML( file_exists($file)?  file_get_contents( $file ): $file);
        if ( ! $success || isset( $dom->doctype ) ) {
            return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'wordpress-importer' ), libxml_get_errors() );
        }
        $xml = simplexml_import_dom( $dom );
        unset( $dom );
        return $xml;
    }

    /**
     * cast to simplexmlelement object
     * @param $file
     * @param bool $break_error
     * @return WP_Error
     */
    public static function read_simplexml_object($file, $break_error=true) {
        $internal_errors = libxml_use_internal_errors(true);
        $old_value = null;
        if ( function_exists( 'libxml_disable_entity_loader' ) ) {
            $old_value = libxml_disable_entity_loader( true );
        }
        if($file instanceof SimpleXMLElement) {
            $xml = $file;
        }
        elseif($file instanceof DOMDocument) {
            #$xml = simplexml_import_dom($file, 'SimpleXMLElement');
            //safe way
            $xml = simplexml_load_string($file->saveXML(), 'SimpleXMLElement', LIBXML_NOCDATA); //third param can be: LIBXML_NOCDATA | LIBXML_NOBLANKS
        }
        elseif(is_string($file)) {
            $dom = new DOMDocument;
            if(file_exists($file)) $xml_content = file_get_contents( $file );
            elseif(is_string($file)) $xml_content = $file;

            $success = $xml_content? $dom->loadXML( $xml_content ) : false;
            if ( ! $success || isset( $dom->doctype ) ) {
                HW_Logger::log_file(libxml_get_errors());   //log errors
                if($break_error) return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'hw-importer' ), libxml_get_errors() );
                else return false;
            }

            $xml = simplexml_import_dom( $dom );
            unset( $dom );
        }
        if ( ! is_null( $old_value ) ) {
            libxml_disable_entity_loader( $old_value );
        }
        // halt if loading produces an error
        if ( empty( $xml) ) {
            if($break_error) return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'hw-importer' ), libxml_get_errors() );
            else return false;
        }

        //store namespace
        $namespaces = self::valid_namespaces($xml);

        return (object)array('xml' => $xml, 'namespaces' => $namespaces);
    }

    /**
     * valid simpleXMLElement namespaces
     * @param array|null $xml
     * @param $ns
     * @return array|string|null
     */
    public static function valid_namespaces($xml= null, $ns='') {
        if(is_string($xml)) $xml = self::read_simplexml_object($xml)->xml;
        $namespaces = $xml instanceof SimpleXMLElement? $xml->getDocNamespaces() : array();
        if ( ! isset( $namespaces['wp'] ) )
            $namespaces['wp'] = 'http://wordpress.org/export/1.2/';
        if ( ! isset( $namespaces['dc'] ) )
            $namespaces['dc'] = 'http://purl.org/dc/elements/1.1/';

        if ( ! isset( $namespaces['wfw'] ) )
            $namespaces['wfw'] = 'http://wellformedweb.org/CommentAPI/';

        if ( ! isset( $namespaces['hw'] ) )
            $namespaces['hw'] = 'http://hoangweb.com/export/1.0/';
        if(!isset($namespaces['skin'])) $namespaces['skin'] = 'http://hoangweb.com/export/1.0/skin/';
        if(!isset($namespaces['param'])) $namespaces['param'] = 'http://hoangweb.com/export/1.0/param/';
        if(!isset($namespaces['params'])) $namespaces['params'] = 'http://hoangweb.com/export/1.0/params/';

        if ( ! isset( $namespaces['excerpt'] ) )
            $namespaces['excerpt'] = 'http://wordpress.org/export/1.2/excerpt/';
        if ( ! isset( $namespaces['content'] ) )
            $namespaces['content'] = 'http://purl.org/rss/1.0/modules/content/';

        return  $ns ? (isset($namespaces[$ns])? $namespaces[$ns] : '') : $namespaces;
    }

    /**
     * @param string|SimpleXMLElement $file
     * @param $num_posts split posts into segments
     * @return array|WP_Error
     */
    public function parse( $file ,$num_posts=0, $page=0) {
		$authors = $posts = $categories = $tags = $terms = $options = array();
        //$skins_instances = array();
        //parse paths
        $paths = array();
        if(is_string($file)) {
            if(!$this->parser->data('import_path')) {
                $paths['import_path'] = rtrim(HW_URL::get_path_url($file, true),'\/'). '/';
            }
            if(!$this->parser->data('import_dir')) $paths['import_dir'] = dirname($file);
            $this->parser->update_variables( $paths);
        }
        $xml = self::read_simplexml_object($file);
        $namespaces = $xml->namespaces;
        $xml = $xml->xml;

		$wxr_version = $xml->xpath('posts/wp:wxr_version');    #/rss/posts/wp:wxr_version
        /*if ( ! $wxr_version )
            return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );

        $wxr_version = (string) trim( $wxr_version[0] );
        // confirm that we are dealing with the correct file format
        if ( ! preg_match( '/^\d+\.\d+$/', $wxr_version ) )
            return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );
*/
		$base_url = $xml->xpath('posts/wp:base_site_url'); #/rss/posts/wp:base_site_url
		$base_url = isset($base_url[0])? (string) trim( $base_url[0] ) : '';

        //$x=simplexml_load_string($file->saveXML(), 'SimpleXMLElement');
        //$p=$x->posts->item[0];__print((string)$p->children('http://wordpress.org/export/1.2/')->post_type);

        //store namespace
        $this->namespaces = $namespaces;

        //gather skins instances
        $this->gather_skins_data($xml, $namespaces);
        //grab widgets data
        $widgets = $this->grab_widgets($xml , $namespaces);
        $sidebars = $this->grab_sidebars($xml , $namespaces);

		// grab authors
        if($xml->xpath('posts/wp:author'))
		foreach ( $xml->xpath('posts/wp:author') as $author_arr ) {    #/rss/posts/wp:author
			$a = $author_arr->children( $namespaces['wp'] );
			$login = (string) $a->author_login;
			$authors[$login] = array(
				'author_id' => (int) $a->author_id, //for import id, but we don't use
				'author_login' => $login,
				'author_email' => (string) $a->author_email,
				'author_display_name' => (string) $a->author_display_name,
				'author_first_name' => (string) $a->author_first_name,
				'author_last_name' => (string) $a->author_last_name
			);
		}

		// grab cats, tags and terms
        if($xml->xpath('posts/wp:category'))
		foreach ( $xml->xpath('posts/wp:category') as $term_arr ) {    #/rss/posts/wp:category
            $atts= $term_arr->attributes();
            $t = $term_arr->children( $namespaces['wp'] );

            if(isset($atts['_id'])) $key = (string) $atts['_id'];
            else $key = (string) $t->category_nicename;
            $args = array(
                '_id' => $key,
                'term_id' => (int) $t->term_id,
                'category_nicename' => (string) $t->category_nicename,
                'category_parent' => (string) $t->category_parent,
                'cat_name' => (string) $t->cat_name,
                'category_description' => $this->parser->pre_shortcode_tags((string) $t->category_description)
            );
            if(isset($t->category_parent) && count($t->category_parent[0]->xpath('hw:params')) ) {
                $parent = $this->recursive_option_data($t->category_parent[0]->xpath('hw:params'));    //purpose of import result by hoangweb
                /*if(!empty($parent->attributes['export']) && is_array($parent->option[0])) {
                    $export_path = (string) $parent->attributes['export'];
                    $export_path = hwArray::drwOfPath(explode('/', $export_path));
                    eval('$parent = $parent->option[0]'.$export_path.';');
                }
                elseif(!empty($parent->option[0])) {
                    $parent = $parent->option[0];
                }*/
                if(isset($parent->option[0])) {
                    $args['category_parent'] = $parent->option[0];
                }
            }
			$categories[] = $args;
		}
        if( $xml->xpath('posts/wp:tag'))
		foreach ( $xml->xpath('posts/wp:tag') as $term_arr ) { #/rss/posts/wp:tag
            $atts= $term_arr->attributes();
			$t = $term_arr->children( $namespaces['wp'] );

            if(isset($atts['_id'])) $key = (string) $atts['_id'];
            else $key = (string) $t->tag_slug;

			$tags[] = array(
                '_id' => $key,
				'term_id' => (int) $t->term_id,
				'tag_slug' => (string) $t->tag_slug,
				'tag_name' => (string) $t->tag_name,
				'tag_description' => $this->parser->pre_shortcode_tags((string) $t->tag_description)
			);
		}
        if( $xml->xpath('posts/wp:term'))
		foreach ( $xml->xpath('posts/wp:term') as $term_arr ) {    #/rss/posts/wp:term
            $atts= $term_arr->attributes();
            $t = $term_arr->children( $namespaces['wp'] );

            if(isset($atts['_id'])) $key = (string) $atts['_id'];
            else $key = (string) $t->term_slug;
            $args = array(
                '_id' => $key,
                'term_id' => (int) $t->term_id,
                'term_taxonomy' => (string) $t->term_taxonomy,
                'slug' => (string) $t->term_slug,
                'term_parent' => (string) $t->term_parent,
                'term_name' => (string) $t->term_name,
                'term_description' => $this->parser->pre_shortcode_tags((string) $t->term_description),
            );
            if(isset($t->term_parent) && count($t->term_parent[0]->xpath('hw:params')) ) {
                $parent = $this->recursive_option_data($t->term_parent[0]->xpath('hw:params'));    //purpose of import result by hoangweb
                if(!empty($parent->option[0])) {
                    $args['term_parent'] = $parent->option[0];
                }
            }

            if($args['term_taxonomy'] =='nav_menu') $args['menu_location'] = (string)$t->menu_location; //for nav menu
			$terms[] = $args;
		}
        $start = $end= 0;
        if($num_posts!=0 && isset($xml->posts->item)) {
            $segments = hwArray::split_loop_segments($num_posts, $xml->posts->item->count());
            if(is_numeric($page) && isset($segments[$page])) {
                $range = explode('-',$segments[$page] );
                $start= $range[0];
                $end = $range[1];
            }
        }
        elseif(isset($xml->posts->item)) {
            $start=0;
            $end = $xml->posts->item->count();
        }
        $count=0;
        //$end = isset($xml->posts->item)? $xml->posts->item->count() : 0;
		// grab posts
        if(isset($xml->posts->item))
		foreach ( $xml->posts->item as  $item ) {
            if($start > ++$count) continue;
            if($count && $end+1 < $count) break;


            //by hoang
            $atts = (array)$item->attributes();
            $atts = isset($atts['@attributes'])? $atts['@attributes']: array();   //cast to array

			$post = array(
				'post_title' => (string) $item->title,
				'guid' => (string) $item->guid,
                'hw_attributes' => !empty($atts)? $atts : array()
			);

			$dc = $item->children( /*'http://purl.org/dc/elements/1.1/'*/$namespaces['dc'] );
			$post['post_author'] = (string) $dc->creator;

			$content = $item->children( $namespaces['content']/*'http://purl.org/rss/1.0/modules/content/'*/ );
			$excerpt = $item->children( $namespaces['excerpt'] );
            if(isset($content->encoded) && count($content->encoded[0]->xpath('hw:params')) ) {
                $content_encoded = $this->recursive_option_data($content->encoded[0]->xpath('hw:params'));    //purpose of import result by hoangweb
                if(!empty($content_encoded->option[0])) {
                    $content_encoded = $content_encoded->option[0];
                }
            }
            else $content_encoded = (string) $content->encoded;
            //i decided to use import result in content encoded for post
			if(!is_object($content_encoded)) $post['post_content'] = $this->parser->pre_shortcode_tags($content_encoded );
            else $post['post_content'] = $content_encoded;
			$post['post_excerpt'] = (string) $excerpt->encoded;

			$wp = $item->children( $namespaces['wp'] );
			$hw = $item->children( $namespaces['hw'] );
            //import id or save post id
			$post['post_id'] = (int) $wp->post_id;
            if(!empty($atts['_id'])) $post['_id'] = $atts['_id'];
            elseif(!empty($hw->_id)) $post['_id'] = (string) $hw->_id;
            elseif(isset($post['title'])) $post['_id'] = sanitize_title($post['title']);

			$post['post_date'] = (string) $wp->post_date;
			$post['post_date_gmt'] = (string) $wp->post_date_gmt;
			$post['comment_status'] = (string) $wp->comment_status;
			$post['ping_status'] = (string) $wp->ping_status;
			$post['post_name'] = (string) $wp->post_name;
			$post['status'] = !empty($wp->status)? (string) $wp->status : 'publish';
			$post['post_parent'] = (int) $wp->post_parent;
			$post['menu_order'] = (int) $wp->menu_order;
			$post['post_type'] = (string) $wp->post_type;
			$post['post_password'] = (string) $wp->post_password;
			$post['is_sticky'] = (int) $wp->is_sticky;

			if ( isset($wp->attachment_url) )   //for attachment of post type
				$post['attachment_url'] = (string) $wp->attachment_url;

            foreach($hw->attachment as $a) {    //fetch attachments in other post type
                //$_post = hwArray::cloneArray($post);
                $url = (string) $a->url;
                if($this->parser->data('demo')) {
                    $url = $this->parser->data('demo')->get_file_url($url);
                }
                else {
                    if(!HW_URL::valid_url($url)) $url =$this->parser->data('import_path').'/' .$url;
                }

                //$post['_id'] = (string) $a->_id;
                if($post['post_type']!=='attachment') {
                    $_post = hwArray::cloneArray($post) ;
                    $_post['post_content'] = HW_String::limit($_post['post_content'],50);
                    $_post['post_excerpt'] = HW_String::limit($_post['post_excerpt'],50);
                    $_post['attachment_url'] = $url;
                    $_post['post_type'] = 'attachment';
                    $_post['_id'] = (string) $a->_id;
                    $posts[] = $_post;
                    if((string)$a->thumbnail) $post['hw_thumbnail_id'] = (string) $a->_id;
                }
                //continue;

            }
			foreach ( $item->category as $c ) {
				$att = $c->attributes();
				if ( isset( $att['nicename'] ) )
					$post['terms'][] = array(
						'name' => (string) $c,
						'slug' => (string) $att['nicename'],
						'domain' => (string) $att['domain']
					);
			}
            //post meta
			foreach ( $wp->postmeta as $meta ) {
                //by hoangweb
                $hw_params = $meta->meta_value->xpath('hw:params');
                if(!empty($hw_params)) {
                    //$meta_value = $this->grab_options($meta->meta_value->xpath('hw:params'), $namespaces);
                    $meta_value =$this->recursive_option_data($meta->meta_value->xpath('hw:params'));
                    if(!empty($meta_value->attributes['export']) && is_array($meta_value->option[0])) {
                        $export_path = (string) $meta_value->attributes['export'];
                        $export_path = hwArray::drwOfPath(explode('/', $export_path));
                        eval('$meta_value = $meta_value->option[0]'.$export_path.';');
                    }
                    elseif(!empty($meta_value->option[0])) {
                        $meta_value = $meta_value->option[0];
                    }
                    else $meta_value = array();
                    //HW_Logger::log_file($meta_value);
                    if(!is_string($meta_value) && !is_object($meta_value)) $meta_value = serialize($meta_value);
                }
                else $meta_value = (string) $meta->meta_value;
				$post['postmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => $meta_value
				);
			}
            //for comment
			foreach ( $wp->comment as $comment ) {
				$meta = array();
				if ( isset( $comment->commentmeta ) ) {
					foreach ( $comment->commentmeta as $m ) {
						$meta[] = array(
							'key' => (string) $m->meta_key,
							'value' => (string) $m->meta_value
						);
					}
				}
			
				$post['comments'][] = array(
					'comment_id' => (int) $comment->comment_id,
					'comment_author' => (string) $comment->comment_author,
					'comment_author_email' => (string) $comment->comment_author_email,
					'comment_author_IP' => (string) $comment->comment_author_IP,
					'comment_author_url' => (string) $comment->comment_author_url,
					'comment_date' => (string) $comment->comment_date,
					'comment_date_gmt' => (string) $comment->comment_date_gmt,
					'comment_content' => (string) $comment->comment_content,
					'comment_approved' => (string) $comment->comment_approved,
					'comment_type' => (string) $comment->comment_type,
					'comment_parent' => (string) $comment->comment_parent,
					'comment_user_id' => (int) $comment->comment_user_id,
					'commentmeta' => $meta,
				);
			}

			$posts[] = $post;

		}

        //grab options
        if(isset($xml->options->option)) {
            $options = $this->grab_options($xml->options->option, $namespaces);
        }

		return array(
			'authors' => $authors,
			'posts' => $posts,
			'categories' => $categories,
			'tags' => $tags,
			'terms' => $terms,
            'options' => $options,
            'widgets' => $widgets,
            'sidebars' => $sidebars,
            'skins_data' => $this->skins_data,
			'base_url' => $base_url,
			'version' => $wxr_version
		);
	}


    /**
     * @param $skin
     * @param $namespaces
     */
    public static function parse_skin_data(SimpleXMLElement $skin, $namespaces=null ) {
        if(empty($namespaces)) $namespaces = self::valid_namespaces($skin) ;
        $item = array(
            'apply_plugin' => ''    //refer to hw-hoangweb plugin
        );
        $atts= $skin->attributes();
        $sk = $skin->children( $namespaces['skin'] );

        if(isset($atts['name'])) $item['name'] = (string) $atts['name'];

        if(isset($sk->apply_plugin)) {  //skin used for this module
            $item['apply_plugin'] = (string) $sk->apply_plugin;
            if(!isset($atts['name'])) $atts['name'] = $item['apply_plugin'];
        }
        if(isset($sk->default_skin_path)) {
            $item['default_skin_path'] = (string) $sk->default_skin_path;
        }
        if(isset($sk->default_skin)) {
            $item['default_skin'] = (string) $sk->default_skin;
        }
        if(isset($sk->skin_type)) {
            $item['skin_type'] = (string) $sk->skin_type;
        }
        else $item['skin_type'] = 'file';

        if(isset($sk->skin_name)) {
            $item['skin_name'] = (string) $sk->skin_name;
        }
        if(isset($sk->other_folder)) {
            $item['other_folder'] = (string) $sk->other_folder;
        }
        if(isset($sk->group)) {
            $item['group'] = (string) $sk->group;
        }
        //other params
        if(isset($sk->enable_external_callback)) {
            $item['enable_external_callback'] = (string) $sk->enable_external_callback;
        }
        //allow skin names
        if(isset($sk->allows_skin_names)) {#$this->_print($sk->allows_skin_names->children());
            $params = HW_XML::xml2array($sk->allows_skin_names->children(), 'param', 'params');
            $item['allows_skin_names'] = $params;
        }
        //properties
        if(isset($sk->properties)) {
            $params = HW_XML::xml2array($sk->properties->children(), 'param', 'params');
            $item['properties'] = $params;
        }
        //options
        if(isset($sk->options)) {
            $params = HW_XML::xml2array($sk->options->children(), 'param', 'params');
            $item['options'] = $params;
        }
        //skin list
        if(isset($sk->list)) {
            #$list = array();
            $params = HW_XML::xml2array($sk->list->children(), 'param', 'params');
            foreach($params as $id => &$name) {
                //validation
                if((!$id && !$name) || !$id) continue;
                //if(!$name) $name = $id;
            }
            $item['avaiable_skins_list'] = $params;
        }
        return $item;
    }
    /**
     * fetch all skins instance
     * @param $xml
     * @param $namespaces
     */
    public function gather_skins_data($xml, $namespaces = null) {
        if(empty($namespaces) && $this->namespaces) $namespaces = $this->namespaces;
        else $namespaces = $this->valid_namespaces($xml);

        $skins = array();   ///rss/skins/hw:skin
        if($xml->xpath('skins/hw:skin'))
        foreach ( $xml->xpath('skins/hw:skin') as $skin ) {
            $item = self::parse_skin_data($skin, $namespaces);
            if(/*isset($item['apply_plugin']) &&*/ isset($item['default_skin_path'])
                && isset($item['skin_name']) && isset($item['other_folder'])) {
                /*$instance = new HW_SKIN();
                $item['instance'] = $instance;*/
                $skins[$item['name']] = $item;
            }
        }
        $this->skins_data = $skins ;
        return $skins;
    }
    /**
     * grab options
     * @param $xml
     * @param $namespaces
     */
    protected function grab_options($xml, $namespaces = null) {
        if(empty($namespaces)) $namespaces = $this->namespaces;
        $options = array();
        //grab options
        #if(isset($xml->options->option))
        foreach($xml as $item) {
            $atts = $item->attributes();
            $option_data = $this->recursive_option_data($item);

            list($key, $value) = each($option_data->option );
            $option['name'] = $key;
            $option['import_results'] = &$option_data->import_results;#__print($option_data->import_results);
            $option['method'] = $option_data->method;
            $option['prefix'] = $option_data->prefix;
            $option['attributes'] = HW_XML::xml2array($option_data->attributes);
            if(!empty($option['prefix'])) {

                if(is_array($value)) {
                    foreach($value as $_key => $_val) {
                        $value[$option['prefix'].$_key] = $_val;
                        unset($value[$_key]);
                    }
                }
            }
            $option['value'] = $value;  //simple option value
            $options[] = $option ;
        }
        return $options;
    }

    /**
     * grab sidebars
     * @param $xml
     * @param null $namespaces
     */
    public function grab_sidebars($xml, $namespaces =null) {
        if(empty($namespaces)) $namespaces = $this->namespaces;
        $sidebars = array();
        //loop sidebars
        if($xml->xpath('sidebars/hw:sidebar'))
        foreach ( $xml->xpath('sidebars/hw:sidebar') as $item ) {
            $atts = $item->attributes();
            if(empty($atts['name'])) continue; //sidebar invalid name
            $params = array();

            $name = (string) $atts['name'];
            if(count($item->children())) $params = (array)$this->recursive_option_data($item->children(), $namespaces);
            $params['name'] = $name;
            //get sidebar skin
            if(isset($atts['skin'])) $params['skin'] = (string) $atts['skin'];
            //sidebar id
            if(empty($params['id'])) $params['id'] = sanitize_title($params['name']);
            $sidebars[$name] = $params;
        }
        return $sidebars;
    }
    /**
     * grab widgets data
     * @param $xml
     * @param null $namespaces
     */
    protected function grab_widgets($xml, $namespaces = null) {
        if(empty($namespaces)) $namespaces = $this->namespaces;
        global $wp_registered_sidebars;
        $widgets = array();
        $widgets_id = array();
        // Get all available widgets site supports
        $available_widgets = hw_wie_available_widgets();

        // Get existing widgets in this sidebar
        $sidebars_widgets = get_option( 'sidebars_widgets' );
        $sidebar_widgets = null ;
        $available_instance_ids = array();
        foreach ($sidebars_widgets as $ids) {
            if(is_array($ids))
            foreach($ids as $id) {
                $id_base = hw_wie_get_widget_id_base($id);
                if(!isset($available_instance_ids[$id_base])) {
                    $available_instance_ids[$id_base] = array();
                }
                $available_instance_ids[$id_base][] = str_replace( $id_base . '-', '', $id );
            }
        }
        if($xml->xpath('widgets/hw:widget'))
        foreach ( $xml->xpath('widgets/hw:widget') as $item ) {
            $args = array();
            $atts = $item->attributes();
            $args['update'] = isset($atts['update'])? (string)$atts['update'] : 0;  //whether allow to update widget or add new
            $sidebar_id = !isset($atts['sidebar'])? 'hw-sidebar-data' : (string) $atts['sidebar'];

            if ( !isset( $wp_registered_sidebars[$sidebar_id] ) || !isset($atts['id_base'])) {
                continue;
            }
            $id_base = (string)$atts['id_base'];
            // Does site support this widget?
            if ( ! isset( $available_widgets[$id_base] ) ) {
                //Site does not support widget, explain why widget not imported
                continue;
            }
            // check Inactive if that's where will go
            $sidebar_widgets = isset( $sidebars_widgets[$sidebar_id] ) ? $sidebars_widgets[$sidebar_id] : array();

            if(!isset($widgets_id[$id_base])) $widgets_id[$id_base] = 0;
            do {
                $widgets_id[$id_base]++;  //set next widget instance id
            }
            while(isset($available_instance_ids[$id_base]) && in_array($widgets_id[$id_base], $available_instance_ids[$id_base]));

            $hw = $item->children($namespaces['hw']);
            //widget id
            $widget_id = $id_base . '-'. $widgets_id[$id_base];
            //widget instance
            if($hw->params) $options = $this->recursive_option_data($hw->params[0]->children(), $namespaces)->option;
            else $options = array();

            //init sidebar holder for widgets data
            if(!isset($widgets[$sidebar_id])) $widgets[$sidebar_id] = array();

            //add widget to sidebar
            $widgets[$sidebar_id][$widget_id] = array(
                'instance'=> $options,
                'args' => $args
            );
        }
        return $widgets;
    }
    /**
     * @param $xml
     * @param array $namespaces
     * @return array
     */
    public function build_skin_data(SimpleXMLElement $xml, $namespaces = null ) {
        if(empty($namespaces)) $namespaces = $this->namespaces; #$node=dom_import_simplexml($xml);//dom_import_simplexml()
        #$n = $xml->getNameSpaces(false);_print($node->localName.','.$node->textContent);
        #$xml->getDocNamespaces(0);
        #if(empty($n)) return;
        HW_HOANGWEB::load_class('HW_File_Directory');
        if(empty($namespaces)) {
            $namespaces = $this->valid_namespaces($xml);
        }

        $skin_tag = $xml->xpath('params:skin_encoded');
        $data = array();
        if(!empty($skin_tag) || count($xml->xpath('params:skin_instance'))) {

            $params = $xml->children( $namespaces['params'] );
            $hash_skin = array();
            $skin_config = array();
            $apply_plugin = '';
            $skin_name = '';
            $skin_data = array();
            $screenshot = '';
            $screenshot_ext = 'jpg';

            /*if(isset($skin->apply_plugin)) {    //apply skin
                    $apply_plugin = (string) $skin->apply_plugin ;
                }
                else*/if( isset($skin_data['apply_plugin'])) {
                $apply_plugin = $skin_data['apply_plugin'];
            }
            else {
                $apply_plugin = ''; //leave empty for hw-hoangweb plugin
            }

            //for skin link
            if(!empty($params->skin_file) && HW_XML::count_childs($params->skin_file)) {
                $atts = $params->skin_file->attributes();
                //if(!isset($atts['name'])) $atts['name'] = 'url';  //skin_config
                //get skin data
                if(isset($atts['instance']) && isset($this->skins_data[(string) $atts['instance']])) {
                    $skin_data = $this->skins_data[(string) $atts['instance']];
                }
                $skin = $params->skin_file->children( $namespaces['skin'] );

                $hash_skin['type'] = '';
                //get source
                if(isset($skin->source)) {
                    $hash_skin['source'] = (string) $skin->source;
                }
                else $hash_skin['source'] = 'plugin';

                $file_link = (string) $skin->file;
                $skin_file_name = isset($atts['name'])? (string) $atts['name']: 'url';
                //get group
                if(isset($skin->group)) $group = (string)$skin->group;
                elseif(isset($skin_config['group'])) $group = $skin_config['group'];
                elseif(isset($skin_data['group'])) $group = $skin_data['group'];

                //$hash_skin = HW_SKIN::valid_holder_path($apply_plugin, $hash_skin);

                /*$file_url = HW_File_Directory::generate_url(HW_HOANGWEB_PLUGINS_URL,$apply_plugin,
                    $skin_config['default_skin_path'], $group, $file_link
                );
                //$hash_skin['screenshot_url'] = $hash_skin['file_url'] = $file_url;
                */
                $hash_skin['file'] = $hash_skin['name'] = $file_link;
                //$data[isset($atts['name'])? (string) $atts['name']: 'url'] = $file_url ;

            }
            //parse hash_skin value
            if(isset($params->skin_encoded) && HW_XML::count_childs($params->skin_encoded) ) {

                $atts = $params->skin_encoded->attributes();
                //if(!isset($atts['name'])) $atts['name'] = 'hash_skin';  //default hash_skin field-> not allow to update
                if(isset($atts['instance']) && isset($this->skins_data[(string) $atts['instance']])) {
                    $skin_data = $this->skins_data[(string) $atts['instance']];
                }

                #$params->skin_encoded->xpath('skin:skins_path');
                $skin = $params->skin_encoded->children( $namespaces['skin'] );

                if(isset($skin->apply_plugin)) {    //apply skin
                    $apply_plugin = (string) $skin->apply_plugin ;
                }
                elseif( isset($skin_data['apply_plugin'])) {
                    $apply_plugin = $skin_data['apply_plugin'];
                }
                else {
                    $apply_plugin = ''; //leave empty for hw-hoangweb plugin
                }
                //get source
                if(isset($skin->source)) {
                    $hash_skin['source'] = (string) $skin->source;
                }
                else $hash_skin['source'] = 'plugin';

                //skin name
                /*if(isset($skin->skin_name)) {
                        $skin_name = (string) $skin->skin_name;
                    }
                    else*/if( isset($skin_data['skin_name'])) {
                    $skin_name = $skin_data['skin_name'];
                }
                //$hash = array('skins_path', '');
                $hash_skin['holder'] = $apply_plugin;
                if($apply_plugin) {
                    $hash_skin = HW_SKIN::valid_holder_path($apply_plugin, $hash_skin);
                }
                else {  //refer to hw-hoangweb plugin
                    if(!file_exists($hash_skin['holder'])) {
                        $hash_skin['holder'] = '';#HW_HOANGWEB_PATH. DIRECTORY_SEPARATOR;
                        $hash_skin['holder_url'] = '';  //HW_HOANGWEB_URL ;
                    }
                }
                if(isset($skin->screenshot)){   //screenshot
                    $screenshot = (string) $skin->screenshot;
                }
                /*elseif(isset($skin_data['screenshot'])) {
                    $screenshot = $skin_data['screenshot'];
                }*/

                if(isset($skin->skin_type)) {   //skin type
                    $hash_skin['type'] = (string)$skin->skin_type;
                }
                elseif(isset($skin_data['skin_type'])) $hash_skin['type'] = $skin_data['skin_type'];

                //group
                if(isset($skin->group)) {
                    $hash_skin['group'] = (string) $skin->group;
                }
                elseif(isset($skin_data['group'])) $hash_skin['group'] = $skin_data['group'];
                else $hash_skin['group'] = '';

                if(isset($skin->default_folder)) {  //skins path
                    $hash_skin['default_folder'] = (string)$skin->default_folder;
                }
                elseif(isset($skin_data['default_skin_path'])) $hash_skin['default_folder'] = $skin_data['default_skin_path'];
                //skin_folder
                if($hash_skin['type'] == 'file') $hash_skin['skin_folder'] = $hash_skin['default_folder'].'/'. $hash_skin['group'];
                else $hash_skin['skin_folder'] = $hash_skin['default_folder'];

                if($hash_skin['type'] === 'link') { //path
                    $hash_skin['path'] = rtrim($hash_skin['group'], '\/'). '/';
                }
                else {
                    if(isset($skin->skin)) { //skin folder
                        $hash_skin['path'] = (string)$skin->skin;
                    }
                    elseif(isset($skin_data['default_skin'])) $hash_skin['path'] = $skin_data['default_skin'];
                }
                $hash_skin['path'] = rtrim($hash_skin['path'], '\/'). '/';  //valid

                $full_holder = HW_SKIN::get_skin_realpath(array('path' => $hash_skin['holder']), $hash_skin['source'])->path;
                $skin_path = HW_File_Directory::generate_path($full_holder, $hash_skin['default_folder'],$hash_skin['group'],$hash_skin['path']);
                //screenshot mimetype
                /*if(isset($skin->screenshot_mimetype)) {   //no, we auto find screenshot mimetype
                    $screenshot_ext = (string) $skin->screenshot_mimetype;
                }*/
                $screenshot_ext = file_exists($skin_path. '/screenshot.png')? 'png' : 'jpg';
                //skin name
                if($hash_skin['type'] == 'file' && $skin_name) $hash_skin['filename'] = $skin_name;
                #else $hash_skin['file'] = $skin_name; //for skin link, do it above

                if(isset($skin->title) && !isset($hash_skin['name'])) {   //title
                    $hash_skin['name'] = (string)$skin->title;
                }
                else if(isset($hash_skin['holder']) && isset($hash_skin['default_folder']) && !isset($hash_skin['name'])){
                    if(isset($skin_data['template_header'])) {
                        $header = $skin_data['template_header'];
                    }
                    else $header = array(
                        'name' => 'HW Template',
                        'description' => 'Description',
                        'author' => 'Author',
                        'uri' => 'Author URI'
                    );

                    if($hash_skin['type'] =='file') $skin_file = HW_File_Directory::generate_path($full_holder, $hash_skin['default_folder'],$hash_skin['group'],$hash_skin['path']).'/'.( $skin_name);
                    else {
                        $skin_file = HW_File_Directory::generate_path($full_holder, $hash_skin['default_folder'],$hash_skin['path']).'/'.( $skin_name);
                    }
                    $temp_header = HW_SKIN::get_template_data($skin_file,0, $header);
                    $hash_skin['name'] = $temp_header['name'];
                }

                /*if(isset($skin->skin_url)) {    //active skin url
                    $hash_skin['skin_url'] = (string)$skin->skin_url;
                    if(!HW_Validation::hw_valid_url($hash_skin['skin_url'])) {
                        $hash_skin['skin_url'] = HW_File_Directory::generate_path(HW_HOANGWEB_PLUGINS_URL, $hash_skin['skin_url']);
                    }
                }
                else*/ {
                    $hash_skin['skin_url'] = HW_File_Directory::generate_url($hash_skin['holder_url'], $hash_skin['skin_folder'], $hash_skin['path']);
                }
                if(isset($skin_file_name) && isset($file_link)) {
                    $hash_skin['file_url'] = HW_File_Directory::generate_url($hash_skin['skin_url'], $file_link);
                    $data[$skin_file_name] = $hash_skin['screenshot_url'] = $hash_skin['file_url'];
                }
                /*if($screenshot && !isset($hash_skin['screenshot_url'])) {  //skin screenshot
                    $hash_skin['screenshot_url'] = $screenshot;
                    if(!HW_Validation::hw_valid_url($hash_skin['screenshot_url'])) {
                        $hash_skin['screenshot_url'] = HW_File_Directory::generate_path(HW_HOANGWEB_PLUGINS_URL, $hash_skin['screenshot_url']);
                    }
                }
                else*/
                if(!isset($hash_skin['screenshot_url'])) {
                    $hash_skin['screenshot_url'] = HW_File_Directory::generate_url($hash_skin['skin_url'] , 'screenshot.'. ltrim($screenshot_ext,'.'));  //default jpg mimetype
                }

                //valid hash skin value
                if(count($hash_skin) <7) return ;
                //$hash_skin = hwArray::sortArrayByArray($hash_skin, array('skins_path','skin_folder','skin_url','skin_type','title','screenshot','skin_name'));
                if(isset($hash_skin['type']) && $hash_skin['type'] == 'file') {
                    $hash_skin_value =  HW_SKIN::generate_skin_path( $hash_skin) ;
                }
                else $hash_skin_value =  HW_SKIN::generate_skin_file( $hash_skin) ;#__print(($hash_skin));exit;

                $data[isset($atts['name'])? (string)$atts['name']:'hash_skin'] =  $hash_skin_value ;
            }
            //parse skin config
            if(isset($params->skin_instance) /*&& $params->skin_instance->children() => may inheritance*/) {
                $atts = $params->skin_instance->attributes();
                //if(!isset($atts['name'])) $atts['name'] = 'skin_config';  //skin_config
                if(isset($atts['instance']) && isset($this->skins_data[(string) $atts['instance']])) {
                    $skin_data = $this->skins_data[(string) $atts['instance']];
                }

                $skin = $params->skin_instance->children( $namespaces['skin'] );
                //get default values
                if(isset($skin->apply_plugin) && !$apply_plugin) {   //apply_plugin
                    $apply_plugin = (string) $skin->apply_skin;
                }
                elseif( isset($skin_data['apply_plugin']) && !$apply_plugin) {
                    $apply_plugin = $skin_data['apply_plugin'];
                }

                if(isset($skin->skin_name) && !$skin_name) { //skin_name
                    $skin_name = (string) $skin->skin_name;
                }
                elseif( isset($skin_data['skin_name']) && !$skin_name) {
                    $skin_name = $skin_data['skin_name'];
                }

                //grab
                /*if(isset($skin->apply_current_path)) {  //apply_current_path
                    $skin_config['apply_current_path'] = (string) $skin->apply_current_path;
                    if(!file_exists($skin_config['apply_current_path'])) {
                        $skin_config['apply_current_path'] = HW_File_Directory::generate_path(HW_HOANGWEB_PLUGINS, $skin_config['apply_current_path']);
                    }
                }
                if(isset($skin->plugin_url)) {  //plugin_url
                    $skin_config['plugin_url'] = (string) $skin->plugin_url;
                    if(!HW_Validation::hw_valid_url($skin_config['plugin_url'])) {
                        $skin_config['plugin_url'] = HW_File_Directory::generate_path(HW_HOANGWEB_PLUGINS_URL , $skin_config['plugin_url']);
                    }
                }*/
                if(isset($skin->other_folder)) {  //other_folder
                    $skin_config['other_folder'] = (string) $skin->other_folder;
                }
                elseif(isset($skin_data['other_folder'])) $skin_config['other_folder'] = $skin_data['other_folder'];

                if($skin_name) {  //skin_name
                    $skin_config['skin_name'] = $skin_name;
                }
                if(isset($skin->default_skin_path)) {  //default_skin_path
                    $skin_config['default_skin_path'] = (string) $skin->default_skin_path;
                }
                elseif(isset($skin_data['default_skin_path'])) $skin_config['default_skin_path'] = $skin_data['default_skin_path'];

                if(isset($skin->group)) {  //group
                    $skin_config['group'] = (string) $skin->group;
                }
                elseif(isset($skin_data['group'])) $skin_config['group'] = $skin_data['group'];

                if(isset($skin->enable_external_callback)) {  //enable_external_callback
                    $skin_config['enable_external_callback'] = (string) $skin->enable_external_callback;
                }
                elseif(isset($skin_data['enable_external_callback'])) {
                    $skin_config['enable_external_callback'] = $skin_data['enable_external_callback'];
                }
                //allow skin names
                if(isset($skin->allows_skin_names)) {
                    $skin_config['allows_skin_names'] = HW_XML::xml2array($skin->allows_skin_names->children(), 'param', 'params');
                }
                elseif(isset($skin_data['allows_skin_names'])) $skin_config['allows_skin_names'] = $skin_data['allows_skin_names'];
                //properties
                if(isset($skin->properties)) {
                    $skin_config['properties'] = HW_XML::xml2array($skin->properties->children(), 'param', 'params');
                }
                elseif(isset($skin_data['properties'])) $skin_config['properties'] = $skin_data['properties'];
                //options
                if(isset($skin->options)) {
                    $skin_config['options'] = HW_XML::xml2array($skin->properties->children(), 'param', 'params');
                }
                elseif(isset($skin_data['options'])) $skin_config['options'] = $skin_data['options'];

                if($apply_plugin && !isset($skin_config['apply_current_path'])) {
                    //$skin_config["apply_current_path"] = HW_File_Directory::generate_path(HW_HOANGWEB_PLUGINS,$apply_plugin);
                    $skin_config["apply_current_path"] = $apply_plugin;
                }
                if($apply_plugin && !isset($skin_config['plugin_url'])) {
                    //$skin_config['plugin_url'] = HW_File_Directory::generate_path(HW_HOANGWEB_PLUGINS_URL, $apply_plugin);
                    $skin_config['plugin_url'] =  $apply_plugin;
                }
                //other params
                $other_config_params = HW_XML::xml2array($params->skin_instance->children(), 'param' ,'params');
                //fix
                if(!isset($other_config_params['allows_skin_names']) ) {
                    $other_config_params['allows_skin_names'] = array();
                }
                elseif(isset($skin_data['allows_skin_names'])) {
                    $other_config_params['allows_skin_names'] = $skin_data['allows_skin_names'];
                }

                if(isset($skin_config['skin_name']) && !in_array($skin_config['skin_name'], $other_config_params['allows_skin_names'])) {
                    $other_config_params['allows_skin_names'][] = $skin_config['skin_name'] ;
                }

                if(!empty($other_config_params)) $skin_config = array_merge($skin_config, $other_config_params);

                $data[isset($atts['name'])? (string) $atts['name']: 'skin_config'] = HW_SKIN::generate_skin_config($skin_config) ;
            }

            //other params
            $data = array_merge($data, HW_XML::xml2array($xml) );

            /*$atts = $xml->attributes(); //get attributes
            if(isset($atts['name'])) {
                $data = array((string)$atts['name'] => $data);
            }*/
            return count($data)? $data : null;
        }

    }
    /**
     * @param $xml
     * @param $prefix
     * @param $count
     * @return array
     */
    public  function recursive_option_data($xml, $prefix='', $count=0) {
        //return HW_XML::xml2array($item, 'param', 'params');
        $method = 'override';
        $attributes = null;

        $arr = array();
        $i_results = array();
        foreach ($xml as $id => $element) {
            $tag = $element->getName();
            if($tag !== 'param' && $tag !== 'params') continue ;

            $_recursive = true;
            $val= null;
            $skin= $parse =null;

            $atts = $element->attributes();
            $ori_key = $key = isset($atts['name'])? (string)$atts['name'] : '';
            $encoded = isset($atts['encoded'])? (string)$atts['encoded'] : 0;
            $export = isset($atts['export'])? (string)$atts['export'] : '';
            if($count++ == 0 ) {
                if(isset($atts['method'])) $method = (string) $atts['method'];  //tell how to append data
                if(isset($atts['prefix'])) {
                    $prefix = (string) $atts['prefix']; //prefix option name
                }
                $attributes = $atts;
            }

            if($count !=1 && $prefix !== '') {
                #$key = $prefix.$key;
            }

            //for skin data, because not count for xpath namespace element
            #if(HW_XML::count_childs($element) /*|| count($element->children('params'))*/ ) {
                 //$skin = $this->build_skin_data($element);
            #}
            $import_result= HWIE_Module_Import_Results::valid($element);
            if($import_result) {
                $val=$parse = new HWIE_Module_Import_Results(dom_import_simplexml($import_result));
                //$parse->init($this->parser->importer);
                //$val = $parse->parse_data()->value;
                //$this->import_results[] = &$val;
                //$i_results[] = &$val;
            }
            else  {
                $val = $skin = $this->build_skin_data($element);
            }
            //get value
            /*if(!empty($skin)) {
                $val = $skin;
            }*/
            if($val===null) {
                $val = isset($atts['value'])? (string)$atts['value'] : (!count($element->children())? HW_String::truncate_empty_lines((string)$element)  : null);
            }
            if($val === null && !count($element->children()) && $tag== 'params') {
                $val = array();
            }
            if($export && isset($val[$export])) {
                $val = $val[$export];   //used for build_skin_data & other special param
            }
            if($encoded) {  //for single param
                $val = base64_encode(serialize($val));
            }

            $e = get_object_vars($element);
            if (!empty($e)) {

                if((HW_XML::count_childs($element) && /*$tag == 'params' &&*/ $_recursive) && (empty($skin) && empty($parse)) ){
                    $recursive = $this->recursive_option_data($element, $prefix, $count);
                    //join data option
                    if(is_array($recursive->option) && isset($atts->join)) $recursive->option = join((string)$atts->join, $recursive->option);
                    if($key!=='') {
                        $arr[$key] = &$recursive->option;
                    }
                    else {
                        $arr[] = &$recursive->option;
                    }
                    $i_results = array_merge($i_results, $recursive->import_results);#$i_results[0]='XY';
                    //list($_key, $_value) = end($arr);
                    if($key) {
                        $_key = $key;
                    }
                    else {
                        end($arr);
                        $_key= key($arr);
                    }
                    $_value = $arr[$_key];
                    reset($arr);    //make sure you reset array data
                    if(is_array($_value) && isset($_value[$export])) {
                        $arr[$_key] = $_value[$export];
                        $i_results[$_key] = &$arr[$_key] ;
                    }
                    if($encoded) {  //encoded whole params has sub
                        $arr[$_key] = base64_encode(serialize($_value));
                        $i_results[$_key] = &$arr[$_key] ;
                        #$arr = base64_encode(serialize($_value));  //don't because if exists more element around it
                    }
                }
                else {
                    if($key !== '') {
                        if($val instanceof HWIE_Module_Import_Results) {
                            $arr[$key] = &$val;
                            $i_results[$key] = &$val;
                        }
                        else $arr[$key] = $val;
                    }
                    else {

                        if($val instanceof HWIE_Module_Import_Results) {
                            $arr[] = &$val;
                            $i_results[] = &$val;
                        }
                        else $arr[] = $val;
                    }

                }
                if($skin) {
                    /*$_skin=$element->xpath('params:skin');
                    $node = dom_import_simplexml($element);
                    $node->parentNode->removeChild($node);*/
                    $this->recursive_option_data( $element, $prefix, $count);
                }
            }
            else {
                if($key !=='') $arr[$key] = trim($element); else $arr[] =trim($element);
            }
        }
        $result= (object) array(
            'import_results' => &$i_results,
            'option' => &$arr,
            'method' => $method,
            'prefix' => $prefix,
            'attributes' => $attributes['@attributes']
        );
        return $result;
    }
}
endif;
/**
 * WXR Parser that makes use of the XML Parser PHP extension.
 */
if(!class_exists('HW_WXR_Parser_XML')):
class HW_WXR_Parser_XML {
	var $wp_tags = array(
		'wp:post_id', 'wp:post_date', 'wp:post_date_gmt', 'wp:comment_status', 'wp:ping_status', 'wp:attachment_url',
		'wp:status', 'wp:post_name', 'wp:post_parent', 'wp:menu_order', 'wp:post_type', 'wp:post_password',
		'wp:is_sticky', 'wp:term_id', 'wp:category_nicename', 'wp:category_parent', 'wp:cat_name', 'wp:category_description',
		'wp:tag_slug', 'wp:tag_name', 'wp:tag_description', 'wp:term_taxonomy', 'wp:term_parent',
		'wp:term_name', 'wp:term_description', 'wp:author_id', 'wp:author_login', 'wp:author_email', 'wp:author_display_name',
		'wp:author_first_name', 'wp:author_last_name',
	);
	var $wp_sub_tags = array(
		'wp:comment_id', 'wp:comment_author', 'wp:comment_author_email', 'wp:comment_author_url',
		'wp:comment_author_IP',	'wp:comment_date', 'wp:comment_date_gmt', 'wp:comment_content',
		'wp:comment_approved', 'wp:comment_type', 'wp:comment_parent', 'wp:comment_user_id',
	);
    var $hw_tags = array(
        'hw:skin', 'skin:group','skin:skin_type','skin:source','skin:file'
    );
    /**
     * HW_WXR_Parser
     * @var
     */
    var $parser;

    /**
     * @param $wxr_parser
     */
    public function __construct(HW_WXR_Parser $wxr_parser=null) {
        $this->parser = $wxr_parser;
    }

    /**
     * @param $file
     * @return array|WP_Error
     */
    function parse( $file ) {
		$this->wxr_version = $this->in_post = $this->cdata = $this->data = $this->sub_data = $this->in_tag = $this->in_sub_tag = false;
		$this->authors = $this->posts = $this->term = $this->category = $this->tag = array();

        $paths = array();
        if(is_string($file)) {
            if(!$this->parser->data('import_path')) $paths['import_path'] = rtrim(HW_URL::get_path_url($file, true),'\/'). '/';
            if(!$this->parser->data('import_dir')) $paths['import_dir'] = dirname($file);

            $this->parser->update_variables( $paths);
        }

		$xml = xml_parser_create( 'UTF-8' );
		xml_parser_set_option( $xml, XML_OPTION_SKIP_WHITE, 1 );
		xml_parser_set_option( $xml, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_object( $xml, $this );
		xml_set_character_data_handler( $xml, 'cdata' );
		xml_set_element_handler( $xml, 'tag_open', 'tag_close' );

		if ( ! xml_parse( $xml, file_get_contents( $file ), true ) ) {
			$current_line = xml_get_current_line_number( $xml );
			$current_column = xml_get_current_column_number( $xml );
			$error_code = xml_get_error_code( $xml );
			$error_string = xml_error_string( $error_code );
			return new WP_Error( 'XML_parse_error', 'There was an error when reading this WXR file', array( $current_line, $current_column, $error_string ) );
		}
		xml_parser_free( $xml );

		if ( ! preg_match( '/^\d+\.\d+$/', $this->wxr_version ) )
			return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );

		return array(
			'authors' => $this->authors,
			'posts' => $this->posts,
			'categories' => $this->category,
			'tags' => $this->tag,
			'terms' => $this->term,
			'base_url' => $this->base_url,
			'version' => $this->wxr_version
		);
	}

	function tag_open( $parse, $tag, $attr ) {
		if ( in_array( $tag, $this->wp_tags ) ) {
			$this->in_tag = substr( $tag, 3 );
			return;
		}

		if ( in_array( $tag, $this->wp_sub_tags ) ) {
			$this->in_sub_tag = substr( $tag, 3 );
			return;
		}

		switch ( $tag ) {
			case 'category':
				if ( isset($attr['domain'], $attr['nicename']) ) {
					$this->sub_data['domain'] = $attr['domain'];
					$this->sub_data['slug'] = $attr['nicename'];
				}
				break;
			case 'item': $this->in_post = true;
			case 'title': if ( $this->in_post ) $this->in_tag = 'post_title'; break;
			case 'guid': $this->in_tag = 'guid'; break;
			case 'dc:creator': $this->in_tag = 'post_author'; break;
			case 'content:encoded': $this->in_tag = 'post_content'; break;
			case 'excerpt:encoded': $this->in_tag = 'post_excerpt'; break;

			case 'wp:term_slug': $this->in_tag = 'slug'; break;
			case 'wp:meta_key': $this->in_sub_tag = 'key'; break;
			case 'wp:meta_value': $this->in_sub_tag = 'value'; break;
		}
	}

    /**
     * @param $parser
     * @param $cdata
     */
    function cdata( $parser, $cdata ) {
		if ( ! trim( $cdata ) )
			return;

		$this->cdata .= trim( $cdata );
	}

    /**
     * @param $parser
     * @param $tag
     */
    function tag_close( $parser, $tag ) {
		switch ( $tag ) {
			case 'wp:comment':
				unset( $this->sub_data['key'], $this->sub_data['value'] ); // remove meta sub_data
				if ( ! empty( $this->sub_data ) )
					$this->data['comments'][] = $this->sub_data;
				$this->sub_data = false;
				break;
			case 'wp:commentmeta':
				$this->sub_data['commentmeta'][] = array(
					'key' => $this->sub_data['key'],
					'value' => $this->sub_data['value']
				);
				break;
			case 'category':
				if ( ! empty( $this->sub_data ) ) {
					$this->sub_data['name'] = $this->cdata;
					$this->data['terms'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'wp:postmeta':
				if ( ! empty( $this->sub_data ) )
					$this->data['postmeta'][] = $this->sub_data;
				$this->sub_data = false;
				break;
			case 'item':
				$this->posts[] = $this->data;
				$this->data = false;
				break;
			case 'wp:category':
			case 'wp:tag':
			case 'wp:term':
				$n = substr( $tag, 3 );
				array_push( $this->$n, $this->data );
				$this->data = false;
				break;
			case 'wp:author':
				if ( ! empty($this->data['author_login']) )
					$this->authors[$this->data['author_login']] = $this->data;
				$this->data = false;
				break;
			case 'wp:base_site_url':
				$this->base_url = $this->cdata;
				break;
			case 'wp:wxr_version':
				$this->wxr_version = $this->cdata;
				break;

			default:
				if ( $this->in_sub_tag ) {
					$this->sub_data[$this->in_sub_tag] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_sub_tag = false;
				} else if ( $this->in_tag ) {
					$this->data[$this->in_tag] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_tag = false;
				}
		}

		$this->cdata = false;
	}
}
endif;
/**
 * WXR Parser that uses regular expressions. Fallback for installs without an XML parser.
 */
if(!class_exists('HW_WXR_Parser_Regex')):
class HW_WXR_Parser_Regex {
	var $authors = array();
	var $posts = array();
	var $categories = array();
	var $tags = array();
	var $terms = array();
	var $base_url = '';

    /**
     * track term index as id if not found
     * @var int
     */
    var $terms_index = 0;
    /**
     * HW_WXR_Parser
     * @var
     */
    var $parser;

	/*function HW_WXR_Parser_Regex() {
		$this->__construct();
	}*/
    /**
     * main class construct method
     */
    function __construct(HW_WXR_Parser $wxr_parser =null) {
        #$this->__construct();
		$this->has_gzip = is_callable( 'gzopen' );
        $this->parser = $wxr_parser;
	}

    /**
     * parse wxr data
     * @param $file
     * @return array|WP_Error
     */
    function parse( $file ) {
		$wxr_version = $in_post = false;

		$fp = $this->fopen( $file, 'r' );
		if ( $fp ) {
            $paths = array();
            if(is_string($file)) {
                if(!$this->parser->data('import_path')) $paths['import_path'] = rtrim(HW_URL::get_path_url($file, true),'\/'). '/';
                if(!$this->parser->data('import_dir')) $paths['import_dir'] = dirname($file);

                $this->parser->update_variables( $paths);
            }
			while ( ! $this->feof( $fp ) ) {
				$importline = rtrim( $this->fgets( $fp ) );

				if ( ! $wxr_version && preg_match( '|<wp:wxr_version>(\d+\.\d+)</wp:wxr_version>|', $importline, $version ) )
					$wxr_version = $version[1];

				if ( false !== strpos( $importline, '<wp:base_site_url>' ) ) {
					preg_match( '|<wp:base_site_url>(.*?)</wp:base_site_url>|is', $importline, $url );
					$this->base_url = $url[1];
					continue;
				}
				if ( false !== strpos( $importline, '<wp:category>' ) ) {
					preg_match( '|<wp:category>(.*?)</wp:category>|is', $importline, $category );
					$this->categories[] = $this->process_category( $category[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<wp:tag>' ) ) {
					preg_match( '|<wp:tag>(.*?)</wp:tag>|is', $importline, $tag );
					$this->tags[] = $this->process_tag( $tag[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<wp:term>' ) ) {
					preg_match( '|<wp:term>(.*?)</wp:term>|is', $importline, $term );
					$this->terms[] = $this->process_term( $term[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<wp:author>' ) ) {
					preg_match( '|<wp:author>(.*?)</wp:author>|is', $importline, $author );
					$a = $this->process_author( $author[1] );
					$this->authors[$a['author_login']] = $a;
					continue;
				}
				if ( false !== strpos( $importline, '<item>' ) ) {
					$post = '';
					$in_post = true;
					continue;
				}
				if ( false !== strpos( $importline, '</item>' ) ) {
					$in_post = false;
					$this->posts[] = $this->process_post( $post );
					continue;
				}
				if ( $in_post ) {
					$post .= $importline . "\n";
				}
			}

			$this->fclose($fp);
		}

		if ( ! $wxr_version )
			return new WP_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'wordpress-importer' ) );

		return array(
			'authors' => $this->authors,
			'posts' => $this->posts,
			'categories' => $this->categories,
			'tags' => $this->tags,
			'terms' => $this->terms,
			'base_url' => $this->base_url,
			'version' => $wxr_version
		);
	}


    /**
     * @param $string
     * @param $tag
     * @Param $xml_object return xml in object without string
     * @return mixed|string
     */
    function get_tag( $string, $tag ,$xml_object=false) {
		preg_match( "|<$tag.*?>(.*?)</$tag>|is", $string, $return );
		if ( isset( $return[1] ) ) {
			if ( substr( $return[1], 0, 9 ) == '<![CDATA[' ) {
				if ( strpos( $return[1], ']]]]><![CDATA[>' ) !== false ) {
					preg_match_all( '|<!\[CDATA\[(.*?)\]\]>|s', $return[1], $matches );
					$return = '';
					foreach( $matches[1] as $match )
						$return .= $match;
				} else {
					$return = preg_replace( '|^<!\[CDATA\[(.*)\]\]>$|s', '$1', $return[1] );
				}
			} else {
				$return = $return[1];
			}
		} else {
			$return = '';
		}
        if($xml_object && trim($return) && HW_XML::string_is_xml_format($return) ) return HWIE_Param::string2xml_withNS($return);
		return $return;
	}

    /**
     * import categories
     * @param $c
     * @return array
     */
    public function process_category( $c ) {
        $this->terms_index++;
        $term_id = $this->get_tag( $c, 'wp:term_id' );  //term id
		return array(
			'term_id' => $term_id? $term_id : $this->terms_index,
			'cat_name' => $this->get_tag( $c, 'wp:cat_name' ),
			'category_nicename'	=> $this->get_tag( $c, 'wp:category_nicename' ),
			'category_parent' => $this->get_tag( $c, 'wp:category_parent' ),
			'category_description' => $this->get_tag( $c, 'wp:category_description' ),
		);
	}

    /**
     * import tags
     * @param $t
     * @return array
     */
    public function process_tag( $t ) {
        $this->terms_index++;
        $term_id = $this->get_tag( $t, 'wp:term_id' );  //term id
		return array(
			'term_id' => $term_id? $term_id : $this->terms_index,
			'tag_name' => $this->get_tag( $t, 'wp:tag_name' ),
			'tag_slug' => $this->get_tag( $t, 'wp:tag_slug' ),
			'tag_description' => $this->get_tag( $t, 'wp:tag_description' ),
		);
	}

    /**
     * import terms
     * @param $t
     * @return array
     */
    function process_term( $t ) {
        $this->terms_index++;
        $term_id = $this->get_tag( $t, 'wp:term_id' );  //term id
		return array(
			'term_id' => $term_id? $term_id : $this->terms_index,
			'term_taxonomy' => $this->get_tag( $t, 'wp:term_taxonomy' ),
			'slug' => $this->get_tag( $t, 'wp:term_slug' ),
			'term_parent' => $this->get_tag( $t, 'wp:term_parent' ),
			'term_name' => $this->get_tag( $t, 'wp:term_name' ),
			'term_description' => $this->get_tag( $t, 'wp:term_description' ),
		);
	}

    /**
     * do import for author
     * @param $a
     * @return array
     */
    public function process_author( $a ) {
		return array(
			'author_id' => $this->get_tag( $a, 'wp:author_id' ),
			'author_login' => $this->get_tag( $a, 'wp:author_login' ),
			'author_email' => $this->get_tag( $a, 'wp:author_email' ),
			'author_display_name' => $this->get_tag( $a, 'wp:author_display_name' ),
			'author_first_name' => $this->get_tag( $a, 'wp:author_first_name' ),
			'author_last_name' => $this->get_tag( $a, 'wp:author_last_name' ),
		);
	}

    /**
     * do import for posts
     * @param $post
     * @return array
     */
    public function process_post( $post ) {
		$post_id        = $this->get_tag( $post, 'wp:post_id' );
		$post_title     = $this->get_tag( $post, 'title' );
		$post_date      = $this->get_tag( $post, 'wp:post_date' );
		$post_date_gmt  = $this->get_tag( $post, 'wp:post_date_gmt' );
		$comment_status = $this->get_tag( $post, 'wp:comment_status' );
		$ping_status    = $this->get_tag( $post, 'wp:ping_status' );
		$status         = $this->get_tag( $post, 'wp:status' );
		$post_name      = $this->get_tag( $post, 'wp:post_name' );
		$post_parent    = $this->get_tag( $post, 'wp:post_parent' );
		$menu_order     = $this->get_tag( $post, 'wp:menu_order' );
		$post_type      = $this->get_tag( $post, 'wp:post_type' );
		$post_password  = $this->get_tag( $post, 'wp:post_password' );
		$is_sticky      = $this->get_tag( $post, 'wp:is_sticky' );
		$guid           = $this->get_tag( $post, 'guid' );
		$post_author    = $this->get_tag( $post, 'dc:creator' );

		$post_excerpt = $this->get_tag( $post, 'excerpt:encoded' );
		$post_excerpt = preg_replace_callback( '|<(/?[A-Z]+)|', array( &$this, '_normalize_tag' ), $post_excerpt );
		$post_excerpt = str_replace( '<br>', '<br />', $post_excerpt );
		$post_excerpt = str_replace( '<hr>', '<hr />', $post_excerpt );

		$post_content = $this->get_tag( $post, 'content:encoded' );
		$post_content = preg_replace_callback( '|<(/?[A-Z]+)|', array( &$this, '_normalize_tag' ), $post_content );
		$post_content = str_replace( '<br>', '<br />', $post_content );
		$post_content = str_replace( '<hr>', '<hr />', $post_content );
        $post_content = $this->parser->pre_shortcode_tags($post_content);

		$postdata = compact( 'post_id', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_excerpt',
			'post_title', 'status', 'post_name', 'comment_status', 'ping_status', 'guid', 'post_parent',
			'menu_order', 'post_type', 'post_password', 'is_sticky'
		);

		$attachment_url = $this->get_tag( $post, 'wp:attachment_url' );
		if ( $attachment_url )
			$postdata['attachment_url'] = $attachment_url;
        //hw attachment
        preg_match_all( '|<hw:attachment>(.+?)</hw:attachment>|is', $post, $attachments );
        $attachments = $attachments[1];
        if ( $attachments ) {
            foreach ( $attachments as $p ) {
                $hw_attachment_url = $this->get_tag($p, 'hw:url');
                if($hw_attachment_url) {
                    if(!HW_URL::valid_url($hw_attachment_url)) $postdata['attachment_url'] = $this->parser->data('import_path').$hw_attachment_url;
                    else $postdata['attachment_url'] = $hw_attachment_url;

                    //found new attachment in post
                    if($post_type !=='attachment') {
                        $_postdata = hwArray::cloneArray($postdata) ;
                        $_postdata['post_content'] = HW_String::limit($_postdata['post_content'],50);
                        $_postdata['post_excerpt'] = HW_String::limit($_postdata['post_excerpt'],50);
                        $_postdata['post_type'] = 'attachment';
                        $_postdata['_id'] = $this->get_tag($p, 'hw:_id');

                        $_postdata['thumbnail'] = $this->get_tag($p, 'hw:thumbnail');
                        if($_postdata['thumbnail']) $_postdata['hw_thumbnail_id'] = $this->get_tag($p, 'hw:_id');
                        $this->posts[] = $_postdata;
                    }
                    /*else {
                        $postdata['thumbnail'] = $this->get_tag($p, 'hw:thumbnail');
                        if($postdata['thumbnail']) $postdata['hw_thumbnail_id'] = $this->get_tag($p, 'hw:_id');
                    }*/
                }
            }
        }


		preg_match_all( '|<category domain="([^"]+?)" nicename="([^"]+?)">(.+?)</category>|is', $post, $terms, PREG_SET_ORDER );
		foreach ( $terms as $t ) {
			$post_terms[] = array(
				'slug' => $t[2],
				'domain' => $t[1],
				'name' => str_replace( array( '<![CDATA[', ']]>' ), '', $t[3] ),
			);
		}
		if ( ! empty( $post_terms ) ) $postdata['terms'] = $post_terms;

		preg_match_all( '|<wp:comment>(.+?)</wp:comment>|is', $post, $comments );
		$comments = $comments[1];
		if ( $comments ) {
			foreach ( $comments as $comment ) {
				preg_match_all( '|<wp:commentmeta>(.+?)</wp:commentmeta>|is', $comment, $commentmeta );
				$commentmeta = $commentmeta[1];
				$c_meta = array();
				foreach ( $commentmeta as $m ) {
					$c_meta[] = array(
						'key' => $this->get_tag( $m, 'wp:meta_key' ),
						'value' => $this->get_tag( $m, 'wp:meta_value' ),
					);
				}

				$post_comments[] = array(
					'comment_id' => $this->get_tag( $comment, 'wp:comment_id' ),
					'comment_author' => $this->get_tag( $comment, 'wp:comment_author' ),
					'comment_author_email' => $this->get_tag( $comment, 'wp:comment_author_email' ),
					'comment_author_IP' => $this->get_tag( $comment, 'wp:comment_author_IP' ),
					'comment_author_url' => $this->get_tag( $comment, 'wp:comment_author_url' ),
					'comment_date' => $this->get_tag( $comment, 'wp:comment_date' ),
					'comment_date_gmt' => $this->get_tag( $comment, 'wp:comment_date_gmt' ),
					'comment_content' => $this->get_tag( $comment, 'wp:comment_content' ),
					'comment_approved' => $this->get_tag( $comment, 'wp:comment_approved' ),
					'comment_type' => $this->get_tag( $comment, 'wp:comment_type' ),
					'comment_parent' => $this->get_tag( $comment, 'wp:comment_parent' ),
					'comment_user_id' => $this->get_tag( $comment, 'wp:comment_user_id' ),
					'commentmeta' => $c_meta,
				);
			}
		}
		if ( ! empty( $post_comments ) ) $postdata['comments'] = $post_comments;

		preg_match_all( '|<wp:postmeta>(.+?)</wp:postmeta>|is', $post, $postmeta );
		$postmeta = $postmeta[1];
		if ( $postmeta ) {
			foreach ( $postmeta as $p ) {
				$post_postmeta[] = array(
					'key' => $this->get_tag( $p, 'wp:meta_key' ),
					'value' => $this->get_tag( $p, 'wp:meta_value' ,true),
				);
			}
		}
		if ( ! empty( $post_postmeta ) ) $postdata['postmeta'] = $post_postmeta;

		return $postdata;
	}

    /**
     * @param $matches
     * @return string
     */
    function _normalize_tag( $matches ) {
		return '<' . strtolower( $matches[1] );
	}

    /**
     * @param $filename
     * @param string $mode
     * @return resource
     */
    function fopen( $filename, $mode = 'r' ) {
		if ( $this->has_gzip )
			return gzopen( $filename, $mode );
		return fopen( $filename, $mode );
	}

	function feof( $fp ) {
		if ( $this->has_gzip )
			return gzeof( $fp );
		return feof( $fp );
	}

	function fgets( $fp, $len = 8192 ) {
		if ( $this->has_gzip )
			return gzgets( $fp, $len );
		return fgets( $fp, $len );
	}

	function fclose( $fp ) {
		if ( $this->has_gzip )
			return gzclose( $fp );
		return fclose( $fp );
	}
}
endif;