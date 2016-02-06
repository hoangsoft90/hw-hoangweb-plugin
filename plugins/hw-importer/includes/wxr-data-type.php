<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 13/11/2015
 * Time: 21:39
 */
/**
 * Class HWIE_Param
 */
class HWIE_Param extends DOMElement{
    /**
     * DOMDocument
     * @var null
     */
    protected $dom = null;
    /**
     * save current DOMElement
     */
    public $element = null;
    /**
     * root element
     * @var null
     */
    protected $root = null;
    /**
     * @param null|DOMDocument $dom
     * @param $root
     * @param array $atts
     * @param string $tag
     */
    public function __construct( /*$dom=null,*/$root = null,$atts = array(), $tag='param', $value='') {
        //get ns
        if(HW_XML::getNS($tag)) {   //namespace detect
            $ns = HW_WXR_Parser_SimpleXML::valid_namespaces(null,HW_XML::getNS($tag));
            parent::__construct($tag ,null, $ns);
        }
        else parent::__construct($tag);

        if($atts instanceof DOMElement) {
            $this->element = $atts;
        }
        else $this->element = $this;

        #if($dom instanceof DOMDocument) $this->dom = $dom;
        #else
        {
            if(!$this->element->ownerDocument) {    //make sure element not move from exists dom
                $this->dom = new DOMDocument(HW_Export::XML_VERSION, HW_Export::XML_ENCODING);
                //note: add to dom before do anything on DOMElement object
                $this->element=$this->dom->importNode($this->element, true);    //convert
                $this->dom->appendChild($this->element );
            }
            else $this->dom = $this->element->ownerDocument;

            if(!empty($value) && is_string($value)) $this->element->nodeValue = $value;
        }

        if(is_array($atts) && count($atts))
        foreach ($atts as $key => $value) {
            $this->element->setAttribute($key, $value);
        }

        //$this->nodeValue = '';  //remove node value

    }

    /**
     * get DOMElement
     * @return HWIE_Param|null
     */
    public function get() {
        return $this->element;
    }

    /**
     * @param $default
     * @return array
     */
    public function getAttributes($default = array()) {
        $atts = (array)HW_XML::attributesArray($this->get());
        return !empty($atts)? $atts : (is_array($default)? $default : array() );
    }
    /**
     * return DOMDocument that element in use
     * @return DOMDocument
     */
    public function getDoc() {
        return $this->element->ownerDocument;
    }

    /**
     * update node value
     * @param $value
     */
    public function set_value($value) {
        $this->element->nodeValue = $value;
    }
    /**
     * wrap value for meta value container
     * @param array $atts
     * @param $ele
     */
    public function wrap_value ($atts = array(), HWIE_Param $ele= null) {
        #$holder = $this->dom->createElement('hw:params');
        #$holder = $this->add_child('hw:params');
        $holder = self::new_root_doc('hw:params', $atts);

        /*if(is_array($atts)) //set element attributes
        foreach($atts as $name => $value) $holder->setAttribute($name, $value);*/
        if( $ele || $this->get()) {
            $holder->appendChild($holder->ownerDocument->importNode(($ele? $ele->get(): $this->get()), true)) ;
        }

        return $holder->ownerDocument->documentElement ;
    }
    /**
     * @param $obj
     */
    public static function construct($obj) {
        new self();
    }
    /**
     * @param $tag
     * @param array $atts
     * @param $value
     */
    public function add_child($tag, $atts = array(),$value='') {
        if(is_string($atts) && $value === '') $value = $atts;
        if(is_string($tag)) {
            $ele = $this->dom->createElement($tag, $value? $value: null) ;
        }
        elseif($tag instanceof DOMElement) $ele = $tag;
        elseif($tag instanceof HWIE_Param) $ele = $tag->get();
        else return;    //invalid input

        if(is_array($atts)) {
            foreach($atts as $key => $val) {
                $ele->setAttribute($key, $val) ;
            }
        }
        if(is_string($value)) $this->element->appendChild($this->element->ownerDocument->importNode($ele,true));

        return new self(/*$this->dom,*/$this->element,$ele );   //note you should maintain dom
    }

    /**
     * @param $tag
     * @param array $atts also support attr namespace
     * @param $ns element ns or normal element
     */
    public static function new_root_doc($tag='', $atts = array(), $ns = false) {

        $doc = new DOMDocument(HW_Export::XML_VERSION, HW_Export::XML_ENCODING);
        if(is_string($tag) && $tag) {
            if($ns) $holder = $doc->createElementNS(null, $tag);    //make sure remove attr xmlns="xx"
            else $holder = $doc->createElement($tag);
        }
        elseif($tag instanceof DOMElement) $holder = $tag;
        $holder = $doc->importNode($holder, true);  //valid document
        $doc->appendChild($doc->importNode($holder, true));

        if($ns) $attsNS =  HW_WXR_Parser_SimpleXML::valid_namespaces(); //element NS
        if(is_array($atts))
        foreach($atts as $name => $val) {
            //if(HW_XML::getNS($name) && $ns) $holder->setAttributeNS($val, $name, $val);
            //elseif(!HW_XML::getNS($name)) $holder ->setAttribute($name, $val);
            $holder ->setAttribute($name, $val);
        }
        //set attributes NS
        if(!empty($attsNS))
        foreach($attsNS as $ns => $uri) {   //'http://hoangweb.com/export/xmlns/'.$ns
            //$holder->setAttributeNS('http://www.w3.org/2000/xmlns/' , 'xmlns:'. $ns, $uri);  //
            $holder->setAttribute('xmlns:'. $ns, $uri);
        }

        return $holder;
    }

