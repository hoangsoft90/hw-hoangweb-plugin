<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 21:16
 */
HW_HOANGWEB::load_fieldtype('APF_hw_admin_table');
/**
 * Class HWAWC_SaveWidgets_options
 */
class HWAWC_SaveWidgets_options extends AdminPageFramework{
    /**
     * page slug constant
     */
    const PAGE_SLUG = 'hw_widgets_settings';
    /**
     * setup form fields
     */
    public function setUp() {
        // Set the root menu
        $this->setRootMenuPage( 'Settings' );        // specifies to which parent menu to add.

        // Add the sub menus and the pages
        $this->addSubMenuItems(
            array(
                'title'    =>    'Lưu cấu hình widgets',        // the page and menu title
                'page_slug'    =>    self::PAGE_SLUG         // the page slug
            )
        );

        //define tabs
        $this->addInPageTabs(
            self::PAGE_SLUG,    // set the target page slug so that the 'page_slug' key can be omitted from the next continuing in-page tab arrays.
            array(
                'tab_slug'    =>    'widgets-config',    // avoid hyphen(dash), dots, and white spaces
                'title'        =>    __( 'Cấu hình widgets' ),
            )
            /*,array(
                'tab_slug'      => 'widgets-shortcode',
                'title'         => __('Tab')
            )*/

        );
        $this->setInPageTabTag( 'h2' );        // sets the tag used for in-page tabs
        //init fields
        $this->addSettingFields(
            array(
                'field_id' => 'list_saved_widgets_setting',
                'type' => 'hw_admin_table',
                'title' => '',
                'show_title_column' => false,
                'WP_List_Table' => 'HW_List_Table_Widgets_settings',
                //params
                'columns' => array(
                    'id' => __('ID'),
                    'name'=>__('Tên'),
                    'group'=>__('Nhóm'),
                    'widget' => __('Widget'),
                    'description'=>__('Mô tả'),
                    'setting' => __('Config')
                ),
                'sortable_columns' => array(
                    'id' => array('id', false),     #true the column is assumed to be ordered ascending,
                    'name' => array('name' ,false),     #if the value is false the column is assumed descending or unordered.
                    'group' => array('group', false)
                )
            )
        );
        /*$this->addSettingFields(
            array(
                'field_id' => 'test',
                'type' => 'text',
                'title' => 'Title 1',

            )
        );*/
        //actions
        if(class_exists('HW_HELP')) $page_hook = HW_HELP::load_settings_page_hook_slug(self::PAGE_SLUG);
        else $page_hook = 'load-settings_page_'.self::PAGE_SLUG;

        add_action( $page_hook, array($this, '_add_options') );
        if(class_exists('HW_HOANGWEB') && HW_HOANGWEB::is_current_screen('hw_widgets_settings')) {
            add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
        }
    }


    /**
     * @hook $page_hook
     */
    public function _add_options() {
        $option = 'per_page';   //wordpress api
        $args = array(
            'label' => 'Kết quả hiển thị',
            'default' => 10,
            'option' => 'hw_items_per_page'
        );
        add_screen_option( $option, $args );
    }
    /**
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_hw_widgets_settings() {

    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts() {
        HW_Libraries::enqueue_jquery_libs('jquery-colorbox');

    }
    /**
     * The pre-defined validation callback method.
     *
     * Notice that the method name is validation_{instantiated class name}_{field id}. You can't print out inside callback but stored in session variale instead
     *
     * @param    string|array    $sInput        The submitted field value.
     * @param    string|array    $sOldInput    The old input value of the field.
     */
    public function validation_HWAWC_SaveWidgets_options( $sInput, $sOldInput ) {
        return $sInput;
    }
    /**
     * initial
     * @hook init
     */
    public static function init(){
        //if(class_exists('HW_SKIN')) hwskin_load_APF_Fieldtype(HW_SKIN::SKIN_FILES);

        //init custom field type
        if(class_exists('APF_hw_admin_table')) {
            new APF_hw_admin_table('HWAWC_SaveWidgets_options');
            new HWAWC_SaveWidgets_options();
        }

    }
}
if(is_admin()) {
    //add_action('init', 'HWAWC_SaveWidgets_options::init');    //we move on widget feature setting tab

}
/**
 * Class HW_List_Table_Widgets_settings
 */
class HW_List_Table_Widgets_settings extends HW_List_Table{

    /**
     * main class constructor
     */
    function __construct() {
        parent::__construct(array(
            'singular' => 'hw-list-table-widgets-settings',
            'plural'   => 'hw-list-table-widgets-settings',
            'ajax'     => false,
        ));

        //action bulks
        $this->actions_bulk();

        //screen options
        add_filter('set-screen-option', array($this, 'test_table_set_option'), 10, 3);
        add_action( 'admin_head', array( &$this, 'admin_header' ) );


    }

    /**
     * @hook set-screen-option
     * @param $status
     * @param $option
     * @param $value
     * @return mixed
     */
    public function test_table_set_option($status, $option, $value) {
        return $value;
    }

