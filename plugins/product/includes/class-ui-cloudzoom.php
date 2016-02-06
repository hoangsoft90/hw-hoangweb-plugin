<?php
/**
 * @Class HW_Cloudzoom
 */
if(class_exists('HW_UI_Component', false)):
class HW_Cloudzoom extends HW_UI_Component{
    /**
     * @var
     */
    static $instance = null;

    /**
     * @var null
     */
    private $images= array();
    /**
     * @var null
     */
    private $cloudzoom_id=null;


    /**
     * construct method
     * @param $args
     * @param $options
     */
    public function __construct($args = array(), $options = array()) {
        parent::__construct($args, $options);

        //load cloudzoom lib
        HW_Libraries::enqueue_jquery_libs('cloudzoom');

        $this->cloudzoom_id = HW_String::generateRandomString();
        //valid
        if(!is_array($args)) $args = array();

        //params

        $default_params = array(
            'images_from_current_post' => '0',
            'zoombig_width' => '300px',
            'zoombig_height' => '',
            'container_id' => 'cloudzoom-container',
            'container_class' => '',
            'thumb_anchor_class' => 'cloudzoom-thumb-anchor-class',
            'thumb_img_class' => 'cloudzoom-thumb-img-class',

            'smallthumb_container_class' => 'cloudzoom-smallthumb-container',
            'smallthumb_class'=> 'cloudzoom-smallthumb-class',
            'smallthumb_item_class' => 'cloudzoom-smallthumb-item-class',
            'smallthumb_anchor_class' => 'cloudzoom-smallthumb-anchor-class',
            'smallthumb_img_class'=> 'cloudzoom-smallthumb-img-class'
        );
        if(class_exists('HW_Module_Cloudzoom') && HW_Module_Cloudzoom::is_active()) {
            $setting = HW_Module_Cloudzoom::get();
            $params['images_from_current_post'] = $setting->get_field_value('images_from_current_post');
            $params['zoombig_width'] = $setting->get_field_value('zoombig_width');
            $params['zoombig_height'] = $setting->get_field_value('zoombig_height');

            $params['container_id'] = $setting->get_field_value('container_id');
            $params['container_class'] = $setting->get_field_value('container_class');
            $params['thumb_anchor_class'] = $setting->get_field_value('thumb_anchor_class');
            $params['thumb_img_class'] = $setting->get_field_value('thumb_img_class');
            $params['smallthumb_container_class'] = $setting->get_field_value('smallthumb_container_class');
            $params['smallthumb_class'] = $setting->get_field_value('smallthumb_class');
            $params['smallthumb_item_class'] = $setting->get_field_value('smallthumb_item_class');
            $params['smallthumb_anchor_class'] = $setting->get_field_value('smallthumb_anchor_class');
            $params['smallthumb_img_class'] = $setting->get_field_value('smallthumb_img_class');

        }
        $this->set_params ($default_params);
        if(is_array($args)) {
            $this->set_params ( $args);
        }
        //cloud options
        if(is_array($options)) $this->set_options( $options);
    }

    /**
     * @return HW_Cloudzoom
     */
    public static function init() {
        if(empty(self::$instance)) self::$instance = new self();
        return self::$instance ;
    }

    /**
     * load images data
     * @param $images contain ids or posts data
     */
    public function load_images($images='') {
        if(is_string($images)) $images = preg_split('#[\d,]+#', $images);

        if(is_array($images)) {
            foreach($images as $img) {
                //get post data
                if(is_numeric($img)) $img = get_post($img);
                if(!is_object($img)) continue;

                //validation

                $this->images[]= $img;
            }
        }

    }

