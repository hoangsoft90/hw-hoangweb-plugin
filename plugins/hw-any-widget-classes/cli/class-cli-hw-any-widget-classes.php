<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 02/11/2015
 * Time: 10:58
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_CLI_HW_Any_widget_classes
 */
class HW_CLI_HW_Any_widget_classes extends HW_CLI_Command {
    /**
     * register sidebar
     * @param $args
     * @param $assoc_args
     */
    public function add_sidebar($args, $assoc_args ) {
        $params = $this->get_cmd_args();#var_dump($params);
        foreach($params as $param) {
            //$name = $this->get_cmd_arg($assoc_args, 'name');
            //$id = $this->get_cmd_arg($assoc_args, 'id', $name);
            if(!empty($param['name'])) $name = $param['name'];
            if(!empty($param['id'])) $id = $param['id'];
            else $id = $name;
            //valid sidebar id
            $id = preg_replace('#[\s]+#', '-', $id);

            if(!$name) $name = $id;

            $desc = isset($param['description'])? $param['description'] : '';    //$this->get_cmd_arg($assoc_args, 'description');
            $before_widget = isset($param['before_widget'])? $param['before_widget'] :'';//$this->get_cmd_arg($assoc_args, 'before_widget');
            $before_title = isset($param['before_title'])? $param['before_title'] :'';//$this->get_cmd_arg($assoc_args, 'before_title');
            $after_title = isset($param['after_title'])? $param['after_title'] :'';//$this->get_cmd_arg($assoc_args, 'after_title');
            $after_widget = isset($param['after_widget'])? $param['after_widget'] :'';    //$this->get_cmd_arg($assoc_args, 'after_widget');
            HW_HOANGWEB::load_class('HW_String');

            $sidebar = array(
                'id' => $id,
                'name' => $name,
                'description' => $desc,
                'before_widget' => $before_widget,
                'before_title' => $before_title,
                'after_title' => $after_title,
                'after_widget' => $after_widget
            );
            if(!empty($sidebar['id'])) {
                hwawc_register_sidebar($sidebar);
                WP_CLI::success( sprintf(' register sidebar `%s` successful.', $name) );
            }
        }
    }

    /**
     * remove sidebar
     * @param $args
     * @param $assoc_args
     */
    public function del_sidebar($args, $assoc_args ) {
        $id = $this->get_cmd_arg($assoc_args, 'id');
        if($id) hwawc_unregister_sidebar($id);

        WP_CLI::success( sprintf(' delete sidebar `%s` successful.', $id) );
    }

    /**
     * apply skin for sidebar
     * @param $args
     * @param $assoc_args
     */
    public function apply_sidebar_skin($args, $assoc_args ) {
        $config=$this->get_config();
        $skins = $config->get_config_data('skins_data');
        //sidebar
        $sidebar = $this->get_cmd_arg($assoc_args, 'sidebar');
        $options = $this->get_cmd_data('options');#var_dump($options);
        $this->do_import();

        WP_CLI::success( sprintf(' set skin for sidebar `%s` successful.', $sidebar) );
    }

    /**
     * list all sidebar widgets
     * @param $args
     * @param $assoc_args
     */
    public function stats($args, $assoc_args ) {
        $sidebars_widgets = get_option('sidebars_widgets');
        print_r($sidebars_widgets);
        //WP_CLI::success('');
    }

    /**
     * @param $args
     * @param $assoc_args
     */
    public function reset_settings($args, $assoc_args ) {
        delete_transient('hw_dynamic_sidebar');
        delete_transient('hw_dynamic_sidebars_settings');
        WP_CLI::success('reset AWC module settings successful.');
    }
}