    /**
     * valid string to xml support namespace
     * @param $xml
     * @param array $namespaces
     * @return string
     */
    public static function string2xml_withNS($xml, $namespaces = array()) {
        $root = '<rss ';
        $attsNS =  HW_WXR_Parser_SimpleXML::valid_namespaces();
        if(is_array($namespaces) && count($namespaces)) $attsNS = array_merge($attsNS, $namespaces);
        foreach ($attsNS as $ns=> $uri) {
            $root .= 'xmlns:'. $ns. '="'.$uri.'" ';
        }
        $root .= '>';
        if(is_string($root) && HW_XML::string_is_xml_format($xml)) $root .= $xml;
        $root .= '</rss>';
        $doc = new DOMDocument(HW_Export::XML_VERSION, HW_Export::XML_ENCODING);
        $doc->loadXML($root);
        return $doc->documentElement;
    }
    /**
     * empty all childs from node
     * @param $parentNode
     * @return DOMElement
     */
    public function empty_node(DOMElement $parentNode) {
        while ($parentNode->hasChildNodes()) {
            $parentNode->removeChild($parentNode->firstChild);
        }
        return new self(null,$parentNode);
    }
    /**
     * @param $item
     * @param $ieparam_wrapper
     * @return DOMElement|HWIE_Param
     */
    public static function get_hw_element($item, $ieparam_wrapper=true) {
        if($item instanceof DOMDocument) $ele =  $item->documentElement;
        elseif($item instanceof DOMElement) $ele =  $item;
        elseif($item instanceof SimpleXMLElement) $ele = dom_import_simplexml($item);
        elseif($item instanceof HWIE_Param && $ieparam_wrapper) return $item;
        else return null;

        if($ieparam_wrapper) $ele = new HWIE_Param(0, $ele);
        return $ele;
    }
    /**
     * convert dom to simpleXMLElement
     * @param $ele
     * @param string $class
     * @param $root
     * @return SimpleXMLElement
     */
    public static function dom_to_simplexml($ele, $class='SimpleXMLElement', $root='', $ns=true) {
        $dom = self::get_hw_element($ele);
        if(empty($dom->ownerDocument)) {
            $dom = self::new_root_doc($dom->get(), 0, $ns);
        }
        if($root && is_string($root)) {
            $root = self::new_root_doc($root, 0, true);
            $root->appendChild($root->ownerDocument->importNode($dom, true));
        }
        else $root= $dom;
        /*$namespaces = HW_WXR_Parser_SimpleXML::valid_namespaces($dom);
        foreach ($namespaces as $ns => $uri) {
            $dom->setAttribute('xmlns:' .$ns, $uri) ;
        }*/
        return simplexml_load_string($root->ownerDocument->saveXML(), $class, LIBXML_NOCDATA|LIBXML_NOERROR);
    }
}

/**
 * Class HWIE_Skin_Params
 */
class HWIE_Skin_Params extends HWIE_Param {
    /**
     * @var
     */
    protected $hash_skin ;
    /**
     * @var
     */
    protected $config;
    /**
     * @var
     */
    protected $skin_file;
    /**
     * extra elements
     * @var
     */
    public $extra_params;
    /**
     * @var array
     */
    protected $properties = array();
    /**
     * gather skins for working module
     * @var null
     */
    public  $skins_data =null;
    /**
     * @param string $name
     * @param string|Array $args
     * @param string $tag
     */
    public function __construct($name = 'skin', $args = array(), $tag='params') {
        $atts = array(
            'name' => $name
        );
        parent::__construct(null, $atts, $tag);
        if(is_string($args)) $args = array('instance' => $args);
        if(!empty($args) && is_array($args)) $this->properties = $args;
    }

    /**
     * @param string $name
     * @param array $args
     * @return HWIE_Param
     */
    public function add_hash_skin($name = 'hash_skin', $args = array()) {
        if(!isset($this->properties['instance'])) return ;
        if(is_array($args)) $this->properties = $args = array_merge($this->properties, $args);
        $args = $this->properties;

        $skin = !empty($args['skin'])? $args['skin'] : '';
        $file = !empty($args['file'])? $args['file'] : '';  //for skin link
        $skin_type = !empty($args['skin_type'])? $args['skin_type'] : 'file';
        $source = !empty($args['source'])? $args['source'] : 'plugin';

        if($skin) {
            $skin_encoded = $this->add_child('params:skin_encoded', array('name' => $name, 'instance' => $args['instance']));
            $skin_encoded->add_child('skin:skin',$skin);
            $skin_encoded->add_child('skin:skin_type', $skin_type);
            if($skin_type =='link') $skin_encoded->add_child('skin:file',$file);    //skin link
            $skin_encoded->add_child('skin:source', $source);
            if(!empty($args['group'])) $skin_encoded->add_child('skin:group', (string)$args['group']);

            $this->hash_skin = $skin_encoded;
        }
        return $this->hash_skin;
    }

    /**
     * get hash skin element
     * @return DOMElement
     */
    public function get_hash_skin($wrap = true) {
        if(empty($this->hash_skin)) return ;

        $export_path = (string)$this->hash_skin->get()->getAttribute('name');
        return $wrap? $this->wrap_value(array('export' => $export_path), $this->hash_skin): $this->hash_skin->get();
    }

    /**
     * @param string $item
     * @return array|string
     */
    public function parse_skin_values($item='') {
        $simplexml_parser = HW_WXR_Parser::get_instance()->simplexml_parser;
        $skin = $this->get_skin(0);//->cloneNode(true);
        $xml = self::dom_to_simplexml($skin);   //standalone element must assign namespaces
        $simplexml_parser->gather_skins_data($this->skins_data);
        $data = $simplexml_parser->build_skin_data($xml);
        return $data? (isset($data[$item])? $data[$item]: '') : $data;
    }


