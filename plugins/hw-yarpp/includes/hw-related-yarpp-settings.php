<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 25/05/2015
 * Time: 17:12
 */
if(class_exists('AdminPageFramework_MetaBox_Page')):
/**
 * Class HW_RelatedPost_Metabox_settings
 */
class HW_RelatedPost_Metabox_settings extends AdminPageFramework_MetaBox_Page {
    /**
     * menu page slug
     */
    const menu_setting_page_slug = 'hw_yarpp';

    /**
     * display something
     * Methods for Hooks, echo page content rule: do_{page slug} and it will be automatically gets called.
     */
    public function do_settings_page_hw_yarpp(){
        //$data = hw_navmenu_option();
        echo '<p>Thiết lập cài đặt cho menu.</p>';
    }
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        $menu = self::get_active_menu(); //current menu

        /**
         * Adds setting fields in the meta box.
         */
        /*$settings_fields[] = array(
            'field_id' => 'test',
            'type'=>'text',
            'title' => 'sdfsf'
        );*/
        $settings_fields[] = array(
            'type' => 'submit',
            'field_id'      => 'submit_button1',
            'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            'label' => 'Lưu lại'
        );

        $settings_fields[] = array(
            'field_id' => ('enable_filter_menu'),
            'type'=>'checkbox',
            #'value' => '#fff',
            'title' => 'Kích hoạt tùy chỉnh menu',
            'label' => 'Kích hoạt cho phép tùy chỉnh menu với các cài đặt dưới.',

        );

        $settings_fields[] = array(
            'type' => 'submit',
            'field_id'      => 'submit_button2',
            'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
            'label' => 'Lưu lại'
        );
        //register fields
        foreach($settings_fields as $aFieldSetting){
            $this->addSettingField($aFieldSetting);
        }
        // content_{page slug}_{tab slug}
        add_filter( 'content_settings_page_hw_yarpp', array( $this, 'replyToInsertContents' ) );
    }
    /**
     * custom HTML content around metabox content
     * @param $sContent
     * @return string
     */
    public function replyToInsertContents( $sContent ) {
        //$_aOptions  = get_option( 'APF_Tabs', array() );

        //$edit_menu_page = self::get_edit_menu_setting_page_link();
        $btn = '<a href="javascript:void(0)" onclick="location.href=\'sdfsf&menu=\'+jQuery(select_menu__0).val();" class="button button-primary">Sửa menu</a>';

        return $sContent. $btn;
    }
}
//boot class
if(is_admin()){
    /**
     * register metabox to page slug
     * @hook init
     */
    add_action('init','_hw_relatedpost_setting_init');
    function _hw_relatedpost_setting_init(){

        //if(class_exists('APF_hw_skin_Selector_hwskin')) new APF_hw_skin_Selector_hwskin('HW_NavMenu_Metabox_settings');
        new HW_RelatedPost_Metabox_settings(
            null,                                           // meta box id - passing null will make it auto generate
            __( 'Cài đặt cho YARPP ()', 'hwrp' ), // title
            //array( 'hw_sidebar_widgets_settings' =>  array( 'hw_sidebar_widgets_settings' ) ),    //syntax: {page slug}=>{tab slug}
            array('settings'=>array('hw_yarpp')),  //apply for this page slug
            'normal',                                         // context
            'core'                                       // priority
        );
    }

}
endif;
