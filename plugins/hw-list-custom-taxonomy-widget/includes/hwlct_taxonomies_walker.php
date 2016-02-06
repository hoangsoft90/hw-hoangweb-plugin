<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 24/06/2015
 * Time: 08:40
 */
/**
 * Custom version of Walker_CategoryDropdown
 */
class hwlctwidget_Taxonomy_Walker extends Walker {
    /**
     * get current post terms ids
     * @var array|null
     */
    private $post_term_ids = null;
    /**
     * store wp_list_categories argument
     * @var array
     */
    private $args = array();
    /**
     * @var string
     */
    var $tree_type = 'category';
    /**
     * @var array
     */
    var $db_fields = array ( 'id' => 'term_id', 'parent' => 'parent' );

    /**
     * store menu items
     * @var array
     */
    var $menu_items = array();
    /**
     * current category
     * @var object
     */
    private $current_cat=null;

    /**
     * constructor
     * @param $args
     * @param null $post_id
     */
    public function __construct($args, $post_id = null)  {
        if(!empty($args)) $this->args = $args;
        $this->menu_items = array();

        //valid
        if(!isset($this->args['mydata']) ) $this->args['mydata'] = array();

        $taxonomy = $args['taxonomy'];  //get taxonomy name
        if(!$post_id && is_single()) {
            global $post;   //get current post
            $post_id = $post->ID;
            $this->args['mydata']['enable_categories_by_current_post'] = true;
        }

        // fetch the list of term ids for the given post
        if(is_numeric($post_id) && $post_id) $this->post_term_ids = wp_get_post_terms( $post_id, $taxonomy, 'fields=ids' );
    }
    /**
     * check item has sub menu
     * @param $item
     * @return bool
     */
    private  function item_has_sub($item) {
        //get menu id
        if(is_object($item)) $menu_id = $item->term_id;
        else $menu_id =$item;

        return isset($this->menu_items[$menu_id]) && $this->menu_items[$menu_id]['has_sub'];
    }
    /**
     * render item
     * @param object $element
     * @param array $children_elements
     * @param int $max_depth
     * @param int $depth
     * @param array $args
     * @param string $output
     * @return null|void
     */
    public function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
        $display = true;    //default
        //add current menu item
        $this->menu_items[$element->term_id] = array('has_sub' => false, 'title' => $element->name);
        $current = &$this->menu_items[$element->term_id];

        /*
        // Check if element as a 'current element' class
        $current_element_markers = array( 'current-cat', 'current-item', 'current-cat-ancestor','current-cat-parent' );
        $current_class = array_intersect( $current_element_markers, (array)$element->classes );

        // If element has a 'current' class, it is an ancestor of the current element
        $ancestor_of_current = !empty($current_class);*/
        $children = get_term_children($element->term_id, $element->taxonomy);
        $current['has_sub'] = !empty($children);

        //display terms belong to current post
        if(!empty($this->args['mydata']['categories_by_current_post'])
            && isset($this->args['mydata']['enable_categories_by_current_post']))
        {
            $display = false;

            $id = $element->term_id;

            if ( in_array( $id, $this->post_term_ids ) ) {
                // the current term is in the list
                $display = true;
            }
            elseif ( isset( $children_elements[ $id ] ) ) {
                // the current term has children
                foreach ( $children_elements[ $id ] as $child ) {
                    if ( in_array( $child->term_id, $this->post_term_ids ) ) {
                        // one of the term's children is in the list
                        $display = true;
                        // can stop searching now
                        break;
                    }
                }
            }
        }