    /**
     * get hash skin value with encoding
     * @return array|string
     */
    public function get_hash_skin_code() {
        if(empty($this->hash_skin)) return ;
        $atts = $this->hash_skin->getAttributes();
        return $this->parse_skin_values($atts['name']);
    }
    /**
     * get skin config encode data
     * @return array|string
     */
    public function get_hwskin_config_code() {
        if(empty($this->config)) return ;
        $atts = $this->config->getAttributes();
        return $this->parse_skin_values($atts['name']);
    }

    /**
     * get skin element
     * @return DOMElement
     */
    public function get_skin($wrap = true) {
        #$doc = self::new_root_doc($this->empty_node($this->get()));
        $root = $this->empty_node($this->get());
        $root->add_child($root->getDoc()->importNode($this->hash_skin->get(), true) );
        $root->add_child($root->getDoc()->importNode( $this->config->get(), true) );
        if(!empty($this->skin_file)) $root->add_child($root->getDoc()->importNode( $this->skin_file->get(), true) );
        //skin options
        if(!empty($this->extra_params)) {
            //HW_XML::mergeDom(array_merge(array($root), (array)$this->extra_params->childNodes) );
            foreach($this->extra_params->childNodes as $param) {
                $root->add_child($root->getDoc()->importNode($param, true) );
            }

        }
        $export_path = (string) $this->get()->getAttribute('name');
        return $wrap? $this->wrap_value(array('export' => $export_path), $root) : $root->get();
    }

    /**
     * get skinconfig
     * @return DOMElement
     */
    public function get_skinconfig($wrap = true) {
        if(empty($this->config)) return ;

        $export_path = (string)$this->config->getAttribute('name');
        return $wrap? $this->wrap_value(array('export' => $export_path), $this->config) : $this->config->get();
    }
    /**
     * @param string $name
     * @param array $args
     * @return HWIE_Param
     */
    public function add_skin_file($name = 'url', $args = array()) {
        if(!isset($this->properties['instance'])) return ;
        if(is_array($args)) $this->properties = $args = array_merge($this->properties, $args);
        $args = $this->properties;

        if(!empty($args['file'])) {
            $skin_file = $this->add_child('params:skin_file', array('name' => $name, 'instance' => $args['instance']));
            $skin_file->add_child('skin:file', (string)$args['file']);
            if(!empty($args['group'])) $skin_file->add_child('skin:group', (string)$args['group']);
            $this->skin_file = $skin_file;
        }
        return $this->skin_file ;
    }
    /**
     * add skin config element
     * @param string $name
     * @param array $args
     * @return HWIE_Param
     */
    public function add_skin_config($name = 'hwskin_config', $args = array()) {
        if(!isset($this->properties['instance'])) return ;
        if(is_array($args)) $this->properties = $args = array_merge($this->properties, $args);
        $args = $this->properties;

        $skin_instance = $this->add_child('params:skin_instance', array('name' => $name, 'instance' => $args['instance']));
        $group = !empty($args['group'])? $args['group'] : '';
        if($group) $skin_instance->add_child('skin:group', $group);

        $this->config= $skin_instance;
        return $this->config;
    }

    /**
     * @param array $data
     */
    public function extra_params($data = array()) {
        $extra_params = HW_Export::array_to_xml_params($data, $this->dom, false);
        if(empty($this->extra_params)) {
            $this->extra_params = $extra_params;
        }
        else {
            HW_XML::mergeDom($this->extra_params, $extra_params);
        }
    }
}

/**
 * Class HWIE_Widget
 */
class HWIE_Widget extends HWIE_Param{
    /**
     * DOMDOcument
     */
    protected $doc;
    /**
     * @var null
     */
    protected $id_base = null;
    /**
     * widget instance holder
     * @var
     */
    public  $params;
    /**
     * main construct method
     * @param $id_base widget id base
     * @param $name
     */
    public function __construct($id_base, $name) {
        parent::__construct(0, array('name' => $name, 'id_base' => $id_base), 'hw:widget', null);
        $this->id_base = $id_base;
        //$this->doc = new DOMDocument(HW_Export::XML_VERSION, HW_Export::XML_ENCODING);
        //$widget = $this->doc->createElement('hw:widget') ;
        #$widget->setAttribute('name', $name);
        #$widget->setAttribute('id_base', $id_base); //we ignore to check widget for exists

        #$this->doc->appendChild($widget);
        #$this->widget = $widget;
        $this->params = $this->add_child('hw:params');
    }

    /**
     * add option to widget
     * @param mixed $name
     * @param mixed $value
     * @return current context
     */
    function add_instance($name, $value = '') {
        //create param element
        if(is_array($value) && is_string($name)) {
            $item = new HWIE_Param(0, array('name' => $name), 'params');
            HW_Export::array_to_hw_wxr($value, $item->get(), $this->doc);
            $this->params->add_child($item->get());
        }
        elseif(is_array($name) && $value ==='') {
            foreach ($name as $opt => $val) {
                if(is_string($opt) && !is_numeric($opt)) {
                    $this->add_instance($opt, $val);
                }
            }
        }
        elseif(is_string($value) && is_string($name)) {
            $this->params->add_child('param', array('name' => $name), $value);
        }
        elseif(is_object($name)) {$this->params->add_child($name) ;}
        return $this;
    }
}
/**
 * Class HWIE_WXR_Manager
 */
abstract class HWIE_WXR_Manager extends HW_DOMDocument {
    /**
     * base class
     * @var
     */
    protected $base;
    /**
     * DOMDocument
     * @var
     */
    //protected $doc;
    /**
     * manager
     * @var array
     */
    protected $data = array();

    public function __construct(HW_Export $base=null) {
        parent::__construct(HW_Export::XML_VERSION, HW_Export::XML_ENCODING);
        if($base) $this->base = $base;
    }

