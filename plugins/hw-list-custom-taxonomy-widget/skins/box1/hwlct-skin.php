<?php
/**
Plugin Name: box style 1
*/
//rememer that you should use different name to enqueue file correctly
$theme['styles'][]='tpl-box-style1.css';
$theme['scripts'][] = '';

echo $before_widget;
echo '<div id="hwlct-widget-'.$tax.'-container" class="list-custom-taxonomy-widget">';
if ( $title ) echo $before_title . $title . $after_title;

if($dropdown){
    echo '<form action="'. get_bloginfo('url'). '" method="get">';
    wp_dropdown_categories($args);
    echo '<input type="submit" value="go &raquo;" /></form>';
}
else{
    echo '<div id="lct-widget-'.$tax.'" class="hwlct-container hwlct-box1">';
    //wp_list_categories($args);
    HW_POST::reset_item_counter();
    $data = get_categories($args);
    foreach($data as $term){
        $classes= array('item-box');
        if($awc_enable_grid_posts && isset($awc_grid_posts_cols) && class_exists('HW_POST')) {
            $classes = HW_POST::get_item_class($awc_grid_posts_cols,$classes);
        }
        ?>
        <div <?php HW_POST::item_class($classes)?> id="<?php echo $term->term_id?>">
            <div class="sub-category-item">
                <div class="picture">
                    <a href="<?php echo get_term_link($term);?>" title="<?php echo $term->name; ?>">
                        <?php
                        $image = get_field('image',$term);
                        if(!$image) $image = HW_SKIN::current()->get_skin_url('images/placeholder.png');
                        ?>

                        <img src="<?php echo $image?>" width="150px"/>
                    </a>
                    <h2 class="title">
                        <a href="<?php echo get_term_link($term);?>" title="<?php echo $term->name; ?>">
                            <?php echo $term->name; ?>
                        </a>
                    </h2>
                </div>
                <a href="<?php echo esc_url( get_edit_term_link( $term, $term->taxonomy) ); ?>">Edit</a>
            </div>
        </div>

<?php
    }
    echo '</div>';
}
echo '</div><div class="clearfix"></div>';
echo $after_widget;
?>