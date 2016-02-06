<?php
/**
 * Class HWAWC_SaveWidgets_shortcode
 */
if(class_exists('AdminPageFramework',false)) :
class HWAWC_SaveWidgets_shortcode extends AdminPageFramework{
    /**
     * setup form fields
     */
    public function setUp() {
        // Set the root menu
        $this->setRootMenuPage( 'Settings' );        // specifies to which parent menu to add.

        // Add the sub menus and the pages
        $this->addSubMenuItems(
            array(
                //'title'    =>    'Lưu cấu hình widgets',        // the page and menu title
                'page_slug'    =>    HWAWC_SaveWidgets_options::PAGE_SLUG         // the page slug
            )
        );
    }
}
//draft
endif;