    /**
     * add item to data
     * @param $key
     * @param $data
     */
    public function add($key, $data) {
        $this->data[$key] = $data;
    }

    /**
     * get item
     * @param $key
     * @return HWIE_Param|null
     */
    public function get() {
        $args = func_get_args();
        $key = join('-', $args);
        return isset($this->data[$key])? $this->data[$key] : null;
    }

    /**
     * remove item by key
     * @param $key
     */
    public function remove($key) {
        if(isset($this->data[$key])) unset($this->data[$key]);
    }
    /**
     * get non-action wxr data
     * @param string $item
     */
    public static function get_theme_data($item = '') {
        $wxr_files = array();
        if(file_exists(get_stylesheet_directory(). '/config/skins.xml')) {
            $wxr_files['skins'] = HW_WXR_Parser_SimpleXML::read_simplexml_object(get_stylesheet_directory(). '/config/skins.xml');
        }
        if(file_exists(get_stylesheet_directory(). '/config/widgets.xml')) {
            $wxr_files['widgets'] = HW_WXR_Parser_SimpleXML::read_simplexml_object(get_stylesheet_directory(). '/config/widgets.xml');
        }
        return $item? (isset($wxr_files[$item])? $wxr_files[$item] : null) : $wxr_files;
    }
}
/**
 * Class HWIE_Posts_Group
 */
class HWIE_Posts_Group extends HWIE_WXR_Manager{

    /**
     * main element
     * @var DOMElement
     */
    protected $posts;

    /**
     * @param string $version
     * @param string $encode
     */
    public function __construct($base) {
        parent::__construct($base);

        $this->posts = $this->createElement('posts');
        $this->posts->appendChild($this->createElement('wp:wxr_version', '2.0'));
        $this->appendChild($this->posts);
    }

    /**
     * add item
     * @param $data
     * @param $atts
     * @return string post name
     */
    public function addItem($data, $atts = array()) {
        if(!isset($data['post_type'])) return;  //invalid
        $post_identify = '';
        $item = $this->createElement('item');
        if(count($atts)) {  //set attributes
            foreach($atts as $name => $value) {
                if($name == 'name') $post_identify = sanitize_title($value);
                $item->setAttribute($name, $value);
            }
        }
        $user_info = get_userdata(1);
        //add more elements
        $title = isset($data['title'])? $data['title']: '';
        $description = isset($data['description'])? $data['description'] : '';
        $time = date('Y-m-d H:i:s');
        $status = isset($data['status'])? $data['status'] : 'publish';
        $post_parent = isset($data['post_parent'])? $data['post_parent'] : '0';
        $post_password = isset($data['post_password'])? $data['post_password'] : '';
        $menu_order = isset($data['menu_order'])? $data['menu_order'] : '0';
        $is_sticky = isset($data['is_sticky'])? $data['is_sticky'] : '0';
        $content = isset($data['content'])? $data['content'] : '';
        $excerpt = isset($data['excerpt'])? $data['excerpt'] : '';

        //valid
        if($post_identify==='') $post_identify = sanitize_title($title);
        if($this->get(sanitize_title($title))) return $post_identify;  //already exists
        //or item->ownerDocument->createElement
        $item->appendChild($this->createElement('title', $this->cdata($title))); //title
        $item->appendChild($this->createElement('pubDate', $this->cdata(date('l, F j, Y')) ));  //pubDate
        $item->appendChild($this->createElement('dc:creator', $this->cdata($user_info->user_login )));    //creator
        $item->appendChild($this->createElement('description', $this->cdata($description )));    //description
        $item->appendChild($this->createElement('wp:post_date', $this->cdata($time)));   //post_date
        $item->appendChild($this->createElement('wp:post_date_gmt', $this->cdata($time)));   //post_date_gmt
        $item->appendChild($this->createElement('wp:status', $this->cdata($status)));   //status
        $item->appendChild($this->createElement('wp:post_parent', $this->cdata($post_parent)));   //$post_parent
        $item->appendChild($this->createElement('wp:menu_order', $this->cdata($menu_order)));   //menu_order
        $item->appendChild($this->createElement('wp:post_password', $this->cdata($post_password)));   //post_password
        $item->appendChild($this->createElement('wp:is_sticky', $this->cdata($is_sticky)));   //is_sticky
        $item->appendChild($this->createElement('wp:post_type', $this->cdata($data['post_type'])));   //post_type

        if(!is_object($content)) $item->appendChild($this->createElement('content:encoded', $this->cdata($content)));   //content
        else {
            $content_encoded = $this->createElement('content:encoded', null);
            $content_encoded->appendChild($content_encoded->ownerDocument->importNode(HWIE_Param::get_hw_element($content, false), true));
            $item->appendChild($content_encoded);
        }

        $item->appendChild($this->createElement('excerpt:encoded', $this->cdata($excerpt)));   //excerpt
        //attachment
        if(isset($data['attachments'])) {
            foreach ($data['attachments'] as $attach) {
                $item->appendChild($item->ownerDocument->importNode($attach, true));
            }
        }

        //post meta
        if(isset($data['post_metas']) && is_array($data['post_metas']) && count($data['post_metas'])) {
            foreach($data['post_metas'] as $key => $value) {
                $post_meta = $this->createElement('wp:postmeta',null );
                $meta_key = $this->createElement('wp:meta_key', $key);
                $meta_value = $this->createElement('wp:meta_value');

                $post_meta->appendChild($meta_key) ;
                $post_meta->appendChild($meta_value) ;

                if(is_string($value) || is_numeric($value)) {
                    $meta_value->nodeValue = $this->cdata($value);  //appendChild($doc->createTextNode(..))
                    #$meta_value = $this->createElement('wp:meta_value', $this->cdata($value));
                }
                elseif(is_array($value) ) {
                    #$meta_value = $this->createElement('wp:meta_value');
                    $ele = HW_Export::array_to_xml_params($value,$this, false);
                    $meta_value->appendChild($ele);
                }
                elseif(HWIE_Param::get_hw_element($value) instanceof DOMElement /*|| $value instanceof HWIE_Param*/) {
                    $meta_value->appendChild(HW_Export::element_to_wxr_params(HWIE_Param::get_hw_element($value)->get(), $this));
                }
                $item->appendChild($post_meta); //add to doc
            }
        }
        //category,tag, term
        if(isset($data['terms']) && is_array($data['terms'])) {
            foreach($data['terms'] as $domain=>$terms) {
                if(is_array($terms)) {
                foreach($terms as $nicename => $name) {
                    $category = $this->createElement('category', $name);
                    $category->setAttribute('domain', $domain);
                    $category->setAttribute('nicename', $nicename);

                    $item->appendChild($category); //add to doc
                }
                }
                elseif(HWIE_Param::get_hw_element($terms, false) instanceof DOMElement) {
                    $item->appendChild(HWIE_Param::get_hw_element($terms, false) );
                }
            }
        }
        #$this->appendChild($item);
        if($title && !$this->get(sanitize_title($title))) {
            $this->posts->appendChild($item);
            if($title) $this->add(sanitize_title($title), $item);
        }

        return $post_identify;
    }

