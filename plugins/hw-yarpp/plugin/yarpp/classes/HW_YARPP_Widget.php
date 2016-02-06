<?php
/*
 * Vaguely based on code by MK Safi
 * http://msafi.com/fix-yet-another-related-posts-plugin-yarpp-widget-and-add-it-to-the-sidebar/
 */
class HW_YARPP_Widget extends WP_Widget {

	public function __construct() {
		parent::WP_Widget(false, 'Bài viết liên quan', array('description' => 'Hiển thị nội dung liên quan.'));
        wp_enqueue_style('hư-yarppWidgetCss', HW_YARPP_URL.'/style/widget.css');
	}

	public function widget($args, $instance) {
        if (!is_singular()) return;

		global $hw_yarpp;
		extract($args);

		/* Compatibility with pre-3.5 settings: */
		if (isset($instance['use_template'])) {
			$instance['template'] = ($instance['use_template']) ? ($instance['template_file']) : false;
        }

		if ($hw_yarpp->get_option('cross_relate')){
			$instance['post_type'] = $hw_yarpp->get_post_types();
        } else if (in_array(get_post_type(), $hw_yarpp->get_post_types())) {
			$instance['post_type'] = array(get_post_type());
        } else {
			$instance['post_type'] = array('post');
        }

		$title = apply_filters('widget_title', $instance['title']);
        $output = $before_widget;
        if ($instance['use_pro']) {
            if((isset($hw_yarpp->yarppPro['active']) && $hw_yarpp->yarppPro['active']) &&
               (isset($hw_yarpp->yarppPro['aid']) && isset($hw_yarpp->yarppPro['v']))  &&
               ($hw_yarpp->yarppPro['aid'] && $hw_yarpp->yarppPro['v'])) {

                $aid  = $hw_yarpp->yarppPro['aid'];
                $v    = $hw_yarpp->yarppPro['v'];
                $dpid = $instance['pro_dpid'];
                $ru   = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

                /* TODO: Put this on a template */
                $output .=
                    "\n".
                    '<div id="adkengage_ssp_div"></div>'.
                    '<script type="text/javascript" '.
                    'src="http://adkengage.com/pshandler.js?aid='.$aid.'&v='.$v.'&dpid='.$dpid.'&ru='.$ru.'">'.
                    '</script>'.
                    "\n";
            }
        } else {
            if (!$instance['template']) {
                $output .= $before_title;
                $output .= $title;
                $output .= $after_title;
            }
            $instance['domain'] = 'widget';
            $output .= $hw_yarpp->display_related(null, $instance, false);
        }

        $output .= $after_widget;
        echo $output;
	}

	public function update($new_instance, $old_instance) {
        $instance = array(
            'template'           => false,
            'title'              => $new_instance['title'],
            'thumbnails_heading' => $new_instance['thumbnails_heading'],
            'use_pro'            => (isset($new_instance['use_pro']))  ? $new_instance['use_pro']  : false,
            'pro_dpid'           => (isset($new_instance['pro_dpid'])) ? $new_instance['pro_dpid'] : null,
            'promote_yarpp'      => false,
        );

		if ($new_instance['use_template'] === 'thumbnails')   $instance['template'] = 'thumbnails';
        else if ($new_instance['use_template'] === 'custom' ) $instance['template'] = $new_instance['template_file'];
		
		return $instance;
	}

	public function form($instance) {
		global $hw_yarpp;
        $id = rtrim($this->get_field_id(null), '-');
		$instance = wp_parse_args(
            $instance,
            array(
                'title'                 => 'Liên quan',
                'thumbnails_heading'    => $hw_yarpp->get_option('thumbnails_heading'),
                'template'              => false,
                'use_pro'               => false,
                'pro_dpid'              => null,
                'promote_yarpp'         => false,
            )
        );
	
		/* TODO: Deprecate
		 * Compatibility with pre-3.5 settings
		 */
		if (isset($instance['use_template'])) $instance['template'] = $instance['template_file'];
	
		$choice = ($instance['template']) ? (($instance['template'] === 'thumbnails') ? 'thumbnails' : 'custom') : 'builtin';

		/* Check if YARPP templates are installed */
		$templates = $hw_yarpp->get_templates();

		if (!$hw_yarpp->diagnostic_custom_templates() && $choice === 'custom') $choice = 'builtin';

		include(HW_YARPP_DIR.'/includes/phtmls/hw_yarpp_widget_form.phtml');
	}
}

/**
 * @since 2.0 Add as a widget
 */
function hw_yarpp_widget_init() {
    register_widget('HW_YARPP_Widget');
}

//add_action('widgets_init', 'hw_yarpp_widget_init');
add_action('hw_widgets_init', 'hw_yarpp_widget_init');