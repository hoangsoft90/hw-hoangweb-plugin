<?php
//APF Rules field
if ( ! class_exists( 'APF_hw_condition_rules' ) && class_exists('HW_APF_FormField')) :
    class APF_hw_condition_rules extends HW_APF_FormField {
        /**
         * check field for first initialize
         * @var array
         */
        private $checkInitialFields = array();
        /**
         * to serialize object
         * @var array
         */
        private $serialize_data =  array();

        /**
         * save apf field settings (no longer in use)
         * @var null
         */
        private $__aField = null;

        /**
         * Defines the field type slugs used for this field type.
         */
        public $aFieldTypeSlugs = array( 'hw_condition_rules', 'hw_rules');
        public $aDefaultKeys = array('show_root_field' => true,);

        /**
         * constructor
         * @param $class
         */
        public function __construct($class){
            parent::__construct($class, null);
            $this->prepare_actions();   //actions
        }
        /**
         * Returns the field type specific CSS rules.
         */
        protected function getStyles() {
            return ".admin-page-framework-input-label-container.hw_condition_rules { padding-right: 2em; }";
        }

        /**
         * return field type specific javascript inline
         * @return string
         */
        protected function getScripts( ){
            $_aJSArray = json_encode($this->aFieldTypeSlugs);
            return "
jQuery(document).ready(function(){
	jQuery().registerAPFCallback({
		added_repeatable_field: function(nodeField,sFieldType,sFieldTagID,sCallType) {
		    if(jQuery.inArray(sFieldType,$_aJSArray) <= -1) return;
		    __apf_rules_field.APFCallback__added_repeatable_field(nodeField,sFieldType,sFieldTagID,sCallType);
		},
		removed_repeatable_field: function(nodeField,sFieldType,sFieldTagID,sCallType){
		    if(jQuery.inArray(sFieldType,$_aJSArray) <= -1) return;
		    __apf_rules_field.APFCallback__removed_repeatable_field(nodeField,sFieldType,sFieldTagID,sCallType);
		},
		stopped_sorting_fields:function(oSortedFields,sFieldType,sFieldsTagID,iCallType){
		    console.log('stopped_sorting_fields',oSortedFields);
		},
		saved_widget:function(oWidget){
		    console.log('saved_widget',oWidget);
		},
		sorted_fields:function(node,sFieldType,sFieldsTagID){
            console.log('sorted_fields',node);
		}
	});

});
            ";
        }

        /**
         * enqueue scripts
         * Returns an array holding the urls of enqueuing scripts.
         * @return array|void
         */
        protected function getEnqueuingScripts( ) {
            //$this->prepare_actions();   //actions
            return array(
                array(     // if you need to set a dependency, pass as a custom argument array.
                    'src' => plugins_url('asset/apf-rules-field.js', __FILE__ ) ,     // path or url
                    'dependencies' => array( 'jquery' ),
                    'handle_id' => 'apf-ruels-field-script',
                    'in_footer' => true     //this allow  localize later & put in footer
                ),
                plugins_url('asset/apf-rules-field.js', __FILE__ ) , // a string value of the target path or url will work as well.
            );
        }
        /**
         * Returns an array holding the urls of enqueuing styles.
         * @return array
         */
        protected function getEnqueuingStyles() {
            return array(
                //dirname( __FILE__ ) . '/asset/style.css',
                array(
                    'src' => plugins_url('asset/style.css', __FILE__ ) ,
                    'handle_id' => 'apf-ruels-field-style',
                ),
            );
        }

        /**
         * parse wp query arguments form field value
         * @param $value
         */
        public static function parseQuery ($value) {
            $args = array(
                'post_status' => 'publish'
            );
            foreach ($value as $field) {
                if( $field['act'] == 'templates') {

                }
                if( $field['act'] == 'taxonomies') {
                    $args['taxonomy'] = $field['act_values'];   //allow taxonomy
                    //fix order processing to fill missed value
                    if(isset($args['tax_query']) && empty($args['tax_query']['taxonomy']) ) {
                        $args['tax_query']['taxonomy'] = $args['taxonomy'];
                    }

                }
                if($field['act'] == 'post_types' && $field['compare'] == '==') {    //post type
                    $args['post_type'] = $field['act_values'];
                }
                if($field['act'] == 'terms') {
                    if(!isset($args['tax_query'])) {
                        $args['tax_query'] = array();
                    }
                    $args['tax_query'][] = array (
                            'taxonomy' => isset($args['taxonomy'])? $args['taxonomy'] : '',
                            'field' => 'slug',
                            'terms' => array($field['act_values']),
                            'operator'=>'IN'
                    );
                }
            }
            //_print($args);
            return $args;
        }
        /**
         * valid condition from combine of fields value in your given relation
         * notice: this method should call in theme template files
         * @param $and_bind
         * @param $relation: and /or
         * @return bool
         */
        public static function check_fields_condition($and_bind, $relation = 'AND') {
            $result = array();    //first set to false
            $match_page = true;    //match template page
            $page = isset($GLOBALS['hw_rules_field_current_theme_template'])? $GLOBALS['hw_rules_field_current_theme_template'] : '';//basename( get_page_template() ) ;     //template page

            //valid array
            foreach($and_bind as $id => $cond) {
                if($cond['act'] == '-1') continue;
                $and_bind[$cond['act']] = $cond;
                unset($and_bind[$id]);
            }
            global $wp_query;

            //valid and condition
            //foreach($and_bind as $condition) {
            if(is_single() || is_tax() || is_category()) {
                $queried_object = get_queried_object();
            }
            //check template
            if(isset($and_bind['templates']) ) {
                $is_temp = HW__Template::check_template_page($and_bind['templates']['act_values']);

                $page = $and_bind['templates']['act_values'];

                if($is_temp) {
                    $match_page = $result['templates'] = ($and_bind['templates']['compare'] == '==')? true : false;
                }
                else $match_page = $result['templates'] = false;
            }
            else {
                $page = HW__Template::get_current_template_name();  //detect current template identifier
            }
            //check current post type
            if($match_page && isset($and_bind['post_types']) && is_single() )
            {
                if((($queried_object->post_type == $and_bind['post_types']['act_values'] && $and_bind['post_types']['compare'] == '==')
                    || ($queried_object->post_type != $and_bind['post_types']['act_values'] && $and_bind['post_types']['compare'] == '!=')))
                {
                    $result['post_types'] = true;
                }
                else $result['post_types'] = false;
            }
            //check current taxonomy
            if($match_page && isset($and_bind['taxonomies'])
                && (is_tax() ||  (isset($wp_query->tax_query) && !empty($wp_query->tax_query->queries))/*taxonomy page*/ || is_category() || is_single()))
            {
                //if single template
                if(is_single()) {
                    $terms = wp_get_object_terms( $queried_object->ID, $and_bind['taxonomies']['act_values'], array('fields' => 'ids'));
                    $result['taxonomies'] = (count($terms) && $and_bind['taxonomies']['compare'] === '==')? true : false;
                }
                else $result['taxonomies'] = ($queried_object->taxonomy===($and_bind['taxonomies']['act_values']) && $and_bind['taxonomies']['compare'] === '==') || ($queried_object->taxonomy!==($and_bind['taxonomies']['act_values']) && $and_bind['taxonomies']['compare'] !== '==');
            }

            //check current term taxonomy
            if($match_page && isset($and_bind['terms']) && isset($and_bind['taxonomies'])
                && (is_single()
                    || is_category() || is_tax($and_bind['taxonomies']['act_values'] ) || (isset($wp_query->tax_query) && !empty($wp_query->tax_query->queries)) ))
            {
                if(is_single()) {
                    $terms = wp_get_object_terms( $queried_object->ID, $and_bind['taxonomies']['act_values'], array('fields' => 'slugs'));
                    $result['terms'] = in_array($and_bind['terms']['act_values'], $terms) && $and_bind['terms']['compare'] === '=='? true : false;
                }
                else {
                    $result['terms'] = ($queried_object->slug == $and_bind['terms']['act_values'] && $and_bind['terms']['compare'] === '==');
                }
            }
            //check current page
            if($match_page && isset($and_bind['pages']) ) {
                global $post;

                $result['pages'] = ( is_page() && ((strcmp($post->ID , $and_bind['pages']['act_values']) == 0 && $and_bind['pages']['compare'] == '==') || (strcmp($post->ID , $and_bind['pages']['act_values'])!==0 && $and_bind['pages']['compare'] == '!=')) );
            }
            //check current post
            if($match_page && isset($and_bind['posts']) ) {
                global $post;
                $result['posts'] = ( is_single() && ((strcmp($post->ID , $and_bind['posts']['act_values'])==0 && $and_bind['posts']['compare'] == '==' ) || (strcmp($post->ID, $and_bind['posts']['act_values'])!==0 && $and_bind['posts']['compare'] == '!=' ) )) ;
            }
            //check user
            if($match_page && !empty($result['templates']) && !empty($and_bind['author'])) {
                $curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
                if(is_wp_error($curauth)) {
                    global $wp_query;
                    $curauth = $wp_query->get_queried_object();
                }
                $result['author'] = (is_object($curauth) && (($curauth->user_login == $and_bind['author']['act_values'] && $and_bind['author']['compare'] == '==') || ($curauth->user_login !== $and_bind['author']['act_values'] && $and_bind['author']['compare'] == '!=') ));
            }
            //extend condition to check
            $result = apply_filters('apf_check_fields_condition', $result, array(
                'match_page' => $match_page,
                'and_bind' => $and_bind,
                'page' => $page
            ));
            //}
            $result = !empty($result) && strtoupper($relation) == 'AND'? !in_array(false,$result) : in_array(true, $result);
            return array($page => $result);
        }
        /**
         * add actions
         */
        private  function prepare_actions() {
            add_action('admin_enqueue_scripts', array(&$this, '_admin_enqueue_scripts'),100);
            //add ajax callback
            add_action("wp_ajax_hw_apf_rules_field_get_values", array(&$this, "_hw_apf_rules_field_get_values"));
            add_action("wp_ajax_nopriv_hw_apf_rules_field_get_values", array(&$this, "_my_must_login"));

            add_filter('apf_rules_field_setting', array($this, '_apf_rules_field_setting'), 10,2);
            add_filter( 'template_include', array($this, '_var_template_include')); //template include filter
        }

        /**
         * include template file
         * @param $file: template file name
         */
        public function _var_template_include($file) {
            $GLOBALS['hw_rules_field_current_theme_template'] = basename($file);  //save current theme template file
            return $file;
        }
        /**
         * filter apf rules field data
         * @param $field
         * @param $field_attrs
         * @param $value
         */
        public function _apf_rules_field_setting($args ,$id) {
            if($args['aField']['field_id'] !== $id) return ;    //detect current field id, ignore for other
            //save fields values
            static $fields_value = array();

            //add attributes
            preg_match('/\[(\d+)\]/', $args['aAttributes']['name'], $res);  //find id
            if(count($res)>=2 ) {
                if(!isset($args['field_attrs']['data-id'])) $args['field_attrs']['data-id'] = $args['aField']['field_id'].'__'.$res[1];
                if(!isset($args['field_attrs']['data-name'])) $args['field_attrs']['data-name'] = $args['field']['name'];
                if(!isset($args['field_attrs']['data-fieldname'])) $args['field_attrs']['data-fieldname'] = $args['aField']['field_id'];
            }
            //build act_values field
            if($args['field']['name'] == 'act_values') {
                //get selected_act variable from ajax handle by user select act
                if(isset($args['aField']['selected_act']) ) {
                    $object = $args['aField']['selected_act'];
                }
                else $object = $this->getFieldValue('act', $args['aField']);

                //get binding fields
                if(isset($args['aField']['bindingFields']) && is_array($args['aField']['bindingFields'])) {
                    $bindingFields = $args['aField']['bindingFields'];
                }
                if(!empty($args['value'])) $fields_value[$object] = $args['value'];     //remind fields value

                //filter by post_type
                if(isset($bindingFields['post_types'])) {
                    $_pt = $bindingFields['post_types']['value'];
                }
                elseif(isset($fields_value['post_types'])) $_pt = $fields_value['post_types'];

                //get taxonomy value
                if(isset($bindingFields) && isset($bindingFields['taxonomies'])) {
                    $_tax = $bindingFields['taxonomies']['value'];

                }
                elseif(isset($fields_value['taxonomies'])) $_tax = $fields_value['taxonomies'];  //taxonomy
                //get tax term
                if(isset($bindingFields) && isset($bindingFields['terms'])) {
                    $_term = $bindingFields['terms']['value'];
                }
                elseif(isset($fields_value['terms']))  $_term = $fields_value['terms'];

                switch ($object){
                    case 'post_types':
                        $postypes = get_post_types('','names');
                        $args['field']['options'] = $postypes;

                        break;

                    case 'templates':
                        $args['field']['options'] = HW__Template::getTemplates();
                        break;

                    case 'taxonomies':
                        $args['field']['options'] = get_taxonomies();
                        break;
                    #terms
                    case 'terms':
                        $query_args = array(
                            'hide_empty' => 0,
                            'fields' => 'all'
                        );

                        if(isset($_pt)) {   //no need
                            //$query_args['post_type'] = $_pt;
                        }

                        //get terms taxonomies
                        if(isset($_tax)) {
                            $options = array();
                            $data = get_terms((array)$_tax, $query_args);
                            foreach($data as $t) {
                                $options[$t->slug] = $t->name;
                            }
                            $args['field']['options'] = $options;
                        }
                        else $args['field']['options'] = array();

                        break;
                    #posts
                    case 'posts':
                        $options = array();
                        $query_args = array('order' => 'ASC', 'tax_query' => array('relation' => 'AND',), 'showposts'=>'-1');
                        if(isset($_pt)) $query_args['post_type'] = $_pt;  //post_type
                        //taxonomy param
                        if(isset($_tax)) {
                            $query_args['taxonomy'] = $_tax;
                            //bind term taxonomy
                            if(isset($_term))
                            $query_args['tax_query'][] = array(
                                'taxonomy' => $_tax,
                                'field' => 'slug',
                                'terms' => array($_term),
                                'operator'=>'IN'
                            );
                        }

                        if(count($query_args)) {
                            $query = new WP_Query($query_args);
                            while($query->have_posts()) {
                                $query->the_post();
                                $options[get_the_ID()] = get_the_title();
                            }
                            $args['field']['options'] = $options;
                        }
                        else $args['field']['options'] = array();
                        break;
                    #pages
                    case 'pages':
                        /*$pages = get_pages();
                        $options = array();
                        foreach ($pages as $page) {
                            $options[$page->ID] = $page->post_title;
                        }*/
                        $options = HW__Template::get_pages_select(false);
                        $args['field']['options'] = $options;
                        break;
                    #users
                    case 'users':
                        $users = get_users(array(
                            'orderby'      => 'login',
                            'order'        => 'ASC',
                            'fields'       => 'all',
                        ));
                        $options = array();
                        foreach ($users as $user) {
                            $options[$user->ID] = $user->display_name;
                        }
                        $args['field']['options'] = $options;
                        break;
                    default;
                }
                $args = apply_filters('apf_rules_field_settings', $args, $object);
            };
        }
        /**
         * ajax handle callback to get act object values
         */
        public function _hw_apf_rules_field_get_values (){
            if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_apf_rules_field_get_values_nonce")) {
                exit("hacked !");
            }
            //params
            if(isset($_POST['object']) && $_POST['object']) $name = $_POST['object'];     //get object name
            if(isset($_POST['aField']) ) {  //get apf rules field setting
                $aField = $_POST['aField'];
                $aField = unserialize(base64_decode($aField));
            }
            if(isset($_POST['id'])) $field_id = $_POST['id'];
            if(isset($_POST['bindingFields'])) $bindingFields = $_POST['bindingFields'];    //binding Fields

            //valid
            if(!isset($aField) || !isset($field_id)) return ;

            //parse attribute
            $fname = 'act_values';
            $field_id = explode('__', $field_id);
            $field_name = $field_id[0].'['.$field_id[1].']['.$fname.']';        //field name
            $field_id = self::generate_id($field_name);     //field id

            //list all post types
            if($name == 'post_types') {
                $postypes = get_post_types('','names');

                $field = array(
                    'name' => $fname,
                    'type' => 'select',
                    'options' => $postypes
                );
            }
            //templates
            elseif($name == 'templates') {
                $field = array(
                    'name' => $fname,
                    'type' => 'select',
                    'options' => HW__Template::getTemplates(),

                );

            }
            //list taxonomies
            elseif($name == 'taxonomies') {
                $taxes = get_taxonomies();
                $field = array(
                    'name' => $fname,
                    'type' => 'select',
                    'options' => $taxes,

                );
            }
            //terms taxonomy
            elseif($name == 'terms') {
                $terms_tax = self::get_all_terms_taxonomies('');    //field terms placeholder
                $field = array(
                    'name' => $fname,
                    'type' => 'select',
                    'options' => ''
                );
            }
            //list all users
            elseif($name == 'users'){
                //users options placeholder
                $field = array(
                    'name' => $fname,
                    'type' => 'select',
                    'options' => ''
                );
            }
            //list all pages
            elseif($name == 'pages') {
                //placeholder
                $field = array(
                    'name' => $fname,
                    'type' => 'select',
                    'options' => ''
                );
            }
            //list posts/custom posts
            elseif($name == 'posts'){
                $field = array( //field posts placeholder
                    'name' => $fname,
                    'type' => 'select',
                    'options' => ''
                );
            }
            else $field = apply_filters('apf_rules_field_get_values', array(
                'name' => $fname,
                'type' => 'select',
                'options' => ''
            ),$name);

            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                //$result = json_encode($result);
                //display form field
                if(isset($field)) {
                    if(!isset($field['attributes'])) $field['attributes'] = array();
                    $field['attributes']['id'] = $field_id;
                    $field['attributes']['name'] = $field_name;
                    $field['attributes']['data-id'] = $_POST['id']; //field tag id
                    $field['attributes']['data-name'] = $fname;
                    $field['attributes']['data-fieldname'] = $_POST['fname'];

                    //inject some data
                    $aField['selected_act'] = $name;    //inject 'act' field value
                    if(isset($bindingFields)) $aField['bindingFields'] = $bindingFields;    //binding fields
                    //display field tag
                    echo $this->renderField($field, $aField);
                }
            }
            else {
                header("Location: ".$_SERVER["HTTP_REFERER"]);
            }

            die();
        }

        /**
         * must to authorize account
         */
        public function _my_must_login() {
            echo "You must log in to vote";
            die();
        }
        /**
         * @hook admin_enqueue_scripts
         * put scripts , styles to queues
         */
        public function _admin_enqueue_scripts() {
            $nonce = wp_create_nonce("hw_apf_rules_field_get_values_nonce");
            $ajax_handle = admin_url('admin-ajax.php?action=hw_apf_rules_field_get_values&post_id=&nonce='.$nonce);
            $this->serialize_data = array(
                'ajax_handle_url' => $ajax_handle,
                'IMAGES_URL' => HW_HOANGWEB_URL.'/images'
            );

        }
        /**
         * return all terms taxonomies base post type (from hwr-yarpp/includes/functions.php)
         * @param $post_type: post type name
         * @param $args: addition arguments
         */
        public static function get_all_terms_taxonomies($post_type, $args  = array()){
            $taxes = get_object_taxonomies($post_type,'names');
            $_args = array(
                'hide_empty' => false
            );
            if(is_array($args)) $_args = array_merge($_args, $args);
            return get_terms($taxes, $_args);
        }
        /**
         * get data to be serialize
         */
        public function get_serialize_data(){
            return $this->serialize_data;
        }
        /**
         * Returns the output of the field type.
         * @param $aField: field data
         */
        protected function getField( $aField ) {
            //init js data
            $fieldname = preg_replace('#\[\d+\]$#','',$aField['attributes']['name']);

            $this->serialize_data['aField'] = base64_encode(serialize($aField));    //stored aField
            $this->serialize_data['fieldname'] = $fieldname;

            $this->__aField = $aField;  //save field setting

            //localize script
            if(wp_script_is('apf-ruels-field-script')) {
                wp_localize_script('apf-ruels-field-script', '__apf_rules_field', $this->get_serialize_data());
            }

            return
                $aField['before_label']
                . $aField['before_input']
                . "<div class='repeatable-field-buttons'></div>"	// the repeatable field buttons will be replaced with this element.
                . $this->_getInputs( $aField )
                . $aField['after_input']
                . $aField['after_label'];
        }

        /**
         * get field value
         * @param $name
         * @param $aField
         */
        public function getFieldValue($name, $aField) {
            return isset($aField['attributes']['value'][$name])? $aField['attributes']['value'][$name] : '';    //field value
        }
        /**
         * generate field HTML
         * @param $aField
         * @return string
         */
        private function _getInputs( $aField ) {
            $_aOutput = array();
            //init js
            if(!isset($this->checkInitialFields[$this->serialize_data['fieldname']])) {
                $_aOutput[] = '<script>
                jQuery(function(){__apf_rules_field.initFields("'.$this->serialize_data['fieldname'].'");});
            </script>';
                $this->checkInitialFields[$this->serialize_data['fieldname']] = true;
            }
            //$this->getFieldValue('compare', $aField);

            $field_compare =  array(    //choose act
                'name' => 'compare',
                'type' => 'select',
                'options' => self::compare_operators()
            );
            $field_select_act = array(      //comparing
                'name' => 'act',
                'type' => 'select',
                'options' => $this->get_objects_type(),
                'attributes' => array(
                    'onchange' => '__apf_rules_field.request_object_values(this)'
                )
            );
            $field_values = array(  //result
                'name' => 'act_values',
                'type' => 'select',
                'options' => array(),
                'attributes' => array(
                    //'id' => ''
                )
            );
            /*foreach( ( array ) $aField['label'] as $_sSlug => $_sLabel ) {
                $_aAttributes = isset( $aField['attributes'][ $_sSlug ] ) && is_array( $aField['attributes'][ $_sSlug ] )
                    ? $aField['attributes'][ $_sSlug ] + $aField['attributes']
                    : $aField['attributes'];
                $_aAttributes = array(
                        'name'	=>	"{$_aAttributes['name']}[{$_sSlug}]",
                        'id'	=>	"{$aField['input_id']}_{$_sSlug}",
                        'value'	=>	isset( $aField['attributes']['value'][ $_sSlug ] ) ? $aField['attributes']['value'][ $_sSlug ] : '',
                    ) ;//+ $_aAttributes;
*/
            $_aAttributes = $aField['attributes'];
                $_aOutput[] = '<div class="apf-rules-field-container">';
                $_aOutput[] =  $this->renderField($field_select_act, $aField, $_aAttributes);
                $_aOutput[] = $this->renderField($field_compare, $aField, $_aAttributes);      //comparing field
                $_aOutput[] =  $this->renderField($field_values, $aField, $_aAttributes);
                //$_aOutput[] = $this->renderTableRow($aField, $_aAttributes);
                $_aOutput[] = '</div>';
            //}

            return implode( PHP_EOL, $_aOutput );
        }

        /**
         * return all actions name
         * @return array
         */
        public static function get_objects_type(){
            $types = array(
                '-1' => __('----Chọn----'),
                'post_types' => __('Post Type'),
                'templates' => __('Templates'),
                'taxonomies' => __('Taxonomies'),
                'terms' => __('Terms'),
                'pages' => __('Trang'),
                'posts' => __('Posts'),
                'users' => __('Users')
            );
            return apply_filters('APF_hw_condition_rules-types', $types);
        }

        /**
         * compare operations
         * @return mixed|void
         */
        private static function compare_operators() {
            $operators = array(
                '==' => 'Bằng nhau',
                '!=' => 'Khác nhau'
            );
            return apply_filters('APF_hw_condition_rules-compare-operators', $operators);
        }
        /**
         * create field HTML
         * @param $field: field setting
         * @param $aField: all field data generate by APF framework
         * @param $_aAttributes: attributes
         */
        private function renderField($field, $aField , $_aAttributes = null)
        {
            //if(empty($aField) && $this->__aField) $aField= $this->__aField;    //get common apf field setting
            if(empty($_aAttributes)) $_aAttributes = $aField['attributes'];  //get field attributes if not pass to function

            $type = isset($field['type'])? $field['type'] : 'text';
            $field_base = $_aAttributes['name'];
            $name = $field_base.'['.$field['name'].']';    //post type field
            $id = self::generate_id($name) ;    //self::generate_id($aField['field_id'].'_'.$field['name']);   //self::generate_id($name); //id
            //build attributes
            $field_attrs = $this->uniteArrays(isset($field['attributes'])? $field['attributes'] : array(), array('name'=>$name, 'id'=>$id));
            $value = isset($aField['attributes']['value'][$field['name']])? $aField['attributes']['value'][$field['name']] : '';    //field value

            //filter field setting
            apply_filters('apf_rules_field_setting', array(
                'aField' => &$aField,
                'field' => &$field,
                'field_attrs' => &$field_attrs,
                'aAttributes' => $_aAttributes,
                'value' => $value
            ), $aField['field_id']);

            //other field data
            $description = isset($field['description']) ? '<p><em>'.$field['description'].'</em></p>' : '';

            $aOutput[] = '<div class="hw-apf-field-wrapper">';
            if($type  == 'text') {  //input tag
                $field_attrs['value'] = $value;
                $aOutput[]='<label><input type="text" '.$this->generateAttributes($field_attrs).'/></label>';
                $aOutput[] = $description;
            }
            elseif($type == 'select') { //select tag
                if(is_string($field['options'])) $field['options'] = explode(',',$field['options']);    //parse options key
                $aOutput[] = '<select '.$this->generateAttributes($field_attrs).'>';
                if(is_array($field['options'])) {
                    foreach($field['options'] as $name => $text) {
                        //if(is_numeric($name)) $name = $text;
                        $selected = ($name == $value)? 'selected="selected"' : '';
                        $option_id = $id.'_'.$name;

                        $aOutput[] = '<option id="'.$option_id.'" value="'.$name.'" '.$selected.'>'.$text.'</option>';
                    }
                }
                $aOutput[] = '</select>';
                $aOutput[] = $description;   //field desc
            }
            $aOutput [] = '</div>';
            return join("\n", $aOutput);
        }
        /**
         * valid field id from their name or any give string
         * @param $str
         */
        private static  function generate_id($str){
            return class_exists('HW_Validation') ? HW_Validation::valid_objname($str) : $str;
        }
    }
endif;