    /**
     * add term taxonomy
     * @param $term_data
     */
    public function add_term($term_data) {

        $tax = isset($term_data['taxonomy'])? $term_data['taxonomy'] : '';
        $slug = isset($term_data['slug'])? $term_data['slug'] : '';
        $name = isset($term_data['name'])? $term_data['name'] : '';
        $description = isset($term_data['description'])? $term_data['description'] : '';
        $parent = isset($term_data['parent'])? $term_data['parent'] : '';
        $menu_location = isset($term_data['menu_location'])? $term_data['menu_location'] : '';  //for nav_menu
        //valid
        if($name && !$slug) $slug = sanitize_title($name);
        if(!$name && $slug) $name = $slug;
        if(!$name && $slug) return ;

        if($tax =='category') {
            $tag = 'wp:category';
            $term_taxonomy='';
            $term_slug = 'wp:category_nicename';
            $term_name = 'wp:cat_name';
            $term_parent= 'wp:category_parent';
            $term_desc = 'wp:category_description';
        }
        elseif($tax == 'post_tag') {
            $tag = 'wp:tag';
            $term_taxonomy='';
            $term_slug = 'wp:tag_slug';
            $term_name = 'wp:tag_name';
            $term_parent= '';
            $term_desc = 'wp:tag_description';
        }
        else {
            $tag = 'wp:term';
            $term_taxonomy ='wp:term_taxonomy';
            $term_slug = 'wp:term_slug';
            $term_name = 'wp:term_name';
            $term_parent= 'wp:term_parent';
            $term_desc = 'wp:term_description';
        }

        $term = $this->createElement($tag);
        //taxonomy
        if($term_taxonomy && $tax) {
            $term->appendChild( $this->createElement($term_taxonomy, $this->cdata($tax)));
        }
        $term->appendChild( $this->createElement($term_slug, $this->cdata($slug)) );    //slug
        $term->appendChild( $this->createElement($term_name, $this->cdata($name)) );    //name
        if($term_parent && $parent) {//parent
            if(!is_object($parent)) $term->appendChild( $this->createElement($term_parent, $this->cdata($parent)));
            else
                if(HWIE_Param::get_hw_element($parent, false)) {
                    $term->appendChild($term->ownerDocument->importNode(HWIE_Param::get_hw_element($parent, false), true));
                }
        }
        if($description) $term->appendChild( $this->createElement($term_desc, $this->cdata($description))); //term description
        //bind menu location
        if($menu_location) $term->appendChild( $this->createElement('wp:menu_location', $this->cdata($menu_location)));

        if(!$this->get('term_'.$term_data['taxonomy'].'_'.$slug)) {
            $this->posts->appendChild($term);
            $this->add('term_'.$term_data['taxonomy'].'_'.$slug, array('element'=>$term, 'data' => $term_data));
        }

    }

    /**
     * add author wxr item
     * @param array $author
     */
    public function add_author($author) {
        $user = $this->createElement('wp:author');
        if(isset($author['login'])) $user->appendChild( $this->createElement('wp:author_login', $this->cdata($author['login'])) );
        if(isset($author['email'])) $user->appendChild( $this->createElement('wp:author_email', $this->cdata($author['email'])) );
        if(isset($author['display_name'])) $user->appendChild( $this->createElement('wp:author_display_name', $this->cdata($author['display_name'])) );
        if(isset($author['first_name'])) $user->appendChild( $this->createElement('wp:author_first_name', $this->cdata($author['first_name'])) );
        if(isset($author['last_name'])) $user->appendChild( $this->createElement('wp:author_last_name', $this->cdata($author['last_name'])) );

        if(!$this->get('author_'.$author['login'])) {
            $this->posts->appendChild($user);
            $this->add('author_'.$author['login'], array('element'=> $user, 'data' => $author));
        }

    }
}

/**
 * Class HWIE_Options_Group
 */
class HWIE_Options_Group extends HWIE_WXR_Manager {

    /**
     * @var null
     */
    protected $options = null;
    /**
     * @param string $version
     * @param string $encode
     */
    public function __construct($base) {
        parent::__construct($base);

        $this->options = $this->createElement('options');
        $this->appendChild($this->options) ;
    }

