<?php
/*
"<" => "&lt;",
">" => "&gt;",
'"' => "&quot;",
"'" => "&apos;",
"&" => "&amp;",
*/

/**
 * Class HW_XML
 */
abstract class HW_XML{
    /**
     * get namespace
     * @param $tag
     * @return string
     */
    public static function getNS($tag) {
        if(is_string($tag) && strpos($tag, ':')!==false) {
            $arr=explode(':', $tag);
            return reset($arr);
        }
        elseif($tag instanceof DOMElement) return $tag->namespaceURI;
    }

    /**
     * get attributes from XMl element
     * @param $ele
     * @param array $merge
     * @return array
     */
    public static function attributesArray($ele, $merge = array()) {
        if($ele instanceof SimpleXMLElement) {
            $atts = (array)$ele->attributes();
            $atts = isset($atts['@attributes'])? $atts['@attributes'] : array();
        }
        elseif($ele instanceof DOMElement) {
            $atts = array();

            foreach($ele->attributes as $attribute_name => $attribute_node)
            {
                /** @var  DOMNode    $attribute_node */
                $atts[$attribute_name] = $attribute_node->nodeValue;
            }
        }
        else $atts = array();

        if(is_array($merge)) $atts = array_merge($atts, $merge);
        return $atts;
    }
    /**
     * cdata value format
     * @param $value
     */
    public static  function cdata($value) {
        if ( seems_utf8( $value ) == false )
            $value = utf8_encode( $value );

        // $str = ent2ncr(esc_html($str));
        #return '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $value ) . ']]>';   //$dom->createTextNode($value)  #for entity special string
        return $value;
    }
    /**
     * recursive, function to transform XML data into pseudo E4X syntax ie. root.child.value = foobar
     * @param $xml
     * @param string $parent
     * @return int
     */
    public static function count_childs($xml,$parent="")
    {
        $child_count = 0;
        foreach($xml as $key=>$value)
        {
            $child_count++;
            if(self::count_childs($value,$parent.".".$key) == 0)  // no childern, aka "leaf node"
            {
                #print($parent . "." . (string)$key . " = " . (string)$value . "<BR>\n");
            }
        }
        return $child_count;
    }
    /**
    * escape string characters for inclusion in XML structure
    * @param string $str: xml string
    */ 
    public static function _escapeXML($str)
    {
        $translation = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES); 
        foreach ($translation as $key => $value) 
        {
            $translation[$key] = '&#' . ord($key) . ';'; 
        }
        $translation[chr(38)] = '&';   
        return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/","&#38;", strtr($str, $translation)); 
    }
    /**
     * import from array
     * @param array $data
     * @param $xml
     * @return $this
     */
    public static function simple_array_to_xml($data = array(), SimpleXMLElement $xml = null) {
        if($xml == null) return ;
        array_walk_recursive($data, array ($xml, 'addChild'));
        return $xml;
    }
    /**
     * function defination to convert array to xml
     * @depricated should be use DOMDocument instead
     * @param array $data
     * @param string $single_tag
     * @param string $plural_tag
     */
    public static function array_to_xml_params($data = array(),  $single_tag='param',$plural_tag='params') {
        $xml = new SimpleXMLElement('<rss></rss>') ;
        #if(empty($xml)) $xml = $this;
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item'.$key; //dealing with <0/>..<n/> issues that have no name attribute
                }
                $subnode = $xml->addChild($plural_tag);
                $subnode->addAttribute('name', $key);
                self::array_to_xml_params($value, $subnode);
            } else {
                //$xml->addChild("$key",htmlspecialchars("$value"));
                #$value = str_replace( ']]>', ']]]]><![CDATA[>', htmlspecialchars("$value") );
                $ele = $xml->addChild($single_tag, self::cdata($value));
                $ele->addAttribute('name', $key);
            }
        }
        return $xml->children();
    }

    /**
     * convert array to domdocument
     * @param array $data
     * @param DOMElement $element
     * @param DOMDocument $doc
     * @param string $single_tag
     * @param string $plural_tag
     */
    public static function array2dom($data = array(), $element = null, DOMDocument $doc=null, $single_tag='param', $plural_tag='params') {
        if($doc == null) $doc = new DOMDocument('2.0', 'UTF-8') ;
        if($element == null) $element = $doc;

        #if(empty($xml)) $xml = $this;
        foreach( $data as $key => $value ) {
            if( is_array($value) ) {
                if( is_numeric($key) ){
                    $key = 'item-'.$key; //dealing with <0/>..<n/> issues
                }
                #$dom = new DOMDocument('1.0', 'utf-8');
                $ele=$doc->createElement($plural_tag);
                $subnode = $element->appendChild($ele);#dom_import_simplexml, simplexml_import_dom
                $subnode->setAttribute('name', $key);
                self::array2dom($value, $subnode,$doc);
            } else {
                //$xml->appendChild("$key",htmlspecialchars("$value"));
                //$value = str_replace( ']]>', ']]]]><![CDATA[>', htmlspecialchars("$value") );
                $ele = $element->appendChild($doc->createElement($single_tag, self::cdata($value)));
                $ele->setAttribute('name', $key);
            }
        }
        return $doc;
    }

    /**
     * convert dom to array
     * @param DOMDocument $dom
     * @return array
     */
    public static function dom2array(DOMDocument $dom) {
        return self::xml2array(simplexml_load_string($dom->saveXML())); //simplexml_import_dom
    }
    /**
     * convert dom to simpleXML
     * @param DOMElement $dom
     * @param $class cast to child class of SimpleXMLElement
     * @return SimpleXMLElement
     */
    public static function whole_dom_to_simplexml($dom, $class= 'SimpleXMLElement') {
        if($dom instanceof DOMElement) $save_xml = $dom->ownerDocument->saveXML();
        elseif($dom instanceof DOMDocument) $save_xml = $dom->saveXML();
        return simplexml_load_string($save_xml, $class, LIBXML_NOCDATA|LIBXML_NOERROR);
    }
    /**
     * convert xml to array
     * @param SimpleXMLElement $xml
     * @param $singular_tag
     * @param $plural_tag
     * @return array
     */
    public static function xml2array( $xml, $singular_tag = 'param', $plural_tag= 'params') {
        $arr = array();
        if($xml instanceof SimpleXMLElement)
        foreach ($xml as $element) {
            $tag = $element->getName();
            if($tag !== $singular_tag && $tag !== $plural_tag) continue ;

            $atts = $element->attributes();
            $key = isset($atts['name'])? (string)$atts['name'] : '';
            $val = isset($atts['value'])? (string)$atts['value'] : HW_String::truncate_empty_lines((string)$element);
            if(isset($atts['type']) && $atts['type'] == 'bool') $val = $val? true: false;

            $e = get_object_vars($element);
            if (!empty($e)) {
                $val = (count($element->children()) && $tag == $plural_tag)? self::xml2array($element) : $val;
                if($key!=='') $arr[$key] = $val;
                else {$arr[] = $val;}
            }
            else {
                if($key !=='') $arr[$key] = trim($element);
                else $arr[] = trim($element);
            }
        }
        return $arr;
    }

    /**
     * the easiest way to get inner HTML of the node
     * @param $node
     * @return string
     */
    public static function get_inner_html( $node ) {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML( $child );
        }

        return $innerHTML;
    }

    /**
     * get the innerHTML value of a DOMNode
     * @param $Node
     * @return string
     */
    public static function getInnerHTML($Node)
    {
        $Body = $Node->ownerDocument->documentElement->firstChild->firstChild;
        $Document = new DOMDocument();
        $Document->appendChild($Document->importNode($Body,true));
        return $Document->saveHTML();
    }

    /**
     * shows can text-only content be extracted from a document
     * @param $Node
     * @param string $Text
     * @return string
     */
    function getTextFromNode($Node, $Text = "") {
        if ($Node->tagName == null)
            return $Text.$Node->textContent;

        $Node = $Node->firstChild;
        if ($Node != null)
            $Text = getTextFromNode($Node, $Text);

        while($Node->nextSibling != null) {
            $Text = getTextFromNode($Node->nextSibling, $Text);
            $Node = $Node->nextSibling;
        }
        return $Text;
    }

    /**
     * shows can text-only content be extracted from a document
     * @param $DOMDoc
     * @return mixed
     */
    function getTextFromDocument($DOMDoc) {
        return getTextFromNode($DOMDoc->documentElement);
    }

    /**
     * Changes the name of element $element to $newName.
     * @param $element
     * @param $newName
     * @example renameElement($element, 'invites');
     */
    function renameElement($element, $newName) {
        $newElement = $element->ownerDocument->createElement($newName);
        $parentElement = $element->parentNode;
        $parentElement->insertBefore($newElement, $element);

        $childNodes = $element->childNodes;
        while ($childNodes->length > 0) {
            $newElement->appendChild($childNodes->item(0));
        }

        $attributes = $element->attributes;
        while ($attributes->length > 0) {
            $attribute = $attributes->item(0);
            if (!is_null($attribute->namespaceURI)) {
                $newElement->setAttributeNS('http://www.w3.org/2000/xmlns/',
                    'xmlns:'.$attribute->prefix,
                    $attribute->namespaceURI);
            }
            $newElement->setAttributeNode($attribute);
        }

        $parentElement->removeChild($element);
    }
    /**
     * output dom xml to string
     * @param $xml
     * @return mixed|string
     */
    public static function output_xml_to_string(SimpleXMLElement $xml) {
        $dom = new DOMDocument('2.0');
        $dom->preserveWhiteSpace = false;   //set to false to print out pretty XML
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
//Echo XML - remove this and following line if echo not desired
        $xml_content = $dom->saveXML($dom, LIBXML_NOEMPTYTAG);  //please do not use htmlentities($dom->saveXML(..)) function
//turn on self-close tag
        $xml_content = preg_replace('#\>\<\/.+?\>#', ' />', $xml_content);
        $xml_content=html_entity_decode($xml_content, ENT_QUOTES, "utf-8");

        return $xml_content;
    }

    /**
     * convert DOMDocument to string
     * @param DOMDocument $dom
     * @param bool $noxmldecl
     * @return string
     */
    public static function output_dom_to_string( $dom, $noxmldecl=false) {
        if($dom instanceof DOMElement) {
            $doc = $dom->ownerDocument;
            #$doc = new DOMDocument() ;  //should create new doc for generating HTML from object
            #$doc->appendChild($doc->importNode($dom, true));
        }
        elseif($dom instanceof DOMDocument) $doc = $dom;
        if(empty($doc)) $doc = new DOMDocument();

        $doc->preserveWhiteSpace = false;   //set to false to print out pretty XML
        $doc->formatOutput = true;
        if($noxmldecl == false) {
            $xml_content = $doc->saveXML($doc );
        }
        else $xml_content = $doc->ownerDocument->saveXML($doc->ownerDocument->documentElement);
        return $xml_content;
    }

    /**
     * merge two or more XML documents into one
     * @param $data
     * @param $parent
     */
    public static function mergeDom($data, $parent=null) {
        #$args= func_get_args();
        $importChild = !$parent? true: false;   //import child nodes or whole element
        #if(isset($args[0]) && is_array($args[0])) $args = $args[0]; //refer to $data
        if($parent == null) list(,$parent) = each($data);
        //get Document
        if($parent instanceof SimpleXMLElement) $dom = dom_import_simplexml($parent)->ownerDocument;
        elseif($parent instanceof DOMElement) $dom = $parent->ownerDocument;
        elseif($parent instanceof HWIE_Param) $dom= $parent->getDoc();
        elseif($parent instanceof DOMDocument) $dom = $parent;
        #else return ;
        if(empty($dom)) $dom = new DOMDocument();

        if(is_array($data))
        foreach($data as $doc) {
            $ele = null;
            if($doc instanceof DOMDocument) $ele = $doc->documentElement;
            elseif($doc instanceof SimpleXMLElement) $ele = dom_import_simplexml($doc);
            elseif($doc instanceof DOMElement) $ele = $doc;
            elseif($doc instanceof HWIE_Param) $ele = $doc->get();
            else continue;

            if($ele) {
                #$dom->importNode($ele, true);
                $doc1=$dom->importNode($ele, true);
                if($importChild) {
                    foreach($doc1->childNodes as $child) {
                        $dom->documentElement->appendChild($child);
                    }
                }
                else $dom->documentElement? $dom->documentElement->appendChild($doc1) : $dom->appendChild($doc1);  #$dom->documentElement
            }
        }
        return $dom;
    }
    //use mergeDom method
    public static function mergeDom_withParent($data) {

    }
    /**
     *  Takes XML string and returns a boolean result where valid XML returns true
     */
    public static function is_valid_xml ( $xml ) {
        libxml_use_internal_errors( true );

        $doc = new DOMDocument('1.0', 'utf-8');

        $doc->loadXML( $xml );

        $errors = libxml_get_errors();

        return empty( $errors );
    }

    /**
     * check if string is xml format
     * @param $xml
     * @return bool
     */
    public static function string_is_xml_format($xml) {
        libxml_use_internal_errors( true );
        $t=simplexml_load_string($xml);
        $errors = libxml_get_errors();
        foreach($errors as $error) {
            if($error['code']!=201) return false;
        }
        return true;
    }
}
/**
 * Class HW_SimpleXMLElement
 */
 class HW_SimpleXMLElement extends SimpleXMLElement{
    /**
     *  escapes "predefined entities" that are in XML
     * @param $string
     * @return string
     */
    public static function xml_entities($string) {
        return strtr(
            $string,
            array(
                "<" => "&lt;",
                ">" => "&gt;",
                '"' => "&quot;",
                "'" => "&apos;",
                "&" => "&amp;",
            )
        );
    }

     /**
      * get element attribute
      * @param $attribute
      * @return string
      */
    public function xml_attribute( $attribute)
    {
        if(isset($this->attributes()->$attribute))
         return (string) $this->attributes()->$attribute ;
    }
    /**
     * A well-formed XML string or the path or URL to an XML document
     * parse xml
     * @param $file
     */
    public static function parser_xml ($file) {
        /*if(file_exists($file)) {    //read xml from file
            $xml=simplexml_load_file("note.xml") or die("Error: Cannot create object");
        }
        else {
            $xml=simplexml_load_string($file) or die("Error: Cannot create object");

        }*/
        $data_is_url =  (is_string($file) && !empty($file) && file_exists($file)? true : false);
        $xml=new self($file, null, $data_is_url);
        return $xml ;
    }
    /**
     * output dom xml to string
     * @return mixed|string
     */
    public function output_xml_to_string() {
        $dom = new DOMDocument('2.0');
        $dom->preserveWhiteSpace = false;   //set to false to print out pretty XML
        $dom->formatOutput = true;
        $dom->loadXML($this->asXML());
//Echo XML - remove this and following line if echo not desired
        $xml_content = $dom->saveXML($dom, LIBXML_NOEMPTYTAG);  //please do not use htmlentities($dom->saveXML(..)) function
//turn on self-close tag
        $xml_content = preg_replace('#\>\<\/.+?\>#', ' />', $xml_content);
        $xml_content=html_entity_decode($xml_content, ENT_QUOTES, "utf-8");

        return $xml_content;
    }

    /**
     * convert to string
     * @return string
     */
    public function asHTML(){
        $ele=dom_import_simplexml($this);
        $dom = new DOMDocument('2.0', 'utf-8');
        $element=$dom->importNode($ele,true);
        $dom->appendChild($element);
        return $dom->saveHTML();
    }
    /**
     * Returns the error message on improper XML
     * Must be tested with ===, as in if(isXML($xml) === true){}
     * @param $xml
     * @return bool
     */
    public static function isXML($xml){
        libxml_use_internal_errors(true);

        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);

        $errors = libxml_get_errors();

        if(empty($errors)){
            return true;
        }

        $error = $errors[0];
        if($error->level < 3){
            return true;
        }

        $explodedxml = explode("r", $xml);
        $badxml = $explodedxml[($error->line)-1];

        $message = $error->message . ' at line ' . $error->line . '. Bad XML: ' . htmlentities($badxml);
        return $message;
    }

    /**
     * xml to object conversion function
     * @param bool $force: set to true to always create 'text', 'attribute', and 'children' even if empty
     * @return
        object with attributs:
            (string) name: XML tag name
            (string) text: text content of the attribut name
            (array) attributes: array witch keys are attribute key and values are attribute value
            (array) children: array of objects made with xml2obj() on each child
     */
    public function xml2obj($force = false){

        $obj = new StdClass();

        $obj->name = $this->getName();

        $text = trim((string)$this);
        $attributes = array();
        $children = array();

        foreach($this->attributes() as $k => $v){
            $attributes[$k]  = (string)$v;
        }

        foreach($this->children() as $k => $v){
            $children[] = xml2obj($v,$force);
        }


        if($force or $text !== '')
            $obj->text = $text;

        if($force or count($attributes) > 0)
            $obj->attributes = $attributes;

        if($force or count($children) > 0)
            $obj->children = $children;


        return $obj;
    }

    /**
     * add an XML processing instruction to a SimpleXMLElement
     * @param $name
     * @param $value
     * @example <?xml-stylesheet type="text/xsl" href="xsl/xsl.xsl"?>
     */
    public function addProcessingInstruction( $name, $value )
    {
        // Create a DomElement from this simpleXML object
        $dom_sxe = dom_import_simplexml($this);

        // Create a handle to the owner doc of this xml
        $dom_parent = $dom_sxe->ownerDocument;

        // Find the topmost element of the domDocument
        $xpath = new DOMXPath($dom_parent);
        $first_element = $xpath->evaluate('/*[1]')->item(0);

        // Add the processing instruction before the topmost element
        $pi = $dom_parent->createProcessingInstruction($name, $value);
        $dom_parent->insertBefore($pi, $first_element);
    }


}

