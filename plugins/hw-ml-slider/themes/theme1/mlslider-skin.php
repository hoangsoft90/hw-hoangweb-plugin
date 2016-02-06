<?php
/**
Plugin Name: Skin 1
*/
?>
<?php
$theme = array();
if($settings['type'] == 'flex'){
    $theme['styles'] = array('style.css');
    //wp_enqueue_style('mlslider-skin1',plugins_url('style.css',__FILE__));
}

if($settings['type'] == 'nivo') {
    $theme['styles'] = array('style-nivo.css');
    //wp_enqueue_style('mlslider-skin1',plugins_url('style-nivo.css',__FILE__));
}
?>