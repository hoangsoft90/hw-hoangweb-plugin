<?php
/**
 * get livechat apf option
 * @param string $opt: give name of option want to getting
 * @param string $default: default value    (optional)
 * @param string $group: group section name (optional)
 */
function hw_livechat_option($opt,$default='',$group = ''){
    if($group) return AdminPageFramework::getOption( 'HW_Livechat_settings', array($group,$opt), $default );
    else return AdminPageFramework::getOption( 'HW_Livechat_settings', $opt, $default );
}