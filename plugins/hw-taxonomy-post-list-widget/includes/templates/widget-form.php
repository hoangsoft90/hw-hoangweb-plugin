<?php
global $wp_post_types;
//valid
if(! isset($instance['query_data'])) $instance['query_data'] = '';

/**
 * skin options form
 */
$skin_setting = isset($instance['skin_setting'])? $instance['skin_setting'] : null;   //skin options
$skin = isset($instance['skin'])? $instance['skin'] : '';     //widget skin
$skin_options = $this->skin->prepare_skin_options_fields('skin_setting', $skin_setting, $skin);

/**
 * scrollbar
 */
$scrollbar_skin_setting = isset($instance['scrollbar_skin_setting'])? $instance['scrollbar_skin_setting'] : null;   //scrollbar skin options
$scrollbar_skin = isset($instance['scrollbar_skin'])? $instance['scrollbar_skin'] : '';     //scrollbar skin

//scrollbar skin options output, this should call before display select tag for scrollbar selector in order to make skin change event work
$scrollbar_skin_options = $this->skin->get_skin_instance('scrollbar')->prepare_skin_options_fields('scrollbar_skin_setting', $scrollbar_skin_setting, $scrollbar_skin);

//get taxonomies by specific post types
if(isset($instance['posttype'])) {
    HW_HOANGWEB::load_class('HW_POST');
    $pts_taxonomies = HW_POST::get_posttypes_taxonomies($instance['posttype'], true);   //get all taxonomies from post types

    //get all posts by choose post types
    $posts_data = HW_POST::get_all_posts_by_posttypes($instance['posttype']);
}
else {
    $taxonomies = get_taxonomies( '','objects' );
    $pts_taxonomies = array();
    foreach($taxonomies as $name => $tax) {
        $pts_taxonomies[$name] = $tax->labels->name ." ($name)";
    }
    //posts empty data
    $posts_data = array();
}
//list all users of current blog
$blogusers = get_users('orderby=nicename');
?>
<p>
			<label for="<?php echo $this->get_field_id("widget_title"); ?>">
				<?php _e( 'Title' ); ?>:
