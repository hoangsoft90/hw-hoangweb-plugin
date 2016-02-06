<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 23/05/2015
 * Time: 20:29
 */
//Add custom nav menu meta box
function mynav_add_custom_box() {
    add_meta_box(
        'add-mynav',
        'My Menu Choices',
        'mynav_show_custom_box',
        'nav-menus',
        'side',
        'default');
}
add_action( 'admin_init', 'mynav_add_custom_box' );

//display nav menu meta box, copy of wp_nav_menu_item_link_meta_box()
function mynav_show_custom_box() {
    global $_nav_menu_placeholder, $nav_menu_selected_id;
    $locations = get_nav_menu_locations();
    //_print($locations);

    $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

    ?>
    <div class="customlinkdiv" id="customlinkdiv">
        <input type="hidden" value="custom" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" />
        <p id="menu-item-url-wrap">
            <label class="howto" for="custom-menu-item-url">
                <span><?php _e('URL'); ?></span>
                <input id="custom-menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" type="text" class="code menu-item-textbox" value="http://" />
            </label>
        </p>

        <p id="menu-item-name-wrap">
            <label class="howto" for="custom-menu-item-name">
                <span><?php _e( 'Link Text' ); ?></span>
                <input id="custom-menu-item-name" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" type="text" class="regular-text menu-item-textbox input-with-default-title" title="<?php esc_attr_e('Menu Item'); ?>" />
            </label>
        </p>

        <p class="button-controls">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-custom-menu-item" id="submit-customlinkdiv" />
				<span class="spinner"></span>
			</span>
        </p>

    </div><!-- /.customlinkdiv -->
<?php
}

//draft
/**
 * Class HW_menu
 */
if(class_exists('AdminPageFramework_MetaBox_Page')):
class HW_menu extends AdminPageFramework_MetaBox_Page {
    public function setUp() {

        $this->addSettingFields(
            array(
                'field_id' => self::create_fieldname4sidebar('enable_override_sidebar'),
                'type'=>'checkbox',
                #'value' => '#fff',
                'title' => 'Kích hoạt tùy chỉnh sidebar',
                'label' => 'Kích hoạt cho phép tùy chỉnh sidebar với các cài đặt dưới.',

            ),
            array(
                'type' => 'submit',
                'field_id'      => 'submit_button',
                'show_title_column' => false,     #hidden button title column mean align button to left bellow field label, see image in bellow:
                'label' => 'Lưu lại'
            )
        );

    }
}
/*new HW_menu(
    null,                                           // meta box id - passing null will make it auto generate
    __( 'Cài đặt cho sidebar ', 'hw-widget-settings-apf' ), // title
    //array( 'hw_sidebar_widgets_settings' =>  array( 'hw_sidebar_widgets_settings' ) ),    //syntax: {page slug}=>{tab slug}
    array('nav-menus'),  //apply for this page slug
    'side',                                         // context
    'default'                                       // priority
);*/
endif;
class HW_NAVMENU_Admin {
    /**
     * constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, '_admin_enqueue_scripts'));
        //triggered when WordPress calls all the scripts needed by the media buttons.
        add_action('wp_enqueue_media', array($this, '_include_media_button_js_file'));
    }
    /**
     * admin enqueue scripts
     */
    public function  _admin_enqueue_scripts(){
        if(class_exists('HW_HOANGWEB') && HW_HOANGWEB::is_current_screen(array('hw_navmenu_settings', 'nav-menus') )){
            wp_enqueue_media();

            wp_enqueue_style('hw-menu-admin-style', HW_MENU_URL.'/css/hw-menu-style.css');
            wp_enqueue_script('hw-menu-js', HW_MENU_URL.'/js/hw-menu-js.js', array('jquery'));
            wp_localize_script('hw-menu-js', '__hw_navmenu', array());
            //should do after localize script
            wp_enqueue_script('hw-menu-item-custom-field', HW_MENU_URL.'/js/hw-menu-item-custom-field.js', array('hw-menu-js'));
        }

    }
    function _include_media_button_js_file() {
        //wp_enqueue_script('media_button', 'path/to/media_button.js', array('jquery'), '1.0', true);

    }
    /**
     * init
     */
    public static function init() {
        new HW_NAVMENU_Admin();
    }
}
add_action('wp_loaded', array('HW_NAVMENU_Admin', 'init'));
