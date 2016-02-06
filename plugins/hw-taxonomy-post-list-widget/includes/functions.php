<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Function hw_cplw_widget_shortcode_output() is called in file shortcode.php to display data.
 * @param $category is used to show posts from particular category.
 * @param $height is the height of posts list content area.
 * @param $width is the width of posts list content area.
 * @param $posts_to_show is number of posts to show.
 * @param $orderby is to display posts by order of title, date etc.
 * @param $order is to display posts by order. 
 * @param $excerpt_length is the length of content to display.
 * @param $thumbnail_width is the thumbnail width.
 * @param $thumbnail_height is the thumbnail height.
 * @param $date_format is the date format.
*/ 
function hw_cplw_widget_shortcode_output($category, $height, $width, $posts_to_show, $orderby, $order, $display, $excerpt_length, $thumbnail_width, $thumbnail_height, $date_format){     
    global $post, $wpdb;     
  
    $sort_by = isset($orderby) ? $orderby : 'date'; 
    $sort_order = isset($order) ? $order : 'DESC';   
   
    $cat_posts = new WP_Query(
        "showposts=" . $posts_to_show . 
        "&cat= ". $category  .
        "&orderby=" . $sort_by .
        "&order=" . $sort_order
    ); 

    // Excerpt length 
    $new_excerpt_length = create_function('$length', "return " . $excerpt_length . ";");
    if ( $excerpt_length > 0 )
        add_filter('excerpt_length', $new_excerpt_length);
    $arrExlpodeFields = explode(',',$display);
   
    $arrExlpodeFields = explode(',',$display);
    ?>
        <div class="post_content" style="height:<?php echo $height; ?>px !important width:<?php echo $width; ?>px !important">
            <div class="ovflhidden post_scroll">                
                <?php while ( $cat_posts->have_posts() )
                    {
                        $cat_posts->the_post(); ?>                      
                            <div class="fl newsdesc">                              
                                <?php
                                if (
                                        function_exists('the_post_thumbnail') &&
                                        current_theme_supports("post-thumbnails") &&
                                        in_array("thumb",$arrExlpodeFields) &&
                                        has_post_thumbnail()
                                    ) :

                                ?>
                                        <div class="post_thumbnail">
                                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                                <?php the_post_thumbnail( array($thumbnail_width,$thumbnail_height )); ?>
                                            </a>
                                        </div>
                                <?php   
                                endif;
                                ?>
                                <div class="post_data">
                                    <!-- Post title -->
                                    <h2><a class="post-title" href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent link to <?php the_title_attribute(); ?>"><?php echo get_the_title(); ?></a></h2>
                                    <?php 
                                    if ( in_array("date",$arrExlpodeFields) ) : ?>
                                         <!-- post Date -->
                                        <p class="post_date" ><?php echo the_time($date_format); ?></p>
                                    <?php 
                                    endif; 
                                    if ( in_array("author",$arrExlpodeFields) ) : ?>
                                        <p class="post_author" ><?php  echo "by " ;?><a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php the_author(); ?></a></p>
                                    <?php 
                                    endif; 

                                    if ( in_array("excerpt",$arrExlpodeFields) ) :
                                        the_excerpt(); 
                                    endif; 
                                    if ( in_array("comment_num",$arrExlpodeFields) ) : ?>
                                        <p class="comment-num"><a href="<?php comments_link(); ?>">(<?php comments_number(); ?>)</a></p>
                                    <?php 
                                    endif; 
                                    ?>
                                </div>
                            </div>                                              
                        <?php   
                    } 
                    wp_reset_postdata();               
                ?>
            </div>
        </div>
    <?php 
    #add_action('widgets_init', create_function('', 'return register_widget("HW_Taxonomy_Post_List_widget");'));
    add_action('hw_widgets_init', create_function('', 'return register_widget("HW_Taxonomy_Post_List_widget");'));
}

/**
 * return HTML for select tag of terms list from taxonomy
 * @param $tax
 * @param $focus
 * @param array $atts
 * @param array $args
 * @return string|void
 */
function hw_get_terms_list($tax,$focus,$atts = array(),$args=array()){
	if(!$tax) return;
    HW_HOANGWEB::load_class('HW_UI_Component');

	if(is_array($args)) $args=array();
	$args['order'] = 'ASC';
	$args['fields'] = 'all';
	$args['hide_empty'] = 0;
	//build atts
	$attrs = HW_UI_Component::generateAttributes($atts);
	/*foreach($atts as $att => $v){
		$a.=$att.'="'.$v.'" ';
	}*/
	$out = __('Rỗng');
	$terms_data = get_terms($tax,$args);
    if(is_wp_error($terms_data)) {
        return '';
    }
	if(count($terms_data)){
	    $out= '<select '.trim($attrs).'>';
        $out .= '<option value="">----All----</option>';
        foreach($terms_data as $item){
            if(!isset($item->slug)) continue;
            if($item->slug == $focus);
            $out.= '<option '.selected( $item->slug, $focus, false ).' value="'.$item->slug.'">'.$item->name.'</option>';
        }
        $out.= '</select>';
	}
	return $out;
}


/**
 * encrypt string
 * @param string $encrypt: string to encrypt
 * @param string $key: key
 */
function hwtpl_mc_encrypt($encrypt, $key){
    $encrypt = serialize($encrypt);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    $key = pack('H*', $key);
    $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
    $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
    $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
    return $encoded;
}

/**
 * Decrypt Function
 * @param string $decrypt: encrypted string to be decrypt
 * @param string $key: key
 */
function hwtpl_mc_decrypt($decrypt, $key){
    $decrypt = explode('|', $decrypt.'|');
    $decoded = base64_decode($decrypt[0]);
    $iv = base64_decode($decrypt[1]);
    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
    $key = pack('H*', $key);
    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    $mac = substr($decrypted, -64);
    $decrypted = substr($decrypted, 0, -64);
    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    if($calcmac!==$mac){ return false; }
    $decrypted = unserialize($decrypted);
    return $decrypted;
}

/**
 * limit string
 * @param $str
 * @param int $limit
 * @return string
 */
function hwtpl_limit_str($str, $limit = 100){
    if(strlen($str)<=$limit) return $str;
    return mb_substr($str,0,$limit-3,'UTF-8').'...';
}