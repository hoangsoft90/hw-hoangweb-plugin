<?php
/**
 * Module Name: Ecommerce Product (WooCommerce)
 * Module URI:
 * Description:
 * Version: 1.0
 * Author URI: http://hoangweb.com
 * Author: Hoangweb
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

include ('includes/functions.php');
HW_HOANGWEB::register_class('HW_Product', dirname(__FILE__). '/class-product-type.php');
HW_HOANGWEB::register_class('HW_Cloudzoom', dirname(__FILE__) . '/includes/class-ui-cloudzoom.php');

/**
 * Class HW_Module_product
 */
class HW_Module_product extends HW_Module {
    /**
     * attributes
     * @var array
     */
    private $attrs = array();

    /**
     * class construct method
     */
    public function __construct() {

        //load twig context
        include_once('includes/hw-woo-timberProduct.php');
        include_once('includes/hw-woocommerce.php');
        include_once('includes/hw-woo-customer.php');

        if(function_exists('hw_load_class')) hw_load_class('HW_Product');
        hw_load_class('HW_Cloudzoom');   //HW_HOANGWEB::load_class

        $this->enable_tab_settings();
        $this->enable_submit_button();

        //common product attributes
        $this->attrs = array(
            "price"=> __("Giá"),
            "sale_price" => __("Giá khuyến mại"),
            "status"=> __('Tình trạng')
        );
        if(class_exists('HW_Product')) {
            HW_Product::register('product');
            HW_Product::get('product')->set_attributes($this->attrs);
        }
        //add_action('wp_footer', array($this, 'execute_cloudzoom'));
        /*Setting*/
        add_action( 'modules_loaded', array( $this, '_woo_load_textdomain' ) );
    }

    /**
     * @hook modules_loaded
     */
    function _woo_load_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce' );

        // Admin Locale
        if ( is_admin() ) {
            load_textdomain( 'woocommerce', plugins_url( "languages/woocommerce-$locale.mo", __FILE__ ));
        }

        // Global + Frontend Locale
        load_textdomain( 'woocommerce', plugins_url( "languages/woocommerce-$locale.mo", __FILE__ ) );
        load_plugin_textdomain( 'woocommerce', false, plugins_url( "languages/", __FILE__ ) );
    }

    /**
     * after module loaded
     * @return mixed|void
     */
    public function module_loaded() {
        $this->register_help('product', 'class-product-type.txt');
    }
    /**
     * @hook wp_enqueue_scripts
     */
    public function enqueue_scripts() {
        if(!is_admin()) {
            $this->enqueue_style('asset/custom-cloudzoom.css');
            //load cloudzoom lib
            HW_Libraries::enqueue_jquery_libs('cloudzoom');
        }
    }

    /**
     * @hook admin_enqueue_scripts
     */
    public function admin_enqueue_scripts() {

    }

    /**
     * @hook wp_footer
     */
    function print_footer() {
        if(is_product()) {
            $setting = self::get()->get_values();
            $thumbnailsContainer = isset($setting['smallthumb_container'])? $setting['smallthumb_container']: '.product .thumbnails';
            $productImages = isset($setting['productImages'])? $setting['productImages']: '.product .images > a';
            $mouseEvent = isset($setting['mouseEvent'])? $setting['mouseEvent']: 'mouseover';
            //css generator
            $css = array(
                $thumbnailsContainer.' img' => array('width' => get_option('woocommerce_thumbnail_image_width').'px'),
                '.cloud-zoom-big' => array()
            );

            if(!empty($setting['zoombig_width'])) {
                $css['.cloud-zoom-big']['width'] = HW_Validation::format_unit($setting['zoombig_width']) .' !important';
            }
            if(!empty($setting['zoombig_height'])) {
                $css['.cloud-zoom-big']['height'] = HW_Validation::format_unit($setting['zoombig_height']).' !important';
            }
            echo HW_UI_Component::generateCSS($css);
            ?>

            <script type="text/javascript">
                jQuery(document).ready(function($){
                    var $thumbnailsContainer, $thumbnails,$productImages, addCloudZoom;
                    $('a.zoom').unbind('click.fb');
                    $thumbnailsContainer = $('<?php echo $thumbnailsContainer;?>');
                    $thumbnails = $('a', $thumbnailsContainer);

                    $productImages = $('<?php echo $productImages;?>');
                    addCloudZoom = function(el){

                        el.addClass('cloud-zoom').CloudZoom();

                    }

                    if($thumbnails.length){
                        <?php if($mouseEvent == 'click') {
                            echo '$thumbnails.unbind(\'click\');';
                        }
                        ?>

                        $thumbnails.bind('<?php echo $mouseEvent; ?>',function(){
                            var $image = $(this).clone(false);
                            $image.insertAfter($productImages);
                            $productImages.remove();
                            $productImages = $image;
                            $('.mousetrap').remove();
                            addCloudZoom($productImages);

                            return false;

                        })

                    }
                    addCloudZoom($productImages);

                });
            </script>
<?php
        }
    }
    /**
     * Triggered when the tab is loaded.
     */
    public function replyToAddFormElements($oAdminPage) {
        $cz = $this->add_tab(array(
            'id'=>'cloudzoom',
            'title' => 'Cloudzoom',
            'description' => 'Cloudzoom settings.'
        ));
        $cz->addFields(
            array(
                'field_id' => ('images_from_current_post'),
                'type' => 'checkbox',
                'title' => 'Lấy các ảnh trong post hiện tại.'
            ),
            array(
                'field_id' => 'zoombig_width',
                'type' => 'text',
                'title' => 'Zoom Big width (px)'
            ),
            array(
                'field_id' => 'zoombig_height',
                'type' => 'text',
                'title' => 'Zoom Big Height (px)'
            ),
            array(
                'field_id'          => ('container_id'),
                'type'              => 'text',
                'title' => 'container_id'
            ),
            array(
                'field_id'          => ('container_class'),
                'type'              => 'text',
                'label_min_width'   => '100%',
                'title' => 'container_class'
            ),
            array(
                'field_id' => 'productImages',
                'type' => 'text',
                'title' => 'productImages'
            ),
            array(
                'field_id' => ('thumb_anchor_class'),
                'type' => 'text',
                'title' => 'thumb_anchor_class',
            ),
            array(
                'field_id' => ('thumb_img_class'),
                'type' => 'text',
                'title' => 'thumb_img_class',
            ),
            array(
                'field_id' => ('smallthumb_container'),
                'type' => 'text',
                'title' => 'smallthumb_container',
            ),
            array(
                'field_id' => ('smallthumb_class'),
                'type' => 'text',
                'title' => 'smallthumb_class',
            ),
            array(
                'field_id' => ('smallthumb_item_class'),
                'type' => 'text',
                'title' => 'smallthumb_item_class'
            ),
            array(
                'field_id' => ('smallthumb_anchor_class'),
                'type' => 'text',
                'title' => 'smallthumb_anchor_class'
            ),
            array(
                'field_id' => ('smallthumb_img_class'),
                'type' => 'text',
                'title' => 'smallthumb_img_class'
            ),
            array(
                'field_id' => ('mouseEvent'),
                'type' => 'select',
                'label'=> array('hover'=> 'hover', 'click'=> 'click'),
                'title' => 'MouseEvent'
            )
        );
    }
    /**
     * validation form fields
     * @param $values
     * @return mixed
     */
    public function validation_tab_filter($values) {
        return $values;
    }
}
HW_Module_product::register();