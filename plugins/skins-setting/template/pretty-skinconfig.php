<?php

?>

<table width="100%" border="1">
    <tr>
        <th><strong>Trường</strong></th>
        <th><strong>Giá trị</strong></th>
    </tr>
    <?php foreach($skin_info as $fname => $value){
            if($value === '' || $value === null) continue;
            //image tag
            if($fname == 'thumb') {
                $value = '<img src="'.$value.'"/>';
            }
            if(is_string($value)) {
                echo '<tr><td><strong>'.$fname.'</strong></td><td>'.$value.'</td></tr>';
            }
        }
    ?>

</table>