    /**
     * return images data
     * @return null
     */
    public function get_images() {
        return $this->images;
    }
    /**
     * load images from post
     * @param string $post_id
     */
    public function load_images_from_post($post_id='') {
        global $post;
        if(!$post_id && is_single()) {
            $post_id = $post->ID;
        }
        //valid
        if(!is_numeric($post_id)) return ;

        $images =& get_children( array (
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'post_mime_type' => 'image'
        ));

        /*if ( empty($images) ) {
            // no attachments here
        } else {
            foreach ( $images as $attachment_id => $attachment ) {
                echo wp_get_attachment_image( $attachment_id, 'thumbnail' );
            }
        }*/
        $this->load_images($images);
    }
    /**
     * display thumbnail image
     */
    public function display_thumbImage() {
        extract($this->get_params());
        //get all images
        $images = $this->get_images();

        //display first image
        if(is_array($images)) $img = reset($images);
        if(!empty($img) && is_object($img)) {
            #$thumb_small=wp_get_attachment_image_src($img->ID,'thumbnail');//_print($thumb_small);//array('90','90')
            $thumb=wp_get_attachment_image_src($img->ID,'medium');

            echo '<a class="cloud-zoom '.$thumb_anchor_class.'" href="'.$img->guid.'" itemprop="image" id="'.$this->cloudzoom_id.'" title="'.$img->post_title.'" rel="position:\'right\', adjustX:20, adjustY:-3, tint:\'#FFFFFF\', softFocus:1, smoothMove:5, tintOpacity:0.8" data-rel="prettyPhoto[product-gallery]">
            <img src="'. $thumb[0] .'" class="attachment-shop_single wp-post-image '.$thumb_img_class
                .'" alt="'.$img->post_title . '" title="'.$img->post_title .'"  />
            </a>
            <span class="view-larger-image"></span>';
        }
    }

    /**
     * display slideshow images
     */
    public function display_small_images() {
        extract($this->get_params());
        //get all images
        $images = $this->get_images();
        $setting = $this->get_params();

        $out = '<div id="hw-product-slideShow" class="hw-ImageCarouselBox" style="margin: 10px auto 0;">';
        //slideshow small images
        $out .= '<div class="'.$smallthumb_container_class.' listImages">';
        if(is_array($images) && count($images)){
            $out .= '<ul class="'.$smallthumb_class.'">';
            foreach($images as $img){
                $thumb_small=wp_get_attachment_image_src($img->ID,'thumbnail');//_print($thumb_small);//array('90','90')
                $thumb=wp_get_attachment_image_src($img->ID,'medium');

                $out .= sprintf("<li class='%s'><a  href='%s' class='cloud-zoom-gallery %s' title='%s' rel='useZoom: \"{$this->cloudzoom_id}\", smallImage: \"%s\"'><img  src='%s' class='%s' alt='%s' /></a></li>", $smallthumb_item_class, $img->guid, $smallthumb_anchor_class,$img->post_title, $thumb[0], $thumb_small[0], $smallthumb_img_class, $img->post_title);
            }
            $out .= '</ul>';
        }
        $out .= '</div>';

        $out .= '</div>';
        $out .= "<script>jQuery(function(){jQuery('#".$this->cloudzoom_id."').CloudZoom();});</script>";
        //css
        $css = array('.cloud-zoom-big' => array());
        if(!empty($setting['zoombig_width'])) {
            $css['.cloud-zoom-big']['width'] = HW_Validation::format_unit($setting['zoombig_width']) .' !important';
        }
        if(!empty($setting['zoombig_height'])) {
            $css['.cloud-zoom-big']['height'] = HW_Validation::format_unit($setting['zoombig_height']).' !important';
        }

        $out .= $this->generateCSS($css);
        echo $out;
    }

    /**
     * output HTML
     */
    public function display() {
        echo '<div class="hw-cloudzoom-container" style="overflow: visible;">';
        //thumb image
        echo '<div class="ProductThumbImage" style="width: 220px; height: 220px; text-align:center; overflow:visible;">';
        $this->display_thumbImage();
        echo '</div>';

        //display small images
        echo '<div class="small-images">';
        $this->display_thumbImage();
        echo '</div>';

        echo '</div>';
    }
}
endif;