/**
 * Class HW_DOMDocument
 */
abstract class HW_DOMDocument extends DOMDocument{
    /**
     * generate value with cdata holder
     * @param $str
     */
    public function cdata($value) {
        if ( seems_utf8( $value ) == false )
            $value = utf8_encode( $value );

        // $str = ent2ncr(esc_html($str));
        #return '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $value ) . ']]>';
        return trim($value);
    }

    /**
     * output value with cdata wrapper
     * @param $value
     * @return string
     */
    public function cdata_output($value) {
        if ( seems_utf8( $value ) == false )
            $value = utf8_encode( $value );

        // $str = ent2ncr(esc_html($str));
        return '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $value ) . ']]>';
    }
    /**
     * convert DOMDocument to string
     * @param DOMDocument $dom
     * @param bool $noxmldecl
     * @return string
     */
    public function output_dom_to_string(  $noxmldecl=false, $dom= null) {
        if(is_null($dom)) $dom = $this;
        if($dom instanceof DOMElement) {
            $doc = $dom->ownerDocument;
            #$doc = new DOMDocument() ;  //should create new doc for generating HTML from object
            #$doc->appendChild($doc->importNode($dom, true));
        }
        elseif($dom instanceof DOMDocument) $doc = $dom;
        if(empty($doc)) $doc = new DOMDocument();

        $doc->preserveWhiteSpace = false;   //set to false to print out pretty XML
        $doc->formatOutput = true;
        if($noxmldecl == false) {
            $xml_content = $doc->saveXML($doc );
        }
        else $xml_content = $doc->ownerDocument->saveXML($doc->ownerDocument->documentElement);
        return $xml_content;
    }

    /**
     * @param $file
     * @param bool $noxmldecl
     */
    public function save_xml_to_file($file, $noxmldecl = false) {
        $content = $this->output_dom_to_string($noxmldecl);
        @file_put_contents($file, $content) ;
    }
}
?>