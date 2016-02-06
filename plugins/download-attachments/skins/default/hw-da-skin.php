<?php
/**
 * HW Template: default
 */

?>
<?php
/*
echo $args['content_before'];
?>
<table class="hw-download-attachments-table">
    <tr>
        <?php
        foreach($headers as $name => $label):
            #if($name == 'id') continue;
            printf('<td class="%s"><strong>%s</strong></td>', $name, $label);
        endforeach;
        ?>
    </tr>
<?php
foreach($attachments as $file) {
    //_print($file);
    ?>
    <tr class="<?php echo $file['class']?>">
        <?php
        //index
        printf('<td>%s</td>', $file['index']);

        //link, icon
        printf('<td>%s %s %s %s</td>', $file['icon'], $file['link_before'], $file['link'], $file['link_after']);
        //caption or description
        if(isset($file['caption'])) printf('<td>%s %s</td>', $file['caption'], $file['description']);
        // date
        if(isset($file['date'])) printf('<td>%s</td>', $file['date']);
        //user
        if(isset($file['user'])) printf('<td>%s</td>', $file['user']);
        //size
        if(isset($file['size'])) printf('<td>%s</td>', $file['size']);
        //display count
        if(isset($file['count'])) printf('<td>%s</td>', $file['count']);
        ?>
    </tr>
    <?php
}
?>
</table>
<?php
echo $args['content_after'];
*/