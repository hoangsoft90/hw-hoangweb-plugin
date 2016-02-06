<?php
require_once( dirname( __FILE__ ) . '/libs/admin-page-framework/admin-page-framework-loader.php' );
/**
 * extending the framework admin factory class
 */
class hwngrok_settings extends AdminPageFramework {
    //tell the framework what page to create
    public function setUp() {
        $this->setRootMenuPage( 'Settings' );      # set the top-level page, ie add a page to the Settings page
        #add sub-menu pages
        $this->addSubMenuItem(

            array(
                'title'     => 'Public Localhost',
                'page_slug' => 'hw_public_localhost',

            )

        );
    }
    function load_hw_public_localhost($oAdminPage){
        $this->addSettingFields(
            array(    // Single text field
                'field_id'      => 'text_baseurl_ngrok',
                'type'          => 'text',
                'title'         => 'Ngrok ID',
                'description'   => 'Base URL ngrok',     #description that will be placed below the input field
                'value' => NGROK_ID
            ),

            array( // Submit button
                'field_id'      => 'submit_button',
                'type'          => 'submit',
            )
        );
    }
    //Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
    public function do_hw_public_localhost() {
        ?>


    <?php

    }
}
//Instantiate the Class
if(is_admin()) {
    new hwngrok_settings;
}
