<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 17/06/2015
 * Time: 15:38
 */
//list post type & their skin
HW_HOANGWEB::load_class('HW_UI_Component');
HW_HOANGWEB::load_class('AWC_WidgetFeature_saveconfig');    //widget feature of saveconfig
//hw_load_class('AWC_WidgetFeature_saveconfig');  //widget feature of saveconfig

if(!empty($this->skin)){
?>
<table class="hw-table" border="1" >
<tr>
    <td>Kích hoạt</td>
    <td>Kiểu dữ liệu</td>
    <td>Giao diện</td>
    <td>Giao diện từ HWTPL</td>
    <td>Giao diện box</td>
    <td>Giao diện box widget</td>
    <td>Tiêu đề</td>
    <td>Danh mục áp dụng</td>
    <td>Áp dụng tất</td>
    <td>Xem trước</td>
</tr>
<?php
global $hw_yarpp;
//_print(yarpp_get_option('hw1'));
$allow_pts = $hw_yarpp->get_option('hwrp_allow_post_types');   //allow post types for using yarpp

$skins = hw_yarpp_get_option('hwrp_skins');    //get skins for support post types

$pts = get_post_types('','objects');    //list all registered post types

//skin preview
$init_skin_preview = false;

if(is_array($allow_pts)){
    foreach($allow_pts as $post_type){
        $aSkin_field_name = 'hwrp_skins['.$post_type.']';       //field skins data

        //get all taxonomy terms assign to post type
        $terms = hwrp_get_all_terms_taxonomies($post_type);


        $active_skin4pt = isset($skins[$post_type]['skin'])? $skins[$post_type]['skin'] : '';   //active skin for this post type
        $pt = $pts[$post_type]; //get post type object
        //skins
        $atts = array(
            'name' => $aSkin_field_name.'[skin]',
            'id' => "hwrp_skins_{$post_type}"
        );
        $skins_list = $this->skin->get_skins_select_tag(null,$active_skin4pt,$atts,false);
        $skin_preview_holder = $this->skin->get_skins_preview_screen(null); //skin preview

        //get skin config
        if(1||empty($skins[$post_type]['hwskin_config'])) { //ensure sure to keep update skin config
            $skins[$post_type]['hwskin_config'] = $this->skin->get_config(true);
        }

        //enable filter skin terms
        $enable_filter_term = isset($skins[$post_type]['filter_terms'])? 1 : 0;
        $active = isset($skins[$post_type]['active'])? 1 : 0;

        //get all active sidebars select
        $sidebars = hwawc_get_active_sidebars_select();
        $current_box = isset($skins[$post_type]['box_skin'])? $skins[$post_type]['box_skin'] : '';

        //pick sidebar widget skin
        $current_box_skin = isset($skins[$post_type]['box_widget_skin'])? $skins[$post_type]['box_widget_skin'] : '';

        //since we use hw-taxonomy-post-list-widget plugin to query all posts type in wp, and I also apply to this plugin
        $widgets_settings = AWC_WidgetFeature_saveconfig::get_widgets_settings_select(' where widget="hw_taxonomy_post_list_widget"');
        $current_widgetconfig = isset($skins[$post_type]['widget_config'])? $skins[$post_type]['widget_config'] : '' ;

        echo '<tr>';
        echo '<td><input type="checkbox" name="'.$aSkin_field_name.'[active]" id="enable_'.$post_type.'" '.($active? 'checked="checked"' : '').'/></td>';    //enable tog
        echo "<td>{$pt->labels->name}</td>";
        /**
         * skins chooser
         */
        echo '<td>'.$skins_list;
        //first develop, we use same hw_skin instance to manage all post types
        //echo '<input type="hidden" name="'.$aSkin_field_name.'[hwskin_config]" value="'.$skins[$post_type]['hwskin_config'].'"/></td>';
        echo $this->skin->create_config_hiddenfield($aSkin_field_name, $skins[$post_type]);
        echo '</td>';
        //echo '<span></span>';

        /**
         * get widgets settings from db by hw_taxonomy_post_list_widget widget
         */
        echo '<td>';
        echo HW_UI_Component::build_select_tag($widgets_settings, $current_widgetconfig, array(
            'name' => $aSkin_field_name.'[widget_config]',
            'id' => $post_type.'_widget_config'
        ));

        echo '</td>';

        /**
         * box sidebar
         */
        echo '<td>';
        echo HW_UI_Component::build_select_tag($sidebars, $current_box, array(
            'name' => $aSkin_field_name.'[box_skin]',
            'id' => $post_type.'_box_skin'
        ));

        echo '</td>';

        /**
         * sidebar skins
         */
        echo '<td>';
        $sidebar_skins_data = array();
        $sidebar_skins = HW_AWC_Sidebar_Settings::available_widget_skins();
        foreach($sidebar_skins as $theme_name => $opt) {
            $sidebar_skins_data[$theme_name] = $opt['title'];

        }
        echo HW_UI_Component::build_select_tag($sidebar_skins_data, $current_box_skin, array(
            'name' => $aSkin_field_name.'[box_widget_skin]',
            'id' => $post_type.'_box_widget_skin'
        ));
        echo '</td>';

        /**
         * title
         */
        echo '<td><input type="text" name="' .$aSkin_field_name. '[title]" value="'.(isset($skins[$post_type]['title'])? $skins[$post_type]['title'] : '').'"/></td>';

        /**
         * get taxonomies terms assign to post type
         */
        echo '<td>';
        //echo '';
        foreach($terms as $t){
            $skin_for_term_field = $aSkin_field_name.'[terms][]';
            $skin_for_term_field_id = 'hwrp_skins_'.$post_type.'_term_'.$t->slug;
            $term_in_list = isset($skins[$post_type]['terms']) && in_array($t->slug,$skins[$post_type]['terms'])? 'checked="checked"':'';   //check term already skin

            echo '<label ><input '.$term_in_list.' type="checkbox" name="'.$skin_for_term_field.'" id="'.$skin_for_term_field_id.'" value="'.$t->slug.'" />'.$t->name.'</label>';

        }
        echo '</td>';
        /**
         * enable all terms
         */
        echo '<td><label ><input type="checkbox" name="'.$aSkin_field_name.'[filter_terms]" id="" '.($enable_filter_term? 'checked="checked"' : '').' /></label></td>';
        /**
         * skin preview
         */
        if(!$init_skin_preview) {
            echo '<td rowspan="'.count($allow_pts).'">'.$skin_preview_holder.'</td>';
            $init_skin_preview = true;
        }

        echo '</tr>';
    }

}

?>
</table>
Để cài đặt riêng rẽ template nhấn <a target="_blank" href="<?php  echo esc_url(admin_url('options-general.php?page=hw_settings#section-my_posttype_settings__0'))?>">vào đây</a> và chọn tab "Post Type".

<p><strong>Chú ý</strong>:
    <ul>
        <li>- Tạo các files skin chứa trong plugin này + trong thư mục <code>wp-content/hw_relatedposts_skins</code> + và <code>{THEME_FOLDER}/hw_relatedposts_skins</code></li>
        <li>Skin file có thể là 1 file độc lập không nằm trong thư mục, copy các files template từ module <em>hw-yarpp/../yarpp-templates</em> vào <em>{THEME_FOLDER}/hw_relatedposts_skins</em></li>
        <li>- Sử dụng thêm skins lấy từ plugin <em>hw-taxonomy-post-list</em> loại trừ <code>wp-content/wcp_hw_skins</code> và <code>{THEME_FOLDER}/wcp_hw_skins</code></li>
        <li>- Việc thiết lập widget skin tại widget "Truy xuất nội dung theo chuyên mục" không có tác dụng.
            Mà lựa chọn ở cột "<strong>Giao diện box widget</strong>" để thay đổi skin cho sidebar (box) đó.</li>
        <li>- Trong trường hợp sử dụng <strong>Giao diện từ HWTPL</strong> sẽ kế thừa cài đặt tùy chỉnh widget nếu bạn hiển thị với sidebar đó. VD: Sửa before_title, Sửa after_title, Sửa before_widget... DO vậy: cần đặt widget vào đúng sidebar bạn muốn dùng để thiết tùy chỉnh sidebar cho chính xác trước khi lưu cấu hình.</li>
    </ul>
</p>
<?php
}
else hw_inline_msg('Module HW SKIN chưa kích hoạt.');
?>