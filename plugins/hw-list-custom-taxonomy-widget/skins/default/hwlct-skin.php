<?php
/**
Plugin Name: default
*/
//rememer that you should use different name to enqueue file correctly
include('theme-setting.php');

echo $before_widget;
echo '<div id="hwlct-widget-'.$tax.'-container" class="list-custom-taxonomy-widget">';
if ( $title ) echo $before_title . $title . $after_title;

if($dropdown){
    echo '<form action="'. get_bloginfo('url'). '" method="get">';
    wp_dropdown_categories($args);
    echo '<input type="submit" value="go &raquo;" />';
    echo '</form>';
}
else{
    echo '<div id="lct-widget-'.$tax.'" class="hwlct-container hwlct-travel-box">';
    //wp_list_categories($args);
    HW_POST::reset_item_counter();
    //$data = get_categories($args);
    foreach($terms_data as $i =>$term){
        $classes= array('item-box');
        if($awc_enable_grid_posts && isset($awc_grid_posts_cols) && class_exists('HW_POST')) {
            $classes = HW_POST::get_item_class($awc_grid_posts_cols,$classes);
        }
        $classes_attribute = HW_POST::item_class($classes);
        $term_link = get_term_link($term);
        //term image custom field
        if(function_exists('get_field')) $image = get_field('image',$term);
        else $image = '';
        if(!$image) $image = HW_SKIN::current()->get_skin_url('images/placeholder.png');

        $quick_edit_link = esc_url( get_edit_term_link( $term, $term->taxonomy) );

        $skin->render_skin_template( array(
            'classes_attribute' => $classes_attribute,
            'term_link' => $term_link,
            'image' => $image,
            'quick_edit_link' => $quick_edit_link,
            'term' => $term
        ), true, false,'loop.tpl');
        /*
        ?>
        <div  <?php echo $classes_attribute?> id="<?php echo $term->term_id?>">
            <div class="category-item">
                <div class="picture">
                    <a href="<?php echo $term_link;?>" title="<?php echo $term->name; ?>">
                        <img src="<?php echo $image?>" width="150px"/>

                    </a>
                </div>
                <div class="picture-shadow"></div>
                <h2 class="title">
                    <a href="<?php echo $term_link;?>" title="<?php echo $term->name; ?>">
                        <?php echo $term->name?>
                    </a>
                </h2>
                <div class="category-description">
                    <?php ECHO $term->description?>
                </div>
                <a href="<?php echo  $quick_edit_link?>">Edit</a>
            </div>
        </div>
<?php
        */
    }
    echo '</div>';
}
echo '</div><div class="clearfix"></div>';
echo $after_widget;
?>