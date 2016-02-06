<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 17/06/2015
 * Time: 23:40
 */
?>
<table border="1" width="100%">
    <tr>
        <td>widget</td>
        <td><?php echo $setting->widget?></td>
    </tr>
    <tr>
        <td>name</td>
        <td><?php echo $setting->name?></td>
    </tr>
    <tr>
        <td>group</td>
        <td><?php echo $setting->_group?></td>
    </tr>
    <tr>
        <td>setting</td>
        <td>
            <table width="100%" border="1">
                <tr>
                    <td>Trường</td>
                    <td>Giá trị</td>
                </tr>
<?php $instance = (unserialize(base64_decode($setting->setting)));
foreach ($instance as $field =>$value) {
    if(!$value) continue;
    //skin preview
    if($field == 'skin_thumb'
        || $field == 'pagination_skin_thumb'
        || $field == 'scrollbar_skin_thumb')
    {
        $value = '<img src="'.$value.'"/>';
    }


    if(is_string($value)) {
        echo '<tr><td><strong>'.$field.'</strong></td><td>'.$value.'</td></tr>';
    }
}
?>
            </table>
        </td>
    </tr>
</table>