</label>
<input class="widefat" id="<?php echo $this->get_field_id("widget_title"); ?>" name="<?php echo $this->get_field_name("widget_title"); ?>" type="text" value="<?php echo esc_attr($instance["widget_title"]); ?>" />
</p>
<div style="border:1px dotted #dadada;background:#F0ECEC;padding:5px;">
    <fieldset>
        <legend>Chọn dữ liệu</legend>
        <p class="hwtpl_query_types">
            <label><input type="radio" name="<?php echo $this->get_field_name('query_data')?>" id="<?php echo $this->get_field_id('query_data')?>" value="current_context" <?php checked(isset($instance['query_data']) && $instance['query_data'] == 'current_context')?> onclick="__hwcpl_object.change_wp_query_option(this)" data-id="<?php echo $this->get_field_id('current_context')?>"/> Dữ liệu hiện tại.</label>

            <label><input type="radio" name="<?php echo $this->get_field_name('query_data')?>" id="<?php echo $this->get_field_id('query_data')?>" value="filter_query" <?php checked($instance['query_data'] == 'filter_query')?> onclick="__hwcpl_object.change_wp_query_option(this)" data-id="<?php echo $this->get_field_id('filter_query')?>"/> Lọc dữ liệu mới.</label>

            <!-- <label><input type="radio" name="<?php echo $this->get_field_name('query_data')?>" id="<?php echo $this->get_field_id('query_data')?>" value="filter_query" <?php checked($instance['query_data'] == 'specific_post')?> onclick="__hwcpl_object.change_wp_query_option(this)" data-id="<?php echo $this->get_field_id('specific_post')?>"/> Chỉ định bài viết.</label> -->
        </p>

        <div id="<?php echo $this->get_field_id('current_context')?>" style="display:<?php echo ($instance['query_data'] == 'current_context')? 'block': 'none'?>">
            <p>Lấy dữ liệu trên trang hiện tại.</p>
        </div>
        <div id="<?php echo $this->get_field_id('filter_query')?>" style="display:<?php echo ($instance['query_data'] == 'filter_query')? 'block': 'none'?>">
        <p>
            <label>
                <?php _e( 'Post Type' ); ?>:
            </label>
            <?php
            $post_types = HW_POST::get_post_types();
            $nonce = wp_create_nonce("hw_change_posttype_taxonomies_nonce");
            $link = admin_url('admin-ajax.php?action=hw_change_posttype_taxonomies&nonce='.$nonce);
            ?>
            <select name="<?php echo $this->get_field_name('posttype'); ?>[]" id="<?php echo $this->get_field_id('posttype'); ?>" class="widefat extra-options-select" multiple="multiple" size="10" onchange="__hwcpl_object.hwtpl_change_taxonomies_posttype(this, '#holder-<?php echo $this->get_field_id('tax')?>', 'hwtpl_change_taxonomies_posttype_cbs','<?php //echo $link?>')" data-id="<?php echo $this->number?>">
                <!-- <option value="">---- Chose ----</option> -->
                <?php foreach($post_types as $pt => $pt_name){

                    ?>
                    <option <?php selected(is_array($current_pt) && in_array( $pt, $current_pt) )?> value="<?php echo esc_attr( $pt )?>" id="<?php echo esc_attr( $pt )?>"><?php echo $pt_name.' ('.$pt.')'?></option>
                <?php }?>
            </select>
            <span><em>Chọn một hay nhiều kiểu dữ liệu.</em></span>
        </p>
        <p>
            <label>
                <?php _e( 'Taxonomy' ); ?>:
            </label>
            <?php
            $nonce = wp_create_nonce("hw_change_terms_taxonomy_nonce");
            $link = admin_url('admin-ajax.php?action=hw_change_terms_taxonomy&id='.urlencode(hwtpl_mc_encrypt($this->get_field_id('cat_'),self::ENCRYPTION_KEY)).'&name='.urlencode(hwtpl_mc_encrypt($this->get_field_name('cat_'),self::ENCRYPTION_KEY)).'&nonce='.$nonce);
            ?>
            <div id="holder-<?php echo $this->get_field_id('tax')?>">
            <select name="<?php echo $this->get_field_name('tax'); ?>" id="<?php echo $this->get_field_id('tax'); ?>" class="widefat extra-options-select" nonce="<?php echo $nonce?>" onchange="__hwcpl_object.hwtpl_change_terms_taxonomy(this,'<?php echo $link?>','#holder-<?php echo $this->get_field_id("cat_")?>', 'hwtpl_change_terms_taxonomy_cbs')" data-id="<?php echo $this->number?>">
                <option value="">------Select------</option>
                <?php
                if(isset($pts_taxonomies))
                foreach ( $pts_taxonomies as $tax => $tax_name) {
                    echo '<option id="' . esc_attr( $tax ) . '" value="' . esc_attr( $tax ) . '"' . selected( $tax, $taxonomy, false ) . '>' . $tax_name . '</option>';
                }
                ?>
            </select>
                </div>

        </p>
        <p>
            <label>
                <?php _e( 'Term' );?>:
            </label>
        <div id="holder-<?php echo $this->get_field_id("cat_")?>">
            <?php
            if(isset($instance['tax']) && $instance['tax'] ){?>
                <?php
                echo hw_get_terms_list($instance['tax'],(isset($instance["cat_"]) && $instance["cat_"])? $instance['cat_']:'',
                    array(
                        'name' => $this->get_field_name('cat_'),
                        'id' => $this->get_field_id('cat_')
                    )
                ) ?>
            <?php }else echo __('Chọn Taxonomy.');?>
            <?php //wp_dropdown_categories( array( 'name' => $this->get_field_name("cat_"),'show_option_all' => 'All','hide_empty' => 0, 'selected' => $instance["cat"] ) ); ?>
        </div>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('author')?>"><?php _e("User")?></label><br/>
            <select name="<?php echo $this->get_field_name('author') ?>" id="<?php echo $this->get_field_id('author') ?>">
                <option value="-1">--- Tất cả ---</option>
                <option value="logined_user" <?php selected(isset($instance['author'])? $instance['author'] : '', 'logined_user')?>>User đăng nghập</option>
                <?php foreach ( $blogusers as $user ) {
                    if(get_current_user_id() == $user->ID) $class = "class='hwtpl-current-user-select'";
                    else $class = "";
                    $selected = selected(isset($instance['author'])? $instance['author'] : '', $user->ID, false);   //focus current saved item
                    printf('<option value="%s" %s %s>%s</option>', $user->ID, $selected,$class, $user->display_name);
                }
                ?>
            </select><br/>
            <em>Chú ý: Nếu hoạt động trên website thì "User đăng nhập" sẽ lấy user hiện tại đã logined vào website.</em>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('specific_post')?>"><?php _e('Lấy một bài viết')?></label>
            <div id="holder-<?php echo $this->get_field_id('specific_post')?>">
                <select id="<?php echo $this->get_field_id('specific_post')?>" name="<?php echo $this->get_field_name('specific_post')?>" class="widefat hw-select-tag">
                    <option value="">------Select------</option>
                    <?php if(count($posts_data)) foreach($posts_data as $id => $title) {?>
                        <?php
                        if(isset($instance['specific_post']) && $id == $instance['specific_post']) $selected = 'selected="selected"';
                        else $selected = '';
                        printf('<option value="%s" %s>%s</option>', $id, $selected, $title);
                        ?>
                    <?php }?>
                </select>
            </div>
        </p>
            <p>
                <em>Lọc bài viết với các điều kiện trên</em><br/>
                <span><a href="javascript:void(0)"  onclick="__hwcpl_object.query_posts(this,'#<?php echo $this->get_field_id('filter_query')?>','query_posts_cbs')" data-id="<?php echo $this->number?>" class="button hwtpl-filter-posts-btn"><?php _e('Bắt đầu')?></a></span><br/>
                <em>Lưu ý: lưu widget lần đầu để thông số 'Term' & 'Lấy một bài viết' được lưu lại.</em>
            </p>
            </div>
    </fieldset>
    <script>
        jQuery(document).ready(function(){
            /**
             * hwtpl_change_terms_taxonomy callbacks
             */
            if(typeof __hwcpl_object.add_callback == 'function')
            __hwcpl_object.add_callback('<?php echo $this->number?>','hwtpl_change_terms_taxonomy_cbs', {
                before_ajax : function() {console.log('before_ajax');
                    ttt=jQuery('#<?php echo $this->get_field_id('posttype')?>');
                    jQuery('#<?php echo $this->get_field_id('posttype')?>').attr('disabled', 'disabled');
                },
                after_ajax : function() {console.log('after_ajax');
                    jQuery('#<?php echo $this->get_field_id('posttype')?>').removeAttr('disabled');

                }
            });
            /**
             * hwtpl_change_taxonomies_posttype callbacks
             */
            if(typeof __hwcpl_object.add_callback == 'function')
            __hwcpl_object.add_callback('<?php echo $this->number?>', 'hwtpl_change_taxonomies_posttype_cbs', {
                before_ajax : function() {
                    var select_tag = jQuery('#holder-<?php echo $this->get_field_id('specific_post')?> select:eq(0)');
                    select_tag.html(' ').append(jQuery('<option>', {value:'',text : 'Loading..'}));
                },
                after_ajax : function(data) {
                    var select_tag = jQuery('#holder-<?php echo $this->get_field_id('specific_post')?> select:eq(0)');
                    select_tag.html(' ').append(jQuery('<option>', {
                        value: "",
                        text: "------Select------"  //.data[value]
                    }));

                    if(data.posts)
                    jQuery.each(data.posts, function(value, text) {
                        select_tag.append(jQuery('<option>', {
                            value: value,
                            text: text  //.data[value]
                        }));
                    });


                }
            });
            /**
             * query_posts callbacks
             */
            if(typeof __hwcpl_object.add_callback == 'function')
            __hwcpl_object.add_callback('<?php echo $this->number?>', 'query_posts_cbs', {
                before_ajax: function() {
                    var select_tag = jQuery('select#<?php echo $this->get_field_id('specific_post')?>');
                    //place holder item
                    select_tag.html(' ').append(jQuery('<option>', {
                        value: '',
                        text: 'loading...'  //.data[value]
                    }));
                },
                after_ajax: function (data){
                    var select_tag = jQuery('select#<?php echo $this->get_field_id('specific_post')?>');
                    select_tag.html(' ');

                    if(data.posts)
                        jQuery.each(data.posts, function(value, text) {
                            select_tag.append(jQuery('<option>', {
                                value: value,
                                text: text  //.data[value]
                            }));
                        });
                }

            });
        });

    </script>
