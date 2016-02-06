<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 22/11/2015
 * Time: 22:50
 */
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Class HW_WP_User
 */
class HW_WP_User extends HW_Admin_Options{
    /**
     * @var array
     */
    var $user_meta = array(
        'user_login', 'user_nicename', 'user_url', 'user_email', 'display_name',
        'nickname', 'first_name', 'last_name', 'description', 'role', 'show_admin_bar_front','user_pass'
    );
    /**
     * @return mixed|void
     */
    public function load() {
        //Change admin bar to default:off.
        add_action("user_register", array($this, "_set_user_admin_bar_false_by_default"), 10, 1);
        add_filter( 'show_admin_bar', '__return_false' );   #you also use this

        //custom user profile
        add_action('show_user_profile', array(&$this, '_show_extra_profile_fields'));
        add_action('edit_user_profile', array(&$this, '_show_extra_profile_fields'));

        //update user profile
        add_action('personal_options_update', array(&$this, '_save_extra_profile_fields'));
        add_action('edit_user_profile_update', array(&$this, '_save_extra_profile_fields'));
    }

    /**
     * Change admin bar to default:off.
     * @param $user_id
     */
    public function _set_user_admin_bar_false_by_default($user_id) {
        update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
        update_user_meta( $user_id, 'show_admin_bar_admin', 'false' );
    }
    /**
     * save user meta
     * @param $user_id
     * @return bool
     */
    public function _save_extra_profile_fields($user_id) {
        if (!current_user_can('edit_user', $user_id))
            return false;
        update_usermeta($user_id, 'phone', $_POST['phone']);    //update user profile
    }
    /**
     * @param $user
     * @hook show_user_profile,edit_user_profile
     */
    public function _show_extra_profile_fields($user)
    {
        $phone=get_the_author_meta('phone',$user->ID);
        ?>
        <table class="form-table">
            <tr>
                <td>SĐT:</td>
                <td><input type="text" name="phone" value="<?php echo $phone?>"/></td>
            </tr>
        </table>
    <?php
    }

    /**
     * update data for current/specific user
     * @param $meta
     */
    public function update_user($meta) {
        $meta = array_filter(array_intersect_key($meta, array_flip($this->user_meta)) );
        if(!isset($meta['ID'])) $meta['ID'] = get_current_user_id();
        if(isset($meta['ID'])) wp_update_user($meta);
    }
    public static function __init() {


    }
}