<?php
/**
 * Class HW_ACF_API
 */
class HW_ACF_API
{
    /**
     * return acf location base type & value
     * @param $type
     * @param $value
     */
    public static function get_target_location($type, $value){
        if($type == 'taxonomy') {
            return array (
                array (
                    'param' => 'ef_taxonomy',
                    'operator' => '==',
                    'value' => $value,
                    'order_no' => 0,
                    'group_no' => 0,
                ),
            );
        }
    }
    /**
     * register field group for taxonomy with image field
     * @param $taxonomies
     */
    public static function hw_acf_register_field_group_taxonomy_image($taxonomies){
        $fields = array(
            array (
                'key' => 'field_556e6ba7e9da3',
                'label' => 'Image',
                'name' => 'hw_image',
                'type' => 'image',
                'save_format' => 'url',
                'preview_size' => 'thumbnail',
                'library' => 'all',
            ),
        );
        $taxonomies_location = array();
        foreach((array)$taxonomies as $tax) {
            $taxonomies_location[] = self::get_target_location('taxonomy',$tax);
        }

        self::hw_acf_register_field_group('category', $fields, $taxonomies_location);
    }

    /**
     * ACF register field group
     * @param $title
     * @param array $fields
     * @param array $location
     * @param array $options
     */
    private static function hw_acf_register_field_group($title, $fields = array(), $location = array(), $options = array()){
        //define( 'ACF_LITE', true );
        if(function_exists("register_field_group"))
        {
            //parse id
            $id = preg_replace('|[\s,~\!@#\$%\^&\*\(\)\-\+\/]+|', '_',$title);
            //valid options
            if(empty($options)) {
                $options = array (
                    'position' => 'normal',
                    'layout' => 'no_box',
                    'hide_on_screen' => array (
                    ),
                );
            }
            //note, this create field group hidden not show in acf manager
            register_field_group(array (
                'id' => 'acf_'.$id,
                'title' => $title,
                'fields' => $fields,
                'location' => $location,
                'options' => $options,
                'menu_order' => 0,
            ));
        }
    }
}

/**
 * Class HW_ACF_Taxonomy
 */
class HW_ACF_Taxonomy extends HW_ACF_API{
    /**
     * get field term taxonomy
     * @param $name
     * @param $term term id or term object
     * @param $tax
     */
    public static function get_field($name , $term, $tax = 'category') {
        if(is_object($term)) {
            return get_field($name, $term);
        }
        return get_field($name,"{$tax}_{$term}");
    }

    /**
     * get field image from tax term
     * @param $term
     * @param string $tax
     */
    public static function get_field_image($term, $tax = 'category') {
        self::get_field('hw_image', $term, $tax);
    }
}