</div>
<?php if(isset($btn_tog)) $btn_tog->set_button_toggle_start_wrapper('Nâng cao...');?>

<!-- options -->
<div style="border:1px dotted #dadada;background:#F0ECEC;padding:5px;">
<fieldset>
    <legend><?php _e('Thuộc tính')?></legend>
<p>
    <label for="<?php echo $this->get_field_id("num"); ?>">
        <?php _e('Số lượng posts hiển thị'); ?>:
    </label>
    <input class="digits" id="<?php echo $this->get_field_id("num"); ?>" name="<?php echo $this->get_field_name("num"); ?>" type="text" value="<?php echo !empty($instance["num"])?  absint($instance["num"]) : '-1'; ?>" size='4' maxlength="5"/>
    <br/>(-1 for all posts)
</p>
<p>
    <label for="<?php echo $this->get_field_id("display"); ?>">
        <?php _e('Trường hiển thị'); ?>:
    </label>
    <!-- name="display[]" -->
    <select id="<?php echo $this->get_field_id("display"); ?>" name="display[]" multiple="multiple" class="display" size="5">
        <?php
        $arrDisplay = array("title","excerpt","comment_num","date","thumb","author");
        $arrDisplayLabels = array("title" => "Post Title" , "excerpt" => "Short Description" ,"comment_num" => "Comment Count" ,"date" => "Post Date" ,"thumb" => "Post Thumbnail" ,"author" => "Post Author" );
        foreach($arrDisplay as $strValue)
        {
            ?>
            <option value="<?php echo $strValue; ?>" <?php echo (in_array($strValue,$arrExlpodeFields) || $strValue == 'title') ? "selected=selected" : ''; ?>><?php echo $arrDisplayLabels[$strValue]; ?></option>
        <?php } ?>
    </select><br/>

