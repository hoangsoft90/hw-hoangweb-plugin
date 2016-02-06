<h3 class="hndle">Hình ảnh bộ đếm</h3>
<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 20/10/2015
 * Time: 16:41
 */
$styles_url = $this->option('module_url'). '/assets/styles';
$statsmechanic_style = $this->get_field_value('statsmechanic_style');
$data = hw_acak($this->module_path . '/assets/styles/');

foreach ($data as $parent_folder => $records) {
    foreach ($records as $style_folder => $style_records) {
        foreach ($style_records as $style => $test) {
            preg_match('/styles\/(.*?)\/(.*?)\//', $test, $match);
            $groups[$match[1]][] = $match[2];
        }
    }
}

foreach ($groups as $style_name => $style) {
    ?>

    <p><b>Chọn giao diện bộ đếm <?php //echo $style_name; ?>:</b></p>
    <table class="form-table">
        <?php
        foreach ($style as $name) {
            ?>
            <tr>
                <td>
                    <input type="radio" id="img1" name="<?php echo $this->create_full_field_name('statsmechanic_style')?>" value="<?php echo $style_name . '/' . $name; ?>" <?php echo checked($style_name . '/' . $name, $statsmechanic_style) ?> />
                    <img src='<?php echo $styles_url.'/'. $style_name . '/' . $name . '/'; ?>0.gif'>
                    <img src='<?php echo $styles_url.'/' . $style_name . '/' . $name . '/'; ?>1.gif'>
                    <img src='<?php echo $styles_url. '/'. $style_name . '/' . $name . '/'; ?>2.gif'>
                    <img src='<?php echo $styles_url .'/'. $style_name . '/' . $name . '/'; ?>3.gif'>
                    <img src='<?php echo $styles_url .'/'. $style_name . '/' . $name . '/'; ?>4.gif'>
                </td>
            </tr>
        <?php
        }
        ?>

    </table>

<?php
}
?>
<style type="text/css">
    /*ADMIN STYLING*/
    .form-table {
        clear: none;
    }
    .form-table td {
        vertical-align: top;
        padding: 16px 20px 5px;
        line-height: 10px;
        font-size: 12px;
    }
    .form-table th {
        width: 200px;
        padding: 10px 0 12px 9px;
    }
    .mvc_right_sidebar {
        width: 42%;
        float: right;
    }
    .mvc_left_sidebar {
        width: 55%;
        margin-left: 10px;
    }
    .mvc_plugins_text {
        margin-bottom: 0px;
    }
    .mvc_plugins_text p {
        padding: 5px 10px 10px 10px;
        width: 90%;
    }
    .mvc_plugins_text h2 {
        font-size: 14px;
        padding: 0px;
        font-weight: bold;
        line-height: 29px;
    }
    .mvc_plugins_wrap .hndle {
        font-size: 15px;
        font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
        font-weight: normal;
        padding: 7px 10px;
        margin: 0;
        line-height: 1;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
        border-bottom-color: rgb(223, 223, 223);
        text-shadow: 0px 1px 0px rgb(255, 255, 255);
        box-shadow: 0px 1px 0px rgb(255, 255, 255);
        background: linear-gradient(to top, rgb(236, 236, 236), rgb(249, 249, 249)) repeat scroll 0% 0% rgb(241, 241, 241);
        margin-top: 1px;
        border-bottom-width: 1px;
        border-bottom-style: solid;
        -moz-user-select: none;
    }
    .mvc_option_wrap {
        border:1px solid rgb(223, 223, 223);
        width:100%;
        margin-bottom:30px;
        height:auto;
    }

</style>