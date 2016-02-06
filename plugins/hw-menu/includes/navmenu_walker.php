<?php
/**
 * Class HW_Nav_Menu_Walker
 */
class HW_Nav_Menu_Walker extends Walker_Nav_Menu {
    private $counter=0;

    /**
     * store menu items
     * @var array
     */
    var $menu_items = array();

    function __construct(){
        $this->menu_items = array();
    }

    /**
     * @param $item
     * @return bool
     */
    private  function item_has_sub($item) {
        //get menu id
        if(is_object($item)) $menu_id = $item->ID;
        else $menu_id =$item;

        return isset($this->menu_items[$menu_id]) && $this->menu_items[$menu_id]['has_sub'];
    }

    /**
     * valid classes attribute value
     * @param array|string $classes
     * @return string
     */
    private function valid_classes_attr($classes) {
        HW_HOANGWEB::load_class('HW_Validation');
        return HW_Validation::valid_classes_attr($classes);
    }
    /**
     * parse li, copy from system navmenu by wordpress at /wp-includes/nav-menu-template.php (since wordpress v)
     * @param string $output
     * @param object $item
     * @param int $depth
     * @param array $args
     * @param int $id
     */
    protected function _start_el(&$output, $item, $depth = 0, $args = Array(), $id = 0){
        $args = (object) $args;     //cast to object
        //valid
        if(!isset($args->before)) $args->before = '';
        if(!isset($args->submenu_before)) $args->submenu_before = '';

        if(!isset($args->after)) $args->after = '';
        if(!isset($args->submenu_after)) $args->submenu_after = '';

        if(!isset($args->link_before)) $args->link_before = '';
        if(!isset($args->submenu_link_before)) $args->submenu_link_before = '';

        if(!isset($args->link_after)) $args->link_after = '';
        if(!isset($args->submenu_link_after)) $args->submenu_link_after = '';
        //whether current menu item has sub menu
        $item_has_sub = $this->item_has_sub($item);

        //menu item classes
        $classes = (empty( $item->classes ) ? array() : (array) $item->classes);

        if(//$this->counter &&
            !empty($args->ex_separator) && isset($args->show_items_separator))
        {
            $output .= $args->ex_separator;
        }
        $data['args'] = clone $args;
        $data['indent'] = $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
        $data['item_has_sub'] = $item_has_sub;

        //menu item classes
        $classes[] = 'nav'.(++ $this->counter);
        $classes[] = 'menu-item-' . $item->ID;
        if($item_has_sub && !empty($args->menu_item_class_has_submenu )) {  //menu item class has submenu
            $classes[] = $args->menu_item_class_has_submenu;
        }
        if($depth >0 && !empty($args->menu_item_class_submenu) ) {  //menu item class in submenu
            $classes[] = $args->menu_item_class_submenu;
        }

        $data['classes']= $class_names = join( ' ', apply_filters( 'nav_menu_css_class', $this->valid_classes_attr(array_filter( $classes )), $item, $args, $depth ) );
        //$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
        $data['id'] = ($id = $id ? ' id="' . esc_attr( $id ) . '"' : '');
        //custom fields
        $custom_item = get_post_custom($item->ID);
        $data['field-01'] = isset($custom_item['menu-item-field-01'][0])? $custom_item['menu-item-field-01'][0] : '';
        $data['field-02'] = isset($custom_item['menu-item-field-02'][0])? $custom_item['menu-item-field-02'][0] : '';

        /**
         * menu item image
         */
        if(isset($args->show_icon) && $args->show_icon) {
            $image_url = get_post_meta( $item->ID, 'menu-item-hw_icon', true );
            if($image_url) $image_img = '<img src="'.$image_url.'" class="hw-menu-item-icon"/>';
            else $image_img = '';

        }
        #_print(get_post_custom($item->ID));
        /**
         * menu font icon
         */
        #$fonticon_meta = get_post_meta($item->ID, 'menu-icons', true);  //Menu_Icons::get_meta( $item->ID );
        $fonticon_meta = Menu_Icons::get_meta( $item->ID );
        $data['fonticon_setting'] = $fonticon_meta; //font icon setting

        $font_icon = HW_Menu_Icons::render_icon($fonticon_meta);

        $data['image_url'] = isset($image_url)? $image_url : '';
        $data['image_img'] = isset($image_img)? $image_img : '';
        $data['font_icon'] = isset($font_icon)? $font_icon : '';

        /**
         * render nav item
         */
        //clear submenu before/after for non-ancestor item
        if(!$item_has_sub ) {
            $data['args']->submenu_before = '';
            $data['args']->submenu_after = '';
            $data['args']->submenu_link_before = '';
            $data['args']->submenu_link_after = '';
        }

        //anchor attributes
        $atts = array();
        $atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
        $atts['target'] = ! empty( $item->target )     ? $item->target     : '';
        $atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
        $atts['href']   = ! empty( $item->url )        ? $item->url        : '';
        $atts['class'] = array();

        //anchor classes
        if($item_has_sub && !empty($args->anchor_class_has_submenu)) {  //anchor class has submenu
            $atts['class'][] = $args->anchor_class_has_submenu;
        }
        if($depth >0 && !empty($args->anchor_class_submenu)) {  //anchor class in submenu
            $atts['class'][] = $args->anchor_class_submenu;
        }

        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth ,$this);
        //validation
        $atts['class']= $this->valid_classes_attr(array_filter($atts['class']));
        $atts['class'] = join(' ', $atts['class']);

        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }
        //addition attribute build for item link
        if($depth ==0 && !empty($args->anchor_attrs)) $attributes .= " {$args->anchor_attrs}";
        elseif($depth!=0 && !empty($args->anchor_attrs_submenu)) $attributes .= " {$args->anchor_attrs_submenu}";   //attributes item link for submenu
        if($item_has_sub
            && !empty($args->anchor_attrs_has_submenu)) {
            $attributes .= " {$args->anchor_attrs_has_submenu}";
        }

        $data['attributes'] = ($attributes);
        $data['title'] = apply_filters( 'the_title', $item->title, $item->ID );

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        //This filter is documented in wp-includes/post-template.php
        $item_output .= $args->link_before . $data['title'] . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        if(isset($args->twig) && HW_NAVMENU::twig_asset_exists('start_el.twig', $args->twig)) {
            $tpl = $args->twig->loadTemplate('start_el.twig');
            $output .= $tpl->render($data);
        }
        else {
            $classes = $class_names? 'class="'.$class_names.'"' : "";   //class attr
            $output .= $indent . '<li' . $id . $classes .'>';
            $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, (array)$args );
        }
    }

    /**
     * @param string $output
     * @param object $item
     * @param int $depth
     * @param array $args
     * @param int $id
     */
    public function start_el( &$output, $item, $depth = 0, $args = Array(), $id = 0 ) {
        $this->_start_el($output, $item, $depth, (object)$args, $id);

    }

    /**
     * @param string $output
     * @param object $item
     * @param int $depth
     * @param array $args
     */
    public function end_el( &$output, $item, $depth = 0, $args = array() ) {
        if(isset($args->twig) && HW_NAVMENU::twig_asset_exists('end_el.twig', $args->twig)) {
            $tpl = $args->twig->loadTemplate('end_el.twig');
            $output .= $tpl->render(array());
        }
        else $output .= "</li>\n";
        //parent::end_el($output, $item, $depth, $args);
    }

    /**
     * parse ul
     * @param $output
     * @param int $depth
     * @param array $args
     */
    protected  function _start_lvl(&$output, $depth=0, $args=array()){
        $data['args'] = $args;
        $data['indent'] = ( $depth ) ? str_repeat( "t", $depth ) : '';
        //sub-menu class
        if(!empty($args->submenu_container_class)) $data['class'] = $args->submenu_container_class;
        else $data['class'] = 'sub-menu';

        if(isset($args->twig) && HW_NAVMENU::twig_asset_exists('start_lvl.twig', $args->twig)) {
            $tpl = $args->twig->loadTemplate('start_lvl.twig');
            $output .= $tpl->render($data);
        }
        else{
            //extract($data);
            $output .= $data['indent']."\n<ul class='{$data['class']}'>\n";
        }

    }

    /**
     * for children elements
     * @param string $output
     * @param int $depth
     * @param array $args
     */
    public function start_lvl(&$output, $depth=0, $args=array()) {
        $this->_start_lvl($output, $depth, $args);
        //parent::start_lvl($output, $depth, $args);   #don;t need
    }

    /**
     * close ul tag for children elements
     * @param string $output
     * @param int $depth
     * @param array $args
     */
    public function end_lvl(&$output, $depth=0, $args=array()) {
        $indent = ( $depth ) ? str_repeat( "t", $depth ) : '';

        if(isset($args->twig) && HW_NAVMENU::twig_asset_exists('end_lvl.twig', $args->twig)) {
            $tpl = $args->twig->loadTemplate('end_lvl.twig');
            $output .= $tpl->render(array(
                'indent' => $indent,
                'args' => $args
            ));
        }
        else $output .= $indent."</ul>\n";

        #parent::end_lvl($output, $depth, $args);       #don't use
    }

    /**
     * Only follow down one branch
     * @param $element
     * @param $children_elements
     * @param $max_depth
     * @param int $depth
     * @param $args
     * @param $output
     */
    public function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
        //add current menu item
        $this->menu_items[$element->ID] = array('has_sub' => false, 'title' => $element->title);
        $current = &$this->menu_items[$element->ID];

        // Check if element as a 'current element' class
        $current_element_markers = array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor' );
        $current_class = array_intersect( $current_element_markers, (array)$element->classes );

        // If element has a 'current' class, it is an ancestor of the current element
        $ancestor_of_current = !empty($current_class);
        $current['has_sub'] = $ancestor_of_current;

        // If this is a top-level link and not the current, or ancestor of the current menu item - stop here.
        /*if ( 0 == $depth && !$ancestor_of_current)
            return;*/

        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }

}

/**
 * for testing
 * Class HW_Nav_Menu_Walker1
 */
class HW_Nav_Menu_Walker1 extends Walker_Nav_Menu {
    public function __construct(){

    }
    /**
     * Starts the list before the elements are added.
     *
     * @see Walker::start_lvl()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     */
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";
    }

    /**
     * Ends the list of after the elements are added.
     *
     * @see Walker::end_lvl()
     *
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array  $args   An array of arguments. @see wp_nav_menu()
     */
    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
}