    /**
     * add option item
     * @param $data
     * @param $name
     * @param array $atts
     */
    public function add_option($name, $data = array(), $atts = array()) {
        if($this->get($name)) return;   //already exists
        $option = $this->createElement('option');
        $holder = $this->createElement('params');
        $holder->setAttribute('name', $name);
        $option->appendChild($holder) ;
        //set attributes
        if(is_array($atts));
        foreach ($atts as $key => $val) {
            $holder->setAttribute($key, $val);
        }

        HW_Export::array_to_hw_wxr($data, $holder, $this );
        $this->options->appendChild($option);
        $this->add($name, $option);
    }

    /**
     * add settings for modules settings page
     * @param array $settings
     */
    public function add_module_setting_page($settings = array()) {
        $this->add_option('HW_Module_Settings_page', $settings, array(
            'prefix' => HW_Validation::valid_apf_slug($this->base->get_module()->option('module_name')). '_',
            'method' => 'append'    //alway
        ));
    }

    /**
     * add global options page
     * @param array $settings
     */
    public function add_nhp_setting_page($settings = array()) {
        if(!isset($settings['last_tab'])) $settings['last_tab'] = 'general';    //save last tab if not specify
        $this->add_option('nhp_hoangweb_theme_opts', $settings, array(
            'method' => 'append'
        ));
    }
}

/**
 * Class HWIE_Skins_Group
 */
class HWIE_Skins_Group extends HWIE_WXR_Manager {

    /**
     * skins data
     * @var null
     */
    protected $skins = null;

    /**
     * main constructor
     * @param $base
     */
    public function __construct(HW_Export $base) {
        parent::__construct($base);

        $this->skins = $this->createElement('skins');
        $this->appendChild($this->skins);
    }

    /**
     * return skins in wxr format
     */
    public function get_wxr_skins() {
        $this->skins;
    }
    /**
     * define new skin
     * @param $name skin name
     * @param array $args skin params
     */
    public function add_skin($name, $args = array()) {
        if($this->get($name)) return;   //already exists

        $skin = $this->createElement('hw:skin');
        $skin->setAttribute('name', $name);
        $allow_keys = array('default_skin_path','default_skin', 'skin_type','skin_name','other_folder', 'group','enable_external_callback','list','properties', 'options');
        //get apply skin
        if(!empty($args['apply_plugin'])) $apply_plugin = $args['apply_plugin'];
        else $apply_plugin = $this->base->get_module()->option('module_name');

        $item = $this->createElement('skin:apply_plugin', $this->cdata($apply_plugin));
        $skin->appendChild($item);

        if(is_array($args))
        foreach($args as $key => $value) {
            if(! in_array($key, $allow_keys)) continue;
            if(is_string($value)) $item = $this->createElement('skin:'. $key, $this->cdata($value));
            elseif(is_array($value)) {
                $item = $this->createElement('skin:'. $key);
                $aParams = HW_XML::array_to_xml_params($value, 'param', 'params');
                $item->appendChild($this->importNode(dom_import_simplexml($aParams),true));
            }
            $skin->appendChild($item) ;
        }
        $this->add($name, $skin);
        $this->skins->appendChild($skin);
    }

    /**
     * skins data for current theme
     */
    public function load_skins() {
        $skins = self::get_theme_data('skins');
        if($skins) return $skins->xml->xpath('skins/hw:skin');
    }

}

/**
 * Class HWIE_Widgets_Group
 */
class HWIE_Widgets_Group extends HWIE_WXR_Manager {

    /**
     * widgets data
     * @var null
     */
    protected $widgets = null;

    /**
     * class constructor
     * @param $base
     */
    public function __construct($base) {
        parent::__construct($base);

        $this->widgets = $this->createElement('widgets');
        $this->appendChild($this->widgets);
    }

    /**
     * @param $name
     * @param midex $data
     * @param $id_base
     * @return HWIE_Widget
     */
    public function add_widget( $name, $data = array(),$id_base) {
        if($this->get($name)) return;   //already exists
        $item = new HWIE_Widget($id_base, $name);
        $item->add_instance($data);#if($name=='danhmuc')HW_Logger::log_file(HW_XML::output_dom_to_string($item->get()));

        $this->widgets->appendChild($this->importNode($item->get(), true));
        $this->add(/*$id_base. '-'.*/ $name, $item);    //add to mananger, dont need id_base of widget

        return $item;
    }
    /**
     * widgets data for current theme
     */
    public function load_widgets() {
        $widgets = self::get_theme_data('widgets');
        if($widgets) return $widgets->xml->xpath('widgets/hw:widget');
    }

}

/**
 * Class HWIE_Module_Import_Results
 */
class HWIE_Module_Import_Results extends HWIE_Param{
    /**
     * @var
     */
    protected $processed_posts;
    /**
     * @var
     */
    protected $processed_terms;
    /**
     * @var
     */
    protected $processed_menu_items;
    /**
     * main element for to work
     * this important you set public flag & this var belong to self class
     * @var null
     */
    public $element = null;
    /**
     * HW_Module_Export object
     * @var null
     */
    public $module_export = null;
    /**
     * @var null
     */
    public $importer = null;
    /**
     * @var array
     */
    private static  $tags = array('hw:attachment', 'hw:formatString', 'hw:import_post','hw:import_term', 'hw:shortcode');
    /**
     * context data from import file
     * @var array
     */
    private $data = array();
    /**
     * @param array $atts
     */
    function __construct($atts = array(), $tag ='', $value='') {
        if($atts instanceof DOMElement) {
            $this->element = $atts;
            return $this;
        }
        parent::__construct(0, $atts, $tag, $value);
    }

