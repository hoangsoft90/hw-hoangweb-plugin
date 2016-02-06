<?php
#thank to: http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
/**
 * Class HW_List_Table_Interface
 */
interface HW_List_Table_Interface {
    /**
     * @hook admin_footer
     * @return mixed
     */
    public function _admin_footer();
}

/**
 * Class HW_List_Table
 */
abstract class HW_List_Table extends WP_List_Table implements HW_List_Table_Interface{
    /**
     * columns
     * @var
     */
    private $columns = array();
    /**
     * sortable columns
     * @var
     */
    private $sortable_columns = array();

    /**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    function __construct($args = array()) {
        $args = wp_parse_args( $args, array(
            'plural' => 'hw_wp_list_tables',    //plural label, also this well be one of the table css class
            'singular' => 'hw_wp_list_table',   //Singular label
            'ajax' => false,    //We won't support Ajax for this table
            'screen' => null,
        ) );
        parent::__construct($args );

        $this->columns = array('cb'   => '<input type="checkbox" />');
        //screen options
        $this->add_screen_options();
        //print something in  admin footer
        add_action( 'admin_footer', array( $this, '_admin_footer' ) );
    }
    /**
     * @hook admin_footer
     * @return mixed
     */
    public function _admin_footer() {

    }
    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav( $which ) {
        if ( $which == "top" ){
            //The code that goes before the table is here
            echo"Hello, I'm before the table";
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there
            echo"Hi, I'm after the table";
        }
    }
    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns() {
        return $this->columns;
    }

    /**
     * add extra/override columns
     * @param array $columns
     * @return array
     */
    public function _set_columns($columns = array()) {
        if(!is_array($columns)) $columns = array();     //valid
        $this->columns = array_merge($this->columns ,$columns);
        return $this->columns;
    }
    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns() {
        $sortable = $this->sortable_columns;
        /*array(
            //'id' => array('id', true),     #true the column is assumed to be ordered ascending,
            'name' => array('name' ,false),     #if the value is false the column is assumed descending or unordered.
            'group' => array('group', false)
        );*/
        return $sortable;
    }

    /**
     * prepare sortable columns
     * @param $sortable_columns
     * @return array
     */
    public function _set_sortable_columns($sortable_columns) {
        if(!is_array($sortable_columns)) $sortable_columns = array();     //valid
        $this->sortable_columns = array_merge($this->sortable_columns ,$sortable_columns);
        return $this->sortable_columns;
    }
    /**
     * With this information you can write a method for sorting our example data:
     * @param $a
     * @param $b
     * @return int
     */
    public function usort_reorder( $a, $b ) {
        // If no sort, default to title
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'booktitle';
        // If no order, default to asc
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp( $a[$orderby], $b[$orderby] );
        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }

    /**
     * orderby sql query
     * @param string $query
     * @param string $orderby: default order by
     * @return string
     */
    public function get_items_sql_orderby($query = '', $default_order = 'DESC') {
        if(empty($default_order)) $default_order = 'DESC';      //valid

        /* -- Ordering parameters -- */
        //Parameters that are going to be used to order the result
        $orderby = !empty($_GET["orderby"]) ? @mysql_real_escape_string($_GET["orderby"]) : '';
        $order = !empty($_GET["order"]) ? @mysql_real_escape_string($_GET["order"]) : $default_order;
        if(!empty($orderby) ){
            $query.=' ORDER BY '.$orderby;
            if(!empty($order)) $query .= ' '.$order;
        }

        return $query;
    }

    /**
     * setup paging sql
     * prepare sql for paging
     * @param string $query
     * @param $perpage: items per page
     */
    public function get_items_sql_pagination($query = '', $perpage = 5) {
        global $wpdb;

        if(empty($query)) return;   //invalid sql

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows

        //How many to display per page?
        //$perpage = $this->get_items_per_page('hw_items_per_page', 5);
        $current_page = $this->get_pagenum();

        //Which page is this?
        $paged = !empty($_GET["paged"]) ? ($_GET["paged"]) : '';

        //Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }

        //How many pages do we have in total?
        $totalpages = is_numeric($perpage) && $perpage? ceil($totalitems/$perpage) : 1;

        //adjust the query to take pagination into account
        if(!empty($paged) && !empty($perpage) && is_numeric($perpage)){
            $offset=($paged-1)*$perpage;
            $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters
        return $query;
    }
    /**
     * Prepare the table with different parameters, pagination, columns and table elements (demo)
     */
    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen();

        /* -- Preparing your query -- */
        $query = "SELECT * FROM hw_widgets_settings";

        $query = $this->get_items_sql_orderby($query);
        $query = $this->get_items_sql_pagination($query);

        $this->_prepare_items($query);
    }

    /**
     * prepare items
     * @param $query
     */
    public function _prepare_items($query) {
        global $wpdb;

        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();

        //$_wp_column_headers[$screen->id] = $columns;
        $this->_column_headers = array($columns, $hidden, $sortable);
        /* -- Fetch the items -- */
        $this->items = $wpdb->get_results($query);
        //usort( $this->items, array( &$this, 'usort_reorder' ) );      //If you're retrieving the data from the database, (which is most likely) it's of course best to use SQL's ORDERBY directly.
    }
    /**
     * each column WordPress looks for methods called column_{key_name} (hope override by child class)
     */
    function column_name($item) {   //example

    }

    /**
     * To avoid the need to create a method for each column there is column_default (hope override by child class)
     */
    function column_default( $item, $column_name ){
        $item = (array) $item;  //cast to array
        switch( $column_name ) {
            case 'HW_List_Table_debug':
                return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
            default:
                return $item[ $column_name ];
        }
    }

    /**
     * a method column_{column} for rendering a column. The cb-column is a special case.
     * This method currently will not be processed because we have to tell the class about the new column by extending the method get_columns():
     * @param $item
     * @return string|void
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="ids[]" value="%s" />', $item->id
        );
    }
    /**
     * Bulk actions
     * Bulk action are implemented by overwriting the method get_bulk_actions() and returning an associated array:
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Xóa'
        );
        return $actions;
    }


    /**
     * If there are no items in the list the standard message is "No items found."
     */
    public function no_items() {
        _e( 'Không có dữ liệu !' );
    }
    /**
     * Display the rows of records in the table
     * @return string, echo the markup of the rows
     */
    function display_rowsX() {      //use default

        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list( $columns, $hidden ) = $this->get_column_info();       //avaible on screen options, see: http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
        $columns = $this->get_columns();

        //Loop for each record
        if(!empty($records)){
            foreach($records as $rec){

                //Open the line
                echo '<tr id="record_'.$rec->id.'">';
                foreach ( $columns as $column_name => $column_display_name ) {

                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    //edit link
                    $editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->id;

                    //Display the cell
                    switch ( $column_name ) {
                        //case "id":  echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';   break;
                        case "name": echo '<td '.$attributes.'>'.stripslashes($rec->name).'</td>'; break;
                        case "group": echo '<td '.$attributes.'>'.stripslashes($rec->group).'</td>'; break;
                        case "description": echo '<td '.$attributes.'>'.$rec->description.'</td>'; break;
                        case "setting": echo '<td '.$attributes.'>'.$rec->setting.'</td>'; break;
                    }
                }

                //Close the line
                echo'</tr>';
            }
        }
    }
}