</p>
<p><!-- more meta keys -->
    <label for="<?php echo $this->get_field_id("more_meta_keys"); ?>"><?php _e('Chọn meta keys')?></label>
<div id="<?php echo $this->get_field_id('holder_meta_keys')?>">
    <?php

    if(isset($instance['more_meta_keys'])){
        $mt_keys = HW_POST::generate_posttypes_meta_keys($instance['posttype']);

        $out = sprintf( '<select name="%s" id="%s" multiple style="max-height:200px;width:200px">', esc_attr( $this->get_field_name('more_meta_keys').'[]' ), esc_attr( $this->get_field_id('more_meta_keys') ));
        // Holds the HTML markup.
        $structure = array();

        foreach ( $mt_keys as $key ) {
            $selected = in_array($key, (array)$instance['more_meta_keys']);
            $structure[] = sprintf(
                '<option value="%s" key="%s" '.($selected? 'selected="selected"':'').'>%s</option>',
                esc_attr( $key ),
                esc_attr( $key ),
                esc_html( $key )
            );
        }
        $out .= join( "\n", $structure );
        $out .= '</select>';
        echo $out;
    }
    ?>

</div>
<a href="javascript:void(0)" onclick="__hwcpl_object.get_customfields_by_type(jQuery('#<?php echo $this->get_field_id('posttype');?>'),'<?php echo $this->id?>','<?php echo $this->get_field_id('holder_meta_keys');?>')">Lấy lại Custom Fields</a>
</p>
<p>
    <label for="<?php echo $this->get_field_id("sort_by"); ?>">
        <?php _e('Xắp xếp'); ?>:
    </label>
    <select id="<?php echo $this->get_field_id("sort_by"); ?>" name="<?php echo $this->get_field_name("sort_by"); ?>">
        <option value="date"<?php selected( $instance["sort_by"], "date" ); ?>>Ngày</option>
        <option value="title"<?php selected( $instance["sort_by"], "title" ); ?>>Tiêu đề</option>
        <option value="comment_count"<?php selected( $instance["sort_by"], "comment_count" ); ?>>Số lượng comments</option>
        <option value="rand"<?php selected( $instance["sort_by"], "rand" ); ?>>Tự động</option>
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id("sort_order"); ?>" >
        <?php _e('Thứ tự xắp xếp'); ?>:
    </label>
    <select id="<?php echo $this->get_field_id("sort_order"); ?>" name="<?php echo $this->get_field_name("sort_order"); ?>">
        <option value="desc" <?php selected( $instance["sort_order"], "desc" ); ?>>Giảm dần (DESC)</option>
        <option value="asc" <?php selected( $instance["sort_order"], "asc" ); ?>>Tăng dần (ASC)</option>
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id("excerpt_length"); ?>">
        <?php _e( 'Số lượng từ Excerpt (in words):' ); ?>
    </label>
    <input class="digits" type="text" id="<?php echo $this->get_field_id("excerpt_length"); ?>" name="<?php echo $this->get_field_name("excerpt_length"); ?>" value="<?php echo $instance["excerpt_length"]; ?>" size="5" maxlength="4"/>
</p>
<p>
    <label for="<?php echo $this->get_field_id("post_title_leng"); ?>">
        <?php _e( 'Giới hạn ký tự post title (ký tự):' ); ?>
    </label>
    <input class="digits" type="text" id="<?php echo $this->get_field_id("post_title_leng"); ?>" name="<?php echo $this->get_field_name("post_title_leng"); ?>" value="<?php echo $instance["post_title_leng"]; ?>" size="5" maxlength="4"/>