    /**
     * check if element is valid tags
     * @param $element
     * @return bool
     */
    public static function valid( $element) {
        $tag = '';
        if($element instanceof SimpleXMLElement) {
            $ns =array_keys($element->getNamespaces());
            $tag = reset($ns).':'.$element->getName();
            foreach (self::$tags as $tag) {
                if(count($element->xpath($tag)))
                foreach($element->xpath($tag) as $ele)
                    return $ele;
            }
        }
        #elseif($element instanceof DOMElement) $tag = $element->getName();
        #return in_array($tag, self::$tags);
        return false;
    }
    /**
     * init
     * @param HW_Module_Export|HW_Import $module_exporter
     * @param array $data
     */
    public function init($module_exporter, $data = array()) {
        if($module_exporter instanceof HW_Module_Export) $this->module_export = $module_exporter;
        elseif($module_exporter instanceof HW_Import) $this->importer = $module_exporter;
        //context data
        if(is_array($data)) $this->add_vars( $data);
        $this->get_import_results();
        return $this;   //make chain
    }

    /**
     * add data
     * @param $vars
     */
    public function add_vars($vars) {
        if(is_array($vars)) $this->data = array_merge($this->data, $vars);
    }
    /**
     * @param string $item
     * @return string
     */
    public function get_import_results($item='') {
        //if(empty($this->module_export)) return ;
        if(empty($this->importer) && $this->module_export) $this->importer = $this->module_export->importer;
        $results = $this->importer->get_import_results();
        //posts
        $this->processed_posts = $this->importer->tracker->get_results('value', 'post');
        if(!empty($results['posts'])) {
            $this->processed_posts = array_merge($this->processed_posts, $results['posts']);
        }
        //terms
        $this->processed_terms = $this->importer->tracker->get_results('value', 'term');
        if(!empty($results['terms'])) {
            $this->processed_terms = array_merge($this->processed_terms, $results['terms']);
        }
        //menu items
        $this->processed_menu_items = $this->importer->tracker->get_results('value', 'menu_item');
        if(!empty($results['menu_items'])) {
            $this->processed_menu_items = array_merge($this->processed_menu_items, $results['menu_items']);
        }

        return $item? (isset($results[$item])? $results[$item]:'') : $results;
    }
    /**
     * add format string
     * @param $value
     * @return HWIE_Module_WXR_Reader
     */
    public function add_formatString($value) {
        $format = new self( array('value' => $value), 'hw:formatString');
        //$format->add_formatString_item();
        $this->element = $format;
        return $this ;
    }

