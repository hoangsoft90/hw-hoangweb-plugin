<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 05/11/2015
 * Time: 15:47
 */
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;


/**
 * Class HW_CLI_HW_WPCF7
 */
class HW_CLI_HW_WPCF7 extends HW_CLI_Command {
    /**
     * enable sharing
     * @param $args
     * @param $assoc_args
     */
    public function settings($args, $assoc_args) {
        $this->do_import();
        WP_CLI::success( ' config hwcf7 setting page successful.' );
    }

    /**
     * create new contact form
     * @param $args
     * @param $assoc_args
     */
    public function create_form($args, $assoc_args) {
        $this->do_import();
        $form_slug ='contact-form-1';
        $page = 'lien-he';
        $page = get_page_by_path($page);
        //get form
        $cf = HW_POST::get_post_by_name($form_slug, 'wpcf7_contact_form');
        if(is_wp_error($cf)) {
            WP_CLI::error('Not found contact form with name: '.$form_slug);
            return;
        }

        $form_id = $cf->ID;

        //update contact page with contact form 7 inserting
        $contact_page = array(
            'post_type' => 'page',
            'post_content' => '[contact-form-7 id="'.$form_id.'"]'
        );
        if(!is_wp_error($page)) $contact_page['ID'] = $page->ID;
        wp_update_post($contact_page);
        WP_CLI::success( ' Create new contact form 7 successful.' );
    }

    /**
     * remove all forms
     * @param $args
     * @param $assoc_args
     */
    public function delete_all_forms($args, $assoc_args) {
        $forms = get_posts('post_type=wpcf7_contact_form&showposts=-1');
        foreach($forms as $post) {
            wp_delete_post($post->ID);
        }
        WP_CLI::success( ' Deleted all contact form 7.' );
    }
}
