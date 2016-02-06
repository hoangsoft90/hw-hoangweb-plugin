<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

/** 
* Function _fnCPLWShowDiaglogContent()
* Used to display digalog content & allow wordpress function to use in ytp-dialog.php
* @return html 
*/
function hw_fnCPLWShowDiaglogContent(){
  include_once(dirname(__FILE__)."/templates/add-shortcode.php");
  die();
}
add_action('wp_ajax_show_CPLW_diaglogbox', 'hw_fnCPLWShowDiaglogContent'); //dialog box content
?>