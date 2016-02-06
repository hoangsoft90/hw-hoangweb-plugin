<?php
# used by includes/website/hw-settings-implementation.php
class NHP_Options_footer_Frontend extends NHP_Options_footer {
    function __construct() {
        //add_action('init', array($this, '_init_hook'));
    }
}
HW_Options_Frontend::add_fragment(new NHP_Options_footer_Frontend());