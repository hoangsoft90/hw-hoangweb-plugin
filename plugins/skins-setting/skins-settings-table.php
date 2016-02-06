<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 10/10/2015
 * Time: 00:02
 */
/**
 * Class HW_List_Table_Widgets_settings
 * cover from hw-any-widget-classes/includes/widget-feaures/saveconfig/awc-widgets-config.php
 */
class HW_List_Table_Skins_settings extends HW_List_Table{
    /**
     * main class constructor
     */
    function __construct() {
        parent::__construct(array(
            'singular' => 'hw-list-table-skins-settings',
            'plural'   => 'hw-list-table-skins-settings',
            'ajax'     => false,
        ));

        //action bulks
        $this->actions_bulk();

        //screen options
        #add_filter('set-screen-option', array($this, 'test_table_set_option'), 10, 3);
        add_action( 'admin_head', array( &$this, 'admin_header' ) );
    }
    /**
     * actions bulk
     */
    private function actions_bulk() {
        //bulk actions
        $action = $this->current_action();
        if(!$action) return;

        //delete single item
        if($action == 'delete' && !empty($_GET['hw_skinopt_id']) && function_exists('hwskin_delete_skins_enqueue')) {
            hwskin_delete_skins_enqueue(array('id' => $_GET['hw_skinopt_id']));
        }
        //perform more items
        if(isset($_POST['hw_ids'])) {
            // Grab ids data from $_POST.
            $ids  = isset( $_POST['hw_ids'] ) ? (array) $_POST['hw_ids'] : array();
            if($action == 'delete') {
                foreach($ids as $id) {
                    hwskin_delete_skins_enqueue(array('id' => $id));
                }
            }

            unset( $_POST ); // Reset the $_POST variable in case user wants to perform one action after another.
        }
    }
    /**
     * Processes bulk installation and activation actions.
     *
     * The bulk installation process looks either for the $_POST
     * information or for the plugin info within the $_GET variable if
     * a user has to use WP_Filesystem to enter their credentials.
     *
     * @since 2.2.0
     */
    /*public function  hw_process_bulk_actions() {
        // Bulk activation process.
        if(!$this->current_action()) return;
        check_admin_referer( 'bulk-' . $this->_args['plural'] );


    }*/
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
    function column_object($item) {
        $actions = array(
            //'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item->id),
            'delete'    => sprintf('<a href="javascript:void(0)" onclick="if(confirm(\'Are you sure ?\')) window.location=\'?page=%s&tab=%s&action=%s&hw_skinopt_id=%s\';">Xóa</a>',$_REQUEST['page'],hw__req('tab'),'delete',$item->id),
        );

        return sprintf('%1$s %2$s', $item->object, $this->row_actions($actions) );
    }
    /**
     * @param $item
     * @return string
     */
    function column_setting($item) {
        //ajax to get pretty widgetconfig
        $nonce = wp_create_nonce("_hw_display_pretty_skin_enqueue_nonce");
        $link = admin_url('admin-ajax.php?action=hw_display_pretty_skin_enqueue&config_id=%s&nonce='.$nonce);
        $link = sprintf($link, $item->id);

        $inline_content = '<div class=""></div>';
        return '<textarea class="" readonly="true">'.$item->skin. '</textarea><br/><a target="_blank" class="hw-pretty-skinconfig-ajax hw-colorbox" href="'.$link.'">Xem</a>';
    }

    /**
     * status column
     * @param $item
     * @return string
     */
    function column_status($item) {
        //ajax to get pretty widgetconfig
        $nonce = wp_create_nonce("hw_update_skin_enqueue_status_nonce");
        $active_link = sprintf(admin_url('admin-ajax.php?action=hw_update_skin_enqueue_status&config_id=%s&status=1&nonce='.$nonce), $item->id);
        $inactive_link = sprintf(admin_url('admin-ajax.php?action=hw_update_skin_enqueue_status&config_id=%s&status=0&nonce='.$nonce), $item->id);

        return !empty($item->status)? '<a  href="'.$inactive_link.'" class="hw-colorbox-ajax" data-status="1" title="Cập nhật skin enqueue"><span class="hw-active-icon">Hủy kích hoạt</span></a>' : '<a data-status="0" title="Cập nhật skin enqueue" href="'.$active_link.'" class="hw-colorbox-ajax"><span class="hw-inactive-icon">Kích hoạt</span></a>';
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
            case 'blog_id':
            case 'object':
            case 'setting':
            case 'status':
            default:
                return print_r( $item[$column_name], true ) ; //Show the whole array for troubleshooting purposes
        }
    }
    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items() {
        $screen = get_current_screen();

        /* -- Preparing your query -- */
        //AWC_WidgetFeature_saveconfig::get_widgets_settings();
        $query = "SELECT * FROM ".HW_SKIN::SKINS_SETTINGS_DB;

        #$per_page = $this->get_items_per_page('per_page', 5);
        $per_page = HW_Screen_Option::get('hw_module_setting_page')->get_option('per_page', 5);

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
        if( HW_Module_Settings_page::PAGE_SLUG !== $page )
            return;

        echo '<style type="text/css">';
        echo '.wp-list-table .column-id { width: 5%; }';
        echo '.wp-list-table .column-booktitle { width: 40%; }';
        echo '.wp-list-table .column-author { width: 35%; }';
        echo '.wp-list-table .column-isbn { width: 20%; }';
        echo '</style>';
    }
    /**
     * ajax callback to print skin config
     */
    public static function _hw_display_pretty_skin_enqueue() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "_hw_display_pretty_skin_enqueue_nonce")) {
            exit("No naughty business please");
        }
        if(!isset($_REQUEST['config_id'])) {
            echo 'Không tìm thấy config này';
            return ;
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            $setting = hwskin_get_skins_enqueues(array('id'=>$_REQUEST['config_id']));
            if(count($setting)) {
                $skin = reset($setting);
                $skin_info = $skin->instance->get_skin_info($skin->hash_skin);
                #$t=new HW_SKIN();$t->get_skin_info()
                include(dirname(__FILE__). '/template/pretty-skinconfig.php');
            }

        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

    /**
     * update skin config status
     */
    public static function _hw_update_skin_enqueue_status() {
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_update_skin_enqueue_status_nonce")) {
            exit("No naughty business please");
        }
        if(!isset($_REQUEST['config_id'])) {
            echo 'Không tìm thấy config này';
            return ;
        }

        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            //$result = json_encode($result);
            hwskin_update_skin_enqueue_status(hw__req('config_id'), hw__req('status'));
            echo 'Đã '. (hw__req('status')? "kích hoạt": "hủy kích hoạt"). " preload cho skin ID=". hw__req('config_id') ;

        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }
}
//ajax handle
add_action("wp_ajax_hw_display_pretty_skin_enqueue", "HW_List_Table_Skins_settings::_hw_display_pretty_skin_enqueue");
add_action("wp_ajax_nopriv_hw_display_pretty_skin_enqueue",  "HW_List_Table_Skins_settings::_hw_display_pretty_skin_enqueue");

add_action("wp_ajax_hw_update_skin_enqueue_status", "HW_List_Table_Skins_settings::_hw_update_skin_enqueue_status");
add_action("wp_ajax_nopriv_hw_update_skin_enqueue_status",  "HW_List_Table_Skins_settings::_hw_update_skin_enqueue_status");