</p>
<p>
    <label for="<?php echo $this->get_field_id("date_format"); ?>">
        <?php _e( 'Định dạng Ngày/tháng :' ); ?>
    </label>
    <input class="" type="text" id="<?php echo $this->get_field_id("date_format"); ?>" name="<?php echo $this->get_field_name("date_format"); ?>" value="<?php echo $instance["date_format"]; ?>"  size='20' maxlength="20"/>
    <br/>November 28, 2012 (F j, Y)
</p>
    <hr/>
    <p>
        <label for="<?php echo $this->get_field_id('intermediate_image_sizes')?>"><?php _e('Image Size')?></label>
        <select name="<?php echo $this->get_field_name('intermediate_image_sizes')?>" id="<?php echo $this->get_field_id('intermediate_image_sizes')?>">
            <option value="">Chỉ định</option>
            <?php
            foreach(get_intermediate_image_sizes() as $size) {
                $selected = selected(isset($instance['intermediate_image_sizes'])? $instance['intermediate_image_sizes']:'', $size,false);

                printf('<option value="%s" %s>%s</option>', $size, $selected, $size);
            }
            ?>
        </select>
    </p>
    <p>
        <label><?php _e('Kích thước Thumbnail'); ?>:</label>
        <br/>
        <label for="<?php echo $this->get_field_id("thumb_w"); ?>">
            Width:
        </label>
        <input class="digits" type="text" id="<?php echo $this->get_field_id("thumb_w"); ?>" name="<?php echo $this->get_field_name("thumb_w"); ?>" value="<?php echo isset($instance["thumb_w"])? $instance["thumb_w"]:''; ?>"  size='5'  maxlength="3"/> px
        <br/>
        <label for="<?php echo $this->get_field_id("thumb_h"); ?>">
            Height:
        </label>
        <input class="digits" type="text" id="<?php echo $this->get_field_id("thumb_h"); ?>" name="<?php echo $this->get_field_name("thumb_h"); ?>" value="<?php echo isset($instance["thumb_h"])? $instance["thumb_h"]:''; ?>"  size='5' maxlength="3"/> px
    </p>