        if ( $display )
            parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }

    /**
     * extend from wordpress core
     * @param string $output
     * @param object $category
     * @param int $depth
     * @param array $args
     * @param int $id
     */
    public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        if(!empty($args['mydata']['skin_setting'])) $skin_setting = $args['mydata']['skin_setting'];
        $this->current_cat = new HW_TimberTerm($category);
        /** This filter is documented in wp-includes/category-template.php */
        $cat_name = apply_filters(
            'list_cats',
            esc_attr( $category->name ),
            $category
        );

        //valid
        $options = (object)$args['options'];
        if(!isset($options->before)) $options->before = '';
        if(!isset($options->submenu_before)) $options->submenu_before = '';

        if(!isset($options->after)) $options->after = '';
        if(!isset($options->submenu_after)) $options->submenu_after = '';

        if(!isset($options->link_before)) $options->link_before = '';
        if(!isset($options->submenu_link_before)) $options->submenu_link_before = '';

        if(!isset($options->link_after)) $options->link_after = '';
        if(!isset($options->submenu_link_after)) $options->submenu_link_after = '';

        //whether current menu item has sub menu
        $item_has_sub = $this->item_has_sub($category);
        //separator
        if(//$this->counter &&
            !empty($options->ex_separator) && isset($options->show_items_separator))
        {
            $output .= $options->ex_separator;
        }
        $data['args'] = hwArray::cloneArray($options);
        $data['indent'] = $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
        $data['item_has_sub'] = $item_has_sub;

        $id = apply_filters( 'hw_cat_item_id', 'cat-item-'. $category->term_id, $category, $args, $depth );
        $data['id'] = (' id="cat-' . esc_attr( $category->term_id ) . '"' );

        if($item_has_sub && !empty($options->remove_link_parent)) {
            $data['remove_link_parent'] = true;
        }
        else $data['remove_link_parent'] = false ;
        //item class
        //category item class
        $classes[] = 'cat-item cat-item-' . $category->term_id;
        if ( ! empty( $args['current_category'] ) ) {
            $_current_category = get_term( $args['current_category'], $category->taxonomy );
            if ( $category->term_id == $args['current_category'] ) {
                $classes[] = 'current-cat';
                if(isset($skin_setting['current_item_class'])) {
                    $classes[] = ' ' . $skin_setting['current_item_class'];
                }

            } elseif ( $category->term_id == $_current_category->parent ) {
                $classes[] =  'current-cat-parent';
            }
        }
        if($item_has_sub && !empty($options->menu_item_class_has_submenu )) {  //cat item class has submenu
            $classes[] = $options->menu_item_class_has_submenu;
        }
        if($depth >0 && !empty($options->menu_item_class_submenu) ) {  //cat item class in submenu
            $classes[] = $options->menu_item_class_submenu;
        }
        $data['classes']= $class_names = join( ' ', apply_filters( 'cat_item_css_class', HW_Validation::valid_classes_attr(array_filter( $classes )), $category, $args, $depth ) );

        //custom fields
        $custom_item = get_post_custom($category->term_id);
        $data['field-01'] = isset($custom_item['menu-item-field-01'][0])? $custom_item['menu-item-field-01'][0] : '';
        $data['field-02'] = isset($custom_item['menu-item-field-02'][0])? $custom_item['menu-item-field-02'][0] : '';
        //cat item image
        if(isset($options->show_icon) && $options->show_icon) {
            $image_url = HW_ACF_Taxonomy::get_field_image($category);
            if($image_url) $image_img = '<img src="'.$image_url.'" class="hw-cat-item-icon"/>';
            else $image_img = '';

        }
        $data['image_url'] = isset($image_url)? $image_url : '';
        $data['image_img'] = isset($image_img)? $image_img : '';
        /**
         * anchor attributes
         */
        $atts = array();
        $atts['href'] = esc_url( get_term_link( $category ) );
        $atts['class'] = array();
        //anchor classes
        if($item_has_sub && isset($options->anchor_class_has_submenu)) {  //anchor class has submenu
            $atts['class'][] = $options->anchor_class_has_submenu;
        }
        if($depth >0 && !empty($options->anchor_class_submenu)) {  //anchor class in submenu
            $atts['class'][] = $options->anchor_class_submenu;
        }
        $atts = apply_filters( 'hw_cat_link_attributes', $atts, $category, $args, $depth ,$this);
        //validation
        $atts['class']= HW_Validation::valid_classes_attr(array_filter($atts['class']));
        $atts['class'] = join(' ', $atts['class']);

        if ( $args['use_desc_for_title'] && ! empty( $category->description ) ) {
            $atts['title'] = esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) );
        }

        $attributes = HW_UI_Component::generateAttributes($atts);
        //addition attribute build for item link
        if($depth ==0 && !empty($options->anchor_attrs)) $attributes .= " {$options->anchor_attrs}";
        elseif($depth!=0 && !empty($options->anchor_attrs_submenu)) $attributes .= " {$options->anchor_attrs_submenu}";   //attributes item link for submenu
        if($item_has_sub
            && !empty($options->anchor_attrs_has_submenu)) {
            $attributes .= " {$options->anchor_attrs_has_submenu}";
        }
        $data['attributes'] = ($attributes);
        $data['title'] = apply_filters( 'the_title', $category->name, $category->term_id );

        $link = $options->before;
        $link .= '<a '.$attributes.' >';
        $link .= $options->link_before. $cat_name . $options->link_after;
        $link .= '</a>';
        $link .= $options->after;

        //feed image
        if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
            $link .= ' ';

            if ( empty( $args['feed_image'] ) ) {
                $link .= '(';
            }

            $link .= '<a href="' . esc_url( get_term_feed_link( $category->term_id, $category->taxonomy, $args['feed_type'] ) ) . '"';

            if ( empty( $args['feed'] ) ) {
                $alt = ' alt="' . sprintf(__( 'Feed for all posts filed under %s' ), $cat_name ) . '"';
            } else {
                $alt = ' alt="' . $args['feed'] . '"';
                $name = $args['feed'];
                $link .= empty( $args['title'] ) ? '' : $args['title'];
            }

            $link .= '>';

            if ( empty( $args['feed_image'] ) ) {
                $link .= $name;
            } else {
                $link .= "<img src='" . $args['feed_image'] . "'$alt" . ' />';
            }
            $link .= '</a>';

            if ( empty( $args['feed_image'] ) ) {
                $link .= ')';
            }
        }

        if ( ! empty( $args['show_count'] ) ) {
            $link .= ' (' . number_format_i18n( $category->count ) . ')';
        }
        //render cat item
        if(isset($args['twig']) && HW_NAVMENU::twig_asset_exists('start_el.twig', $args['twig'])) {
            $tpl = $args['twig']->loadTemplate('start_el.twig');
            $data['term'] = $this->current_cat;
            $output .= $tpl->render($data);
        }
        else {
        if ( 'list' == $args['style'] ) {
            $output .= $indent. "\t<li";
            //class attr
            $output .=  ' class="' . $class_names . '"';
            $output .= ">$link\n";
        } else {
            $output .= "\t$link<br />\n";
        }
        }
    }

    /**
     * @param string $output
     * @param object $page
     * @param int $depth
     * @param array $args
     */
    public function end_el( &$output, $page, $depth = 0, $args = array() ) {
        if ( 'list' != $args['style'] )
            return;

        if(isset($args['twig']) && HW_NAVMENU::twig_asset_exists('end_el.twig', $args['twig'])) {
            $tpl = $args['twig']->loadTemplate('end_el.twig');
            $output .= $tpl->render(array('term'=> $this->current_cat));
        }
        else $output .= "</li>\n";
    }

    /**
     * ul tag for child items
     * @param string $output
     * @param int $depth
     * @param array $args
     */
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        if ( 'list' != $args['style'] )
            return;

        $indent = str_repeat("\t", $depth);
        $data['args'] = $args;
        $data['indent'] = ( $depth ) ? $indent : '';
        $options = (object) $args['options'];
        //sub-menu class
        if(!empty($options->submenu_container_class)) $data['class'] = $options->submenu_container_class;
        else $data['class'] = 'hw-item-children';

        if(isset($args['twig']) && HW_NAVMENU::twig_asset_exists('start_lvl.twig', $args['twig'])) {
            $tpl = $args['twig']->loadTemplate('start_lvl.twig');
            $output .= $tpl->render($data);
        }

        else $output .= "{$indent}<ul class='{$data['class']}'>\n";
    }

    /**
     * @param string $output
     * @param int $depth
     * @param array $args
     */
    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        if ( 'list' != $args['style'] )
            return;

        $indent = str_repeat("\t", $depth);
        if(isset($args['twig']) && HW_NAVMENU::twig_asset_exists('end_lvl.twig', $args['twig'])) {
            $tpl = $args['twig']->loadTemplate('end_lvl.twig');
            $output .= $tpl->render(array(
                'indent' => $indent,
                'args' => $args
            ));
        }
        else $output .= "{$indent}</ul>\n";
    }
}

/**
 * dropdown categories select tag
 * Class hwlctwidget_Taxonomy_Dropdown_Walker
 */
class hwlctwidget_Taxonomy_Dropdown_Walker extends Walker {
    /**
     * generate li tag
     * @param string $output
     * @param object $term
     * @param int $depth
     * @param array $args
     * @param int $current_object_id
     */
    function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $url = get_term_link( $term, $term->taxonomy );

        $text = str_repeat( '&nbsp;', $depth * 3 ) . $term->name;
        if ( $args['show_count'] ) {
            $text .= '&nbsp;('. $term->count .')';
        }

        $class_name = 'level-' . $depth;

        $output.= "\t" . '<option' . ' class="' . esc_attr( $class_name ) . '" value="' . esc_url( $url ) . '">' . esc_html( $text ) . '</option>' . "\n";
    }
}