    /**
     * add param element
     * @param $value
     * @param string $name
     * @example <param name="">xx</param>
     */
    public function add_value($value, $name='') {
        $atts = array();
        if($name) $atts['name'] = $name;
        $ele = new self( $atts, 'param', $value);
        if(!empty($this->element) && $this->element instanceof HWIE_Param) $this->element->add_child($ele->get());

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @param string $filter
     * @example <hw:import_post name="xx" value="ID" filter="callback"/>
     */
    public function add_import_post($name, $value ='ID', $filter = '') {
        $post = new self( array(
            'name' => $name,
            'value' => $value,
            'filter' => $filter
        ), 'hw:import_post');
        if(!empty($this->element) && $this->element instanceof HWIE_Param) {
            $this->element->add_child($post->get());
        }
        return $this;
    }

    /**
     * @param $name
     * @return $this
     * @example <hw:attachment _id="" filter=""/>
     */
    public function add_attachment($name) {
        $ele = new self( array('name' => $name), 'hw:attachment');
        if(!empty($this->element) && $this->element instanceof HWIE_Param) $this->element->add_child($ele->get());
        return $this;
    }
    /**
     * @param $name
     * @param string $filter
     * @return $this
     * @example <hw:import_term  name="xx" filter="callback"/>
     */
    public function add_import_term($name, $filter='') {
        $term = new self( array(
            'name' => $name,
            'filter' => $filter
        ),
            'hw:import_term');
        if(!empty($this->element) && $this->element instanceof HWIE_Param) {
            $this->element->add_child($term->get());
        }
        return $this;
    }

    /**
     * @param $name
     * @param string $type
     */
    public function add_var_tag($name, $type='constant') {
        $var = new self( array(
            'name' => $name,
            'type' => $type
        ), 'hw:import_var' );
        $this->element->add_child($var->get());
        return $this;
    }

    /**
     * add shortcode xml
     * @param array $atts
     * @param $content
     */
    public function add_shortcode_tag($atts = array(), $content='') {
        if(!is_array($atts) || !isset($atts['type'])) return ;
        //valid
        if(!$content && !empty($atts['value'])) $content = $atts['value'];
        $sc = new self( $atts, 'hw:shortcode', $content);
        $this->element->add_child($sc->get());

        return $this;
    }
    /**
     * parse import post
     * @return string
     */
    public function parse_import_post() {
        /*if($this->element->tagName !== 'hw:import_post') return '';
        $atts = $this->getAttributes();
        $name = isset($atts['name'])? sanitize_title($atts['name']) : '';
        $key = isset($atts['value'])? $atts['value'] : 'ID';
        if($name && isset($this->processed_posts[$name]) && isset($this->processed_posts[$name][$key])) {
            $val = $this->processed_posts[$name][$key] ;
            if(isset($atts['filter']) ) {
                $val = $this->filter_value($name, $val, (string)$atts['filter']);
                //$val = call_user_func(array($this->module_export, $atts['filter']), $val);
            }
            return $val;
        }*/
        return $this->parse_import_result('post', 'hw:import_post') ;
    }

    /**
     * filter value
     * @param $name
     * @param $val
     * @param $filter_func
     * @return mixed
     */
    private function filter_value($name, $val, $filter_func) {
        if($filter_func && $this->module_export && method_exists($this->module_export, $filter_func)) {
            $val = call_user_func(array($this->module_export, $filter_func), $val, $name);
        }
        elseif($filter_func && $this->importer && method_exists($this->importer, $filter_func)) {
            $val = call_user_func(array($this->importer, $filter_func), $val, $name);
        }
        return $val;
    }
    /**
     * get attachment as thumbnail
     * @return string
     */
    public function parse_import_thumbnail() {
        return $this->parse_import_result('post', 'hw:attachment');
        /*if($this->element->tagName !== 'hw:attachment') return '';
        $atts = $this->getAttributes();
        $id = $this->get_id();
        if($id && isset($this->processed_posts[$id])) {
            $val = $this->processed_posts[$id] ;
            if(is_array($val) && isset($val['post_id'])) $val = $val['post_id'];  //get id of post
            elseif(is_array($val) && isset($val['ID'])) $val = $val['ID'];

            //filter value
            if(isset($atts['filter'])) $val = $this->filter_value($id, $val, $atts['filter']);
            return $val;
        }*/
    }

    /**
     * get import item id
     * @return string
     */
    private function get_id() {
        $atts = $this->getAttributes();
        if(isset($atts['_id'])) $id = $atts['_id'] ;
        elseif(isset($atts['name'])) $id = sanitize_title($atts['name']);
        else $id = '';
        return $id;
    }
    /**
     * get term result
     */
    public function parse_import_term() {
        return $this->parse_import_result('term', 'hw:import_term');
    }

    /**
     * do shortcode
     * @return string
     * @example <hw:shortcode type="image" value="images/1.gif"/>
     */
    private function parse_shortcode() {
        $shortcodes = array('image' => 'hw_image', 'url'=> 'hw_url');
        $atts = $this->getAttributes();
        if(!isset($shortcodes[$atts['type']])) return ;

        $sc = $shortcodes[$atts['type']];
        unset($atts['type']);   //remove shortcode tag from attr
        //$content = (string)$this->get();
        $content = $this->get()->nodeValue; //->textContent
        if(!$content && isset($atts['value'])) $content = $atts['value'];

        return do_shortcode('['.$sc.' '.hwArray::array_to_attrs($atts).']'.$content.'[/'.$sc.']');
    }

    /**
     * parse var in wxr element
     * @param $data
     */
    public function parse_import_vars($data = array()) {
        if(is_array($data)) $this->add_vars($data);

        $atts = $this->getAttributes();
        //valid
        if(empty($atts['name'])) return '';

        $name = $atts['name'];
        if(!isset($atts['type'])) $atts['type'] = 'var';
        if(!isset($atts['default'])) $atts['default'] = ''; //default value

        if($atts['type'] == 'constant') {
            return constant($name);
        }
        elseif($atts['type']== 'var' ) return isset($this->data[$name])? $this->data[$name] : $atts['default'];    //context vars
        elseif(isset($GLOBALS[$name])) {    //global var
            return $GLOBALS[$name];
        }
        return $atts['default'];
    }
    /**
     * parse format string
     * @param $type
     * @param $tag
     */
    public function parse_import_result($type='post', $tag='') {
        if($this->element->tagName !== $tag) return '';

        $atts = $this->getAttributes();
        $id = $this->get_id();
        if($type=='post') {
            $data_result = $this->processed_posts;
        }
        elseif($type == 'term') {
            $data_result = $this->processed_terms;
        }
        else return ;
        //field value
        if(isset($atts['value'])) $key = $atts['value'];
        elseif(isset($atts['field'])) $key = $atts['field'];
        else $key= '';  //return all values with keys

        if($id && isset($data_result[$id])) {
            $val = $data_result[$id] ;
            if($key) {
                if(is_array($val)  && isset($val[$key])) $val = $val[$key];
                elseif(is_array($val) && isset($val['post_id'])) $val = $val['post_id'];  //get id of post
                elseif(is_array($val) && isset($val['ID'])) $val = $val['ID'];
            }

            //filter value
            if(isset($atts['filter'])) $val = $this->filter_value($id, $val, $atts['filter']);
            return apply_filters('hw_parse_import_result', $val, $type, $key);
        }
    }

    /**
     * @param string $item
     * @param array $context_data
     */
    public function parse_data($context_data = array(), $item='' ) {
        if(!$item) $item = $this->element ;
        $data = array();
        if(is_array($context_data) && count($context_data)) $this->add_vars($context_data) ;

        //if($item && $this->get_hw_element($item)) $this->element = $this->get_hw_element($item);
        $init_obj = $this->importer? $this->importer : $this->module_export;
        #foreach ($items as $item) {
            if($item->tagName == 'hw:formatString') {
                $atts = $this->getAttributes();
                if(empty($atts['value'])) return '';
                $values= array();
                $data['format'] = array('mask' => $atts['value'], 'values' => &$values);
                if($item->hasChildNodes()) {
                    foreach($item->childNodes as $child) {
                        if($child->nodeType == XML_TEXT_NODE || $child->nodeType ==XML_COMMENT_NODE) continue;
                        $values[] = (new self($child))->init($init_obj)->parse_data()->value;
                    }
                    //$val = $val->value;
                }
                //$values[] = $val;
                $data['value'] = vsprintf($atts['value'], $values);

            }
            elseif($item->tagName =='hw:import_post') {
                $value = $this->parse_import_post();
                $data['value'] = $value;
            }
            elseif($item->tagName == 'hw:attachment') {
                $value = $this->parse_import_thumbnail();//__print($value);
                $data['value'] = $value;
            }
            elseif($item->tagName== 'hw:import_term') {
                $data['value'] = $this->parse_import_term();
            }
            elseif($item->tagName =='param') $data['value'] = (string) $this->get();
        elseif($item->tagName =='hw:shortcode') $data['value'] = (string)$this->parse_shortcode();
        elseif($item->tagName == 'hw:import_var') $data['value'] = $this->parse_import_vars();
        #}

        return (object)$data ;
    }
}