    /**
     * ajax callback to print widget config
     */
    public static function _hw_display_pretty_widgetconfig() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "_hw_display_pretty_widgetconfig_nonce")) {
            exit("No naughty business please");
        }
        if(!isset($_REQUEST['config_id'])) {
            echo 'Không tìm thấy config này';
            return ;
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            $setting = AWC_WidgetFeature_saveconfig::get_widget_setting($_REQUEST['config_id']);
            include('template/pretty-widgetconfig.php');

        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
    /**
     * actions bulk
     */
    private function actions_bulk() {
        //bulk actions
        $action = $this->current_action();
        if(!$action) return;

        //delete single item
        if($action == 'delete' && !empty($_GET['hw_widopt_id']) ) {
            AWC_WidgetFeature_saveconfig::del_widget_setting($_GET['hw_widopt_id']);
        }
        //perform more items
        if(isset($_POST['hw_ids'])) {
            // Grab ids data from $_POST.
            $ids  = isset( $_POST['hw_ids'] ) ? (array) $_POST['hw_ids'] : array();
            if($action == 'delete') {
                foreach($ids as $id) {
                    AWC_WidgetFeature_saveconfig::del_widget_setting( $id);
                }
            }
            unset( $_POST ); // Reset the $_POST variable in case user wants to perform one action after another.
        }
    }

    /**
     * columns callback
     * @param $item
     * @return string|void
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="hw_ids[]" value="%s" />', $item->id
        );
    }
    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            //The code that goes before the table is here
            //echo"Hello, 44657567";
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there
            //echo"Hi, I'm asdfge";
        }
    }
    /**
     * each column WordPress looks for methods called column_{key_name}
     * @param $item
     */
    function column_name($item) {
        $actions = array(
            //'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item->id),
            'delete'    => sprintf('<a href="?page=%s&action=%s&hw_widopt_id=%s">Xóa</a>',$_REQUEST['page'],'delete',$item->id),
        );

        return sprintf('%1$s %2$s', $item->name, $this->row_actions($actions) );
    }

    /**
     * display column content
     * @param $item
     * @return mixed
     */
    function column_group($item) {
        $groups = AWC_WidgetFeature_saveconfig::get_groups();
        return isset($groups[$item->_group])? $groups[$item->_group] : $item->_group;
    }

    /**
     * @param $item
     * @return string
     */
    function column_setting($item) {
        //ajax to get pretty widgetconfig
        $nonce = wp_create_nonce("_hw_display_pretty_widgetconfig_nonce");
        $link = admin_url('admin-ajax.php?action=hw_display_pretty_widgetconfig&config_id=%s&nonce='.$nonce);
        $link = sprintf($link, $item->id);

        $inline_content = '<div class=""></div>';
        return '<textarea class="" readonly="true">'.$item->setting. '</textarea><br/><a class="hw-pretty-widfea-saveconfig-ajax" href="'.$link.'">Xem</a>';
    }

    /**
     * To avoid the need to create a method for each column there is column_default
     * @param $item
     * @param $column_name
     */
    function column_default( $item, $column_name ){
        $item = (array) $item;  //cast to array
        switch( $column_name ) {
            case 'id':
            case 'name':
            case 'description':
            case 'setting':
            case 'group':
                return $item[ $column_name ];
            case 'widget':
                return '<a href="'.admin_url('widgets.php').'" target="_blank">'.$item[$column_name] . '</a>';
            default:
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
        }
    }
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
        $screen = get_current_screen();

        /* -- Preparing your query -- */
        //AWC_WidgetFeature_saveconfig::get_widgets_settings();
        $query = "SELECT * FROM ".AWC_WidgetFeature_saveconfig::DB_TABLE;

        $per_page = $this->get_items_per_page('hw_items_per_page', 20);
        if(!isset($_GET['orderby']) ) $_GET['orderby'] = 'id';  //if no specific order with column, get id column

        $query = $this->get_items_sql_orderby($query,'DESC');
        $query = $this->get_items_sql_pagination($query, $per_page);

        $this->_prepare_items($query);
    }
    /**
     * Styling the table. Currently the table is styled to the WordPress defaults.
     * @hook admin_head
     */
    function admin_header() {
        $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
        if( HWAWC_SaveWidgets_options::PAGE_SLUG != $page )
            return;

        echo '<style type="text/css">';
        echo '.wp-list-table .column-id { width: 5%; }';
        echo '.wp-list-table .column-booktitle { width: 40%; }';
        echo '.wp-list-table .column-author { width: 35%; }';
        echo '.wp-list-table .column-isbn { width: 20%; }';
        echo '</style>';
    }
}
//ajax handle
add_action("wp_ajax_hw_display_pretty_widgetconfig", "HW_List_Table_Widgets_settings::_hw_display_pretty_widgetconfig");
add_action("wp_ajax_nopriv_hw_display_pretty_widgetconfig",  "HW_List_Table_Widgets_settings::_hw_display_pretty_widgetconfig");