</fieldset>
</div>
<!-- by hoangweb: pagination -->
<div style="border:1px dotted #dadada;background:#F0ECEC;padding:5px;">
    <fieldset><legend>Phân trang (ajax)</legend>
        <p>
            <label for="<?php echo $this->get_field_id("show_pagination"); ?>">
                <input type="checkbox" id="<?php echo $this->get_field_id("show_pagination"); ?>" <?php checked(isset($instance['show_pagination'])  && $instance['show_pagination']? 1 : 0);?> name="<?php echo $this->get_field_name('show_pagination'); ?>"/>
                <?php _e('Kích hoạt phân trang.')?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("enable_ajax_pagination"); ?>">
                <input type="checkbox" id="<?php echo $this->get_field_id("enable_ajax_pagination"); ?>" <?php checked(isset($instance['enable_ajax_pagination'])  && $instance['enable_ajax_pagination']? 1 : 0);?> name="<?php echo $this->get_field_name('enable_ajax_pagination'); ?>"/>
                <?php _e( 'Kích hoạt phân trang Ajax:' ); ?>
            </label>

        <div>Chú ý: Nếu không kích hoạt phân trang ajax bạn cần chọn danh mục cụ thể, nếu không sẽ không hiển thị phân trang.</div>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("nav_posts_num"); ?>">
                <?php _e( 'Số lượng Posts /page:' ); ?>
            </label>
            <input type="text" id="<?php echo $this->get_field_id("nav_posts_num"); ?>" name="<?php echo $this->get_field_name('nav_posts_num'); ?>" value="<?php echo !empty($instance['nav_posts_num'])? absint($instance['nav_posts_num']) : get_option('posts_per_page')?>"/><br/>
            <span><em>(-1: hiển thị tất, để trống: cài đặt mặc định)</em></span>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_prev_next')?>">
                <input type="checkbox" name="<?php echo $this->get_field_name('show_prev_next')?>" id="<?php echo $this->get_field_id('show_prev_next')?>" <?php checked((isset($instance['show_prev_next']) && $instance['show_prev_next']? 1:0))?>/>
                <?php _e('Hiển thị nút prev_next')?>.
            </label>
        </p>
        <p>
            <label>
                <input type="checkbox" name="<?php echo $this->get_field_name('use_default_pagenav_skin')?>" id="<?php echo $this->get_field_id('use_default_pagenav_skin')?>" <?php checked((isset($instance['use_default_pagenav_skin']) && $instance['use_default_pagenav_skin']? 1:0))?>/>
                <?php _e('Sử dụng giao diện phân trang mặc định <a href="'.admin_url('options-general.php?page=pagenavi').'" target="_blank">tại đây</a> (Chú ý: bạn cần kích hoạt module "Phân trang")')?>.
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('pagination_skin')?>"><?php _e('Giao diện phân trang')?></label>
        <div><?php echo $this->skin->get_skin_instance('pagination')->get_skins_select_tag('pagination_skin');?></div>
        </p>
    </fieldset>
    <!-- end pagination option -->
    <hr/>
    <fieldset><legend>Liên kết xem toàn bộ</legend>
        <p>
            <label for="<?php echo $this->get_field_id("enable_cat_link"); ?>">
                <input type="checkbox" id="<?php echo $this->get_field_id("enable_cat_link"); ?>" <?php checked((isset($instance["enable_cat_link"]) && $instance["enable_cat_link"]=='on')? 1:0); ?> name="<?php echo $this->get_field_name('enable_cat_link'); ?>"/>

                <?php _e( 'Tạo link tiêu đề widget tới category/taxonomy (trang danh mục):' ); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("page_cat_link"); ?>">
                <?php _e( 'Trỏ liên kết tiêu đề widget tới trang:' ); ?>
            </label>
            <select id="<?php echo $this->get_field_id("page_cat_link"); ?>" name="<?php echo $this->get_field_name("page_cat_link"); ?>" class="widefat effect">
                <option value="">------Select------</option>
                <?php
                foreach(get_pages() as $item)
                {
                    ?>
                    <option value="<?php echo $item->ID; ?>" <?php selected( $instance["page_cat_link"], $item->ID ); ?>><?php echo ucfirst($item->post_title); ?></option>
                <?php } ?>
            </select>
        </p>
        <hr/>
        <p>
            <label for="<?php echo $this->get_field_id('view_all_pos')?>"><?php _e('Thêm liên kết trang danh mục vào:')?></label>
            <select name="<?php echo $this->get_field_name('view_all_pos')?>" id="<?php echo $this->get_field_id('view_all_pos')?>">
                <option value="top" <?php selected($instance['view_all_pos'] == 'top'? 1:0)?>><?php _e('Trên')?></option>
                <option value="bottom" <?php selected($instance['view_all_pos'] == 'bottom'? 1:0)?>><?php _e('Dưới')?></option>
                <option value="top_bottom" <?php selected($instance['view_all_pos'] == 'top_bottom'? 1:0)?>><?php _e('Trên & dưới')?></option>
                <option value="custom" <?php selected($instance['view_all_pos'] == 'custom'? 1:0)?>><?php _e('Tự chèn vào skin')?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('view_all_text')?>"><?php _e('Văn bản liên kết:')?></label>
            <input type="text" name="<?php echo $this->get_field_name('view_all_text')?>" id="<?php echo $this->get_field_id('view_all_text')?>" value="<?php echo $instance['view_all_text']?>"/>
        </p>
    </fieldset>
    <fieldset><legend>Giao diện</legend>
        <em>Thư mục chứa giao diện "<?php echo self::SKINS_FOLDER?>".</em>
        <!-- skin -->
        <?php if(class_exists('HW_SKIN')){?>
            <p>
                <label for="<?php echo $this->get_field_id("skin"); ?>">
                    <strong><?php _e( 'Skin :' ); ?></strong>
                    <?php echo $this->skin->get_skins_select_tag('skin',0,array('class'=>'widefat'),false)?>
                </label>

            </p>
            <p><!-- skin options fields -->
                <?php echo $skin_options;?>
            </p>
        <?php }?>

    </fieldset>
    <hr/>
    <fieldset>
        <legend ><?php _e('Kích hoạt thanh cuộn')?></legend>
        <p>
            <label><?php _e('Kích hoạt thanh cuộn'); ?>:
                <input type="checkbox" name="<?php echo $this->get_field_name("enable_scrollbar"); ?>" id="<?php echo $this->get_field_id("enable_scrollbar"); ?>" class="" <?php echo (isset($instance["enable_scrollbar"]) && $instance["enable_scrollbar"]) ? 'checked' : '';?>/>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('scrollbar_skin')?>"><?php _e("Giao diện thanh cuộn")?></label>
            <div><?php echo $this->skin->get_skin_instance('scrollbar')->get_skins_select_tag('scrollbar_skin',null,array('class'=>'widefat',),false);?></div>
        </p>
        <p>
            <?php
            echo $scrollbar_skin_options;
            ?>
        </p>
    </fieldset>
    <hr/>
    <!-- scrolling content -->
    <fieldset>
        <legend ><?php _e('Cuộn nội dung')?></legend>
        <p>
            <label><?php _e('Kích hoạt cuộn nội dung'); ?>:
                <input type="checkbox" name="<?php echo $this->get_field_name("enable_scrolling"); ?>" id="<?php echo $this->get_field_id("enable_scrolling"); ?>" class="" <?php echo (isset($instance["enable_scrolling"]) && $instance["enable_scrolling"] == 'on') ? 'checked' : '';?>/>
            </label>
        </p>
        <p>

            <label for="<?php echo $this->get_field_id('scroll_type')?>"><?php _e('Kiểu'); ?>:</label>
            <select name="<?php echo $this->get_field_name('scroll_type')?>" id="<?php echo $this->get_field_id('scroll_type')?>">
                <?php foreach(self::$scroll_types as $type => $text){?>
                    <option <?php selected($type == $instance['scroll_type']? 1:0)?> value="<?php echo $type?>"><?php echo $text?></option>
                <?php }?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('auto_scroll_mode')?>"><?php _e('Cuộn Tự động'); ?>:</label>
            <input type="checkbox" name="<?php echo $this->get_field_name('auto_scroll_mode')?>" id="<?php echo $this->get_field_id("auto_scroll_mode"); ?>" <?php checked(isset($instance['auto_scroll_mode']) && $instance['auto_scroll_mode']? 1:0)?>/>
        </p>
        <hr/>
        <em>(Chỉ dành cho kiểu cuộn liên tục.)</em>
        <p>
            <label><?php _e('Định hướng cuộn'); ?>: </label>
            <select name="<?php echo $this->get_field_name("scroll_direction"); ?>" id="<?php echo $this->get_field_id("scroll_direction"); ?>">
                <?php

                foreach(self::$scrolldirections as $type=>$directions){
                    echo '<optgroup label="'.self::$scroll_types[$type].'">';
                    foreach($directions as $dir=>$text){
                        ?>
                        <option value="<?php echo $dir?>" <?php selected($dir== $instance['scroll_direction']?1:0)?>><?php echo $text?></option>
                    <?php }
                    echo '</optgroup>';
                }
                ?>
            </select><br/>

        </p>
        <p>
            <label for="<?php echo $this->get_field_id('visible_scroll_num')?>"><?php _e('Số lượng hiển thị'); ?>:</label>
            <input type="text" size="5" name="<?php echo $this->get_field_name('visible_scroll_num')?>" id="<?php echo $this->get_field_id("visible_scroll_num"); ?>" value="<?php echo isset($instance['visible_scroll_num'])? $instance['visible_scroll_num'] : ''?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('scroll_num')?>"><?php _e('Số lượng cuộn chuyển tiếp'); ?>:</label>
            <input type="text" size="5" name="<?php echo $this->get_field_name('scroll_num')?>" id="<?php echo $this->get_field_id("scroll_num"); ?>" value="<?php echo isset($instance['scroll_num'])? $instance['scroll_num'] : ''?>"/>
        </p>
        <p>
            <?php
            //more effects https://jqueryui.com/resources/demos/effect/easing.html
            $easing_effects = array(
                'linear','swing','easeInQuad','easeOutQuad','easeInOutQuad','easeInCubic','easeOutCubic','easeInOutCubic','easeInQuart','easeOutQuart','easeInOutQuart','easeInQuint','easeOutQuint','easeInOutQuint','easeInExpo','easeOutExpo','easeInOutExpo'
            );
            ?>
            <label for="<?php echo $this->get_field_id('scroll_easing')?>"><?php _e('Hiệu ứng slide')?></label>
            <select name="<?php echo $this->get_field_name('scroll_easing')?>" id="<?php echo $this->get_field_id('scroll_easing')?>">
                <option value="">Mặc định</option>
                <?php foreach($easing_effects as $effect){?>
                    <option value="<?php echo $effect?>"><?php echo $effect?></option>
                <?php }?>

            </select>
            <em>Sử dụng thư viện <a href="https://jqueryui.com/resources/demos/effect/easing.html" target="_blank">jQuery easing Effects</a></em>
        </p>
        <hr/>
        <p>
            <label for="<?php echo $this->get_field_id('scroll_width')?>"><?php _e('Scroll Width');?></label>
            <input type="text" name="<?php echo $this->get_field_name("scroll_width"); ?>" id="<?php echo $this->get_field_id("scroll_width"); ?>" value="<?php echo isset($instance['scroll_width'])? $instance['scroll_width'] : ''?>" size="5"/>px/%
        <div><em>Chú ý: điều chỉnh kích thước width phù hợp với số lượng nội dung chạy, để có thể cuộn lặp đi lặp lại.</em></div>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("scroll_height");?>"><?php _e('Scroll Height');?></label>
            <input type="text" name="<?php echo $this->get_field_name("scroll_height"); ?>" id="<?php echo $this->get_field_id("scroll_height"); ?>" value="<?php echo isset($instance['scroll_height'])? $instance['scroll_height'] : ''?>" size="5"/>px/% (<em>mặc định là 200 cho chạy dọc</em>)
        <div><em>Chú ý: điều chỉnh kích thước height phù hợp với số lượng nội dung chạy, để có thể cuộn lặp đi lặp lại.</em></div>
        </p>
        <hr/>
        <p>
            <label for="<?php echo $this->get_field_id('scroll_interval')?>"><?php _e('Tốc độ cuộn')?></label>
            <input type="text" size="5" name="<?php echo $this->get_field_name("scroll_interval");?>" id="<?php echo $this->get_field_id("scroll_interval");?>" value="<?php echo isset($instance['scroll_interval'])? $instance['scroll_interval'] : ''?>"/>
			<span><em>(chú ý: số càng lớn thì tốc độ càng chậm). </em>
                    <ul>
                        <li><em>- Nếu chọn kiểu cuộn liên tục thì giá trị nhập từ 10-60</em></li>
                        <li><em>- Nếu chọn kiểu cuộn ngắt nửa chừng thì giá trị có thể là: 800, 600, 1500,..</em></li>
                    </ul>
            </span>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('scroll_delay')?>"><?php _e('Độ dừng')?></label>
            <input type="text" size="5" name="<?php echo $this->get_field_name('scroll_delay')?>" id="<?php echo $this->get_field_id('scroll_delay')?>" value="<?php echo isset($instance['scroll_delay'])? $instance['scroll_delay']  :''?>"/>(ms)<br/>
            <em>(Chỉ dành cho cuộn ngắt quãng và bật chế độ cuộn tự động. VD: 800)</em>
        </p>
    </fieldset>
