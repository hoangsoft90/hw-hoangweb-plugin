<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 06/12/2015
 * Time: 22:38
 */
/**
 * Fetch an instance of a WP_List_Table class.
 *
 * @access private
 * @since 3.1.0
 *
 * @param string $class The type of the list table, which is the class name.
 * @param array $args Optional. Arguments to pass to the class. Accepts 'screen'.
 * @return object|bool Object on success, false if the class does not exist.
 */
function hw_get_list_table( $class, $args = array() ) {
    $core_classes = array(
        'HW_Module_Install_List_Table' => 'module-install',

    );

    if ( isset( $core_classes[ $class ] ) ) {
        foreach ( (array) $core_classes[ $class ] as $required )
            require_once( HW_HOANGWEB_UTILITIES . '/admin/class-hw-' . $required . '-list-table.php' );

        if ( isset( $args['screen'] ) )
            $args['screen'] = convert_to_screen( $args['screen'] );
        elseif ( isset( $GLOBALS['hook_suffix'] ) )
            $args['screen'] = get_current_screen();
        else
            $args['screen'] = null;

        return new $class( $args );
    }

    return false;
}