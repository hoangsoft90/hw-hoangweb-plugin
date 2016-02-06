<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 15/06/2015
 * Time: 21:06
 */
$widopt_groups = apply_filters('hw_awc_widget_saveconfig_group', AWC_WidgetFeature_saveconfig::get_groups());
//_print($t->id_base);

if(method_exists($t, 'AWC_WidgetFeature_saveconfig') ) {        //IF current widget tell you how to get it?
    $saveconfig = $t->AWC_WidgetFeature_saveconfig($instance);
}
else $saveconfig = $instance;

?>
<fieldset>
    <legend>Lưu cài đặt</legend>
    <p>Lưu ý: cần lưu widget trước khi sử dụng tính năng này.</p>
<p>
    <label for="<?php echo $this->get_field_id('hw_widopt_group')?>"><?php _e("Nhóm")?></label><br/>
    <select class="hw_widopt_group" name="<?php echo $this->get_field_name('hw_widopt_group')?>" id="<?php echo $this->get_field_id('hw_widopt_group')?>">
        <?php
        foreach ($widopt_groups as $value => $text) {
            printf('<option value="%s">%s</option>', $value, $text);
        }
        ?>
    </select>
</p>
<p>
    <label for="<?php echo $this->get_field_id('hw_widopt_name')?>"><?php _e("Tên cấu hình")?></label><br/>
    <input class="hw_widopt_name" type="text" name="<?php echo $this->get_field_name('hw_widopt_name')?>" id="<?php echo $this->get_field_id('hw_widopt_name')?>" value=""/>
</p>
<p>
    <label for="<?php echo $this->get_field_id('hw_widopt_desc')?>"><?php _e("Mô tả")?></label><br/>
    <textarea class="hw_widopt_desc" name="<?php echo $this->get_field_name('hw_widopt_desc')?>" id="<?php echo $this->get_field_id('hw_widopt_desc')?>"></textarea>
</p>
    <input type="hidden" class="hw_widopt_setting" name="<?php echo $this->get_field_name('hw_widopt_setting')?>" id="<?php echo $this->get_field_id('hw_widopt_setting')?>" value="<?php echo AWC_WidgetFeature_saveconfig::encode_config($saveconfig)?>"/>
<p>
    <input type="button" class="button" name="saveconfig_btn" value="<?php _e('Lưu')?>" onclick="hwawc_widfea_saveconfig(this,'#<?php echo $this->get_field_id('result')?>')"/>
    <div id="<?php echo $this->get_field_id('result')?>"></div>
</p>
    <p>Quản lý danh sách lưu cấu hình <a target="_blank" href="<?php echo admin_url('options-general.php?page='.HWAWC_SaveWidgets_options::PAGE_SLUG)?>">tại đây</a>.</p>
</fieldset>
<script>
function hwawc_widfea_saveconfig(obj, result) {
    var container = jQuery(jQuery(obj).closest('fieldset')),
        group = container.find('.hw_widopt_group:eq(0)'),
        name = container.find('.hw_widopt_name:eq(0)'),
        desc = container.find('.hw_widopt_desc:eq(0)'),
        setting = container.find('.hw_widopt_setting:eq(0)');

    //valid
    if(!name.val() ) {
        name.focus();
        return;
    }

    //create loading animation
    if(!jQuery(obj).data('loadingObj')) {
        jQuery(obj).data({loadingObj: jQuery('<img/>').attr('src','<?php echo HW_AWC_URL?>/images/loading.gif')});
    }
    jQuery(obj).parent().append(jQuery(obj).data('loadingObj'));    //add loading image

    jQuery.ajax({
        url : '<?php echo AWC_WidgetFeature_saveconfig::$ajax_save_url?>',
        method : 'post',
        data : {name: name.val(), group: group.val(), description: desc.val(), setting: setting.val(), widget: '<?php echo $t->id_base?>'},
        success : function(data) {
            container.find('input[type=text],textarea').val('');    //clear input
            jQuery(obj).data('loadingObj').remove();
            jQuery(result).html('ID=' + data);
            console.log(data);
        }
    });
    return false;   //prevent default
}
</script>