</div>
<!-- end hoangweb -->
<p>
    <a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">Xem định dạng date-time</a>
</p>
<p>
    <label for="<?php echo $this->get_field_id("effects"); ?>">
        <?php _e('Hiệu ứng'); ?>:
    </label>
    <select id="<?php echo $this->get_field_id("effects"); ?>" name="<?php echo $this->get_field_name("effects"); ?>" class="widefat effect">
        <?php
        $arrEffect = array("none","scrollHorz","scrollVert");
        foreach($arrEffect as $strKey => $strValue)
        {
            ?>
            <option value="<?php echo $strValue; ?>" <?php selected( $instance["effects"], "$strValue" ); ?>><?php echo ucfirst($strValue); ?></option>
        <?php } ?>
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id("effects_time"); ?>">
        <?php _e('Thời gian hiệu ứng (Duration) (milliseconds)'); ?>:
    </label>
    <input  class="digits" id="<?php echo $this->get_field_id("effects_time"); ?>" name="<?php echo $this->get_field_name("effects_time"); ?>" type="text" value="<?php echo absint($instance["effects_time"]); ?>" size='5' maxlength="5"/>
</p>
<p>
    <label><?php _e('Kích thước Widget'); ?>:</label>
    <br />
    <label for="<?php echo $this->get_field_id("widget_w"); ?>">
        Width:
    </label>
    <input class="widefat widget_dimension digits" type="text" id="<?php echo $this->get_field_id("widget_w"); ?>" name="<?php echo $this->get_field_name("widget_w"); ?>" value="<?php echo isset($instance["widget_w"])? $instance["widget_w"]:''; ?>"  size='5'  maxlength="4"/> px
    <br />
    <label for="<?php echo $this->get_field_id("widget_h"); ?>">
        Height:
    </label>
    <input class="widefat widget_dimension digits" type="text" id="<?php echo $this->get_field_id("widget_h"); ?>" name="<?php echo $this->get_field_name("widget_h"); ?>" value="<?php echo isset($instance["widget_h"])? $instance["widget_h"]:''; ?>"  size='5'  maxlength="4"/> px
</p>
