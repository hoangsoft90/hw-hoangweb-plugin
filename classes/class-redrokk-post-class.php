<?php 
/**
 * @Author	Anonymous
 * @link http://www.redrokk.com
 * @Package Wordpress
 * @SubPackage RedRokk Library
 * @copyright  Copyright (C) 2011+ Redrokk Interactive Media
 * 
 * @version 2.0
 */
defined('ABSPATH') or die('You\'re not supposed to be here.');
 
/**
 * 
 * 
 * @author Anonymous
 * @example

$events = redrokk_post_class::getInstance('events', array(
	'_single'	=> 'Event',
	'_plural'	=> 'Events',
	'_columns'	=> array(
		array('id'=>'cb', 'title'=> ''),
		array('id'=>'title', 'title'=> 'Title'),
		array(
			'id'=>'location', 
			'title'=> 'Location',
			'callback' => 'redrokk_location_column'
		),
		array(
			'id'=>'date_and_time', 
			'title'=> 'Date and Time',
			'callback'		=> 'redrokk_datetime_column'
		),
		array('id'=>'author', 'title'=> 'Owner'),
	)
));

 */
if (!class_exists('redrokk_post_class')):
class redrokk_post_class
{
	var $_post_type;
	var $_single;
	var $_plural;
	
	/**
	 * (optional) Meta argument used to define default values for 
	 * publicly_queriable, show_ui, show_in_nav_menus and exclude_from_search.
	 * Default: false
	 * 
	 * 'false' - do not display a user-interface for this post type (show_ui=false), 
	 * post_type queries can not be performed from the front end (publicly_queryable=false), 
	 * exclude posts with this post type from search results (exclude_from_search=true), 
	 * hide post_type for selection in navigation menus (show_in_nav_menus=false)
	 * 
	 * 'true' - show_ui=true, publicly_queryable=true, exclude_from_search=false, 
	 * show_in_menu=true
	 * 
	 * @var boolean
	 */
	var $public = true;
	
	/**
	 * (optional) Whether post_type queries can be performed from the front end.
	 * 
	 * @var boolean
	 */
	var $publicly_queryable = true;
	
	/**
	 * (importance) Whether to exclude posts with this post type from search results.
	 * 
	 * @var boolean
	 */
	var $exclude_from_search;
	
	/**
	 * (optional) Whether to generate a default UI for managing this post type. Note that 
	 * _built-in post types, such as post and page, are intentionally set to false.
	 * Default: value of public argument
	 *  
	 * 'false' - do not display a user-interface for this post type
	 * 'true' - display a user-interface (admin panel) for this post type
	 *  
	 * @var boolean
	 */
	var $show_ui = true;
	
	/**
	 * (optional) Whether to show the post type in the admin menu and 
	 * where to show that menu. Note that show_ui must be true.
	 * 
	 * 'false' - do not display in the admin menu
	 * 'true' - display as a top level menu
	 * 'some string' - a top level page like 'tools.php' or 'edit.php?post_type=page'
	 * 
	 * Note: When using 'some string' to show as a submenu of a menu page created by 
	 * a plugin, this item will become the first submenu item, and replace the location 
	 * of the top level link. If this isn't desired, the plugin that creates the menu 
	 * page needs to set the add_action priority for admin_menu to 9 or lower.
	 * 
	 * @var boolean|string
	 */
	var $show_in_menu = true;
	var $show_in_nav_menus = true;
	
	/**
	 * (optional) The url to the icon to be used for this menu.
	 * Default: null - defaults to the posts icon
	 * 
	 * @var string
	 */
	var $menu_icon;
	
	/**
	 * (optional) The string to use to build the read, edit, 
	 * and delete capabilities. May be passed as an array to allow for alternative 
	 * plurals when using this argument as a base to construct the capabilities, 
	 * e.g. array('story', 'stories'). By default the capability_type is used as a 
	 * base to construct capabilities. It seems that `map_meta_cap` needs to be set 
	 * to true, to make this work.
	 * Default: "post"
	 * 
	 * @var string|array
	 */
	var $capability_type = 'post';
	
	/**
	 * @see http://codex.wordpress.org/Function_Reference/register_post_type
	 * @var array
	 */
	var $capabilities = array();
	
	/**
	 * (optional) False to prevent queries, or string value of the query var to 
	 * use for this post type.
	 * 
	 * @var boolean|string
	 */
	var $query_var = true;
	
	/**
	 * (optional) Whether the post type is hierarchical. Allows Parent to be specified.
	 * 
	 * @var boolean
	 */
	var $hierarchical = false;
	
	/**
	 * (optional) The position in the menu order the post type should appear.
	 * Default: null - defaults to below Comments
	 * 
	 * 5 - below Posts
	 * 10 - below Media
	 * 15 - below Links
	 * 20 - below Pages
	 * 25 - below comments
	 * 60 - below first separator
	 * 65 - below Plugins
	 * 70 - below Users
	 * 75 - below Tools
	 * 80 - below Settings
	 * 100 - below second separator
	 * 
	 * @var integer
	 */
	var $menu_position;
	
	/**
	 * (optional) An alias for calling add_post_type_support() directly.
	 * Default: title and editor
	 * 
	 * 'title'
	 * 'editor' (content)
	 * 'author'
	 * 'thumbnail' (featured image, current theme must also support post-thumbnails)
	 * 'excerpt'
	 * 'trackbacks'
	 * 'custom-fields'
	 * 'comments' (also will see comment count balloon on edit screen)
	 * 'revisions' (will store revisions)
	 * 'page-attributes' (menu order, hierarchical must be true to show Parent option)
	 * 'post-formats' add post formats, @see http://codex.wordpress.org/Post_Formats
	 * 
	 * @var array
	 */
	var $supports = array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' );
	
	/**
	 * A short descriptive summary of what the post type is.
	 * 
	 * @var unknown_type
	 */
	var $description;
	
	/**
	 * Contains the help message to display to the administrator
	 * 
	 * @var string
	 */
	var $_help;
	var $_help_edit;
	
	/**
	 * 
	 * @var string
	 */
	var $_query_hash = null;

	/**
	 * 
	 * @var unknown_type
	 */
	var $get_archives_where_r;
	
	/**
	 * 
	 * @var string
	 */
	var $default_structure = '/%postname%/';
	
	/**
	 * The labels to display to the administrators
	 * 
	 * @var array
	 */
	var $_labels = array(
		'name' 				=> '%2$s',
		'singular_name' 	=> '%1$s',
		'add_new' 			=> 'Add New',
		'add_new_item' 		=> 'Add New %2$s',
		'edit_item' 		=> 'Edit %1$s',
		'new_item' 			=> 'New %1$s',
		'all_items' 		=> 'All %2$s',
		'view_item' 		=> 'View %1$s',
		'search_items' 		=> 'Search %2$s',
		'not_found' 		=> 'No %2$s found',
		'not_found_in_trash'=> 'No %2$s found in Trash', 
		'parent_item_colon' => '',
		'menu_name' 		=> '%2$s'
	);
	
	/**
	 * Contains the messages to display to the administrator upon
	 * confirmation of actions
	 * 
	 * @var array
	 */
	var $_messages = array(
		'', // Unused. Messages start at index 1.
		'%1$s updated. <a href="%2$s">View %1$s</a>',
		'Custom field updated.',
		'Custom field deleted.',
		'%1$s updated.',
		'%1$s restored to revision from %2$s',
		'%1$s published. <a href="%2$s">View %1$s</a>',
		'%1$s saved.',
		'%1$s submitted. <a target="_blank" href="%2$s">Preview %1$s</a>',
		'%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %1$s</a>',
		'%1$s draft updated. <a target="_blank" href="%2$s">Preview %1$s</a>',
	);
	
	/**
	 * Constructor.
	 *
	 */
	function __construct( $options = array() )
	{
		//initializing
		$this->setProperties($options);
		if (!$this->_single) {
			$this->_single = ucfirst($this->_post_type);
		}
		if (!$this->_plural) {
			$this->_plural = ucfirst($this->_post_type).'s';
		}
		
		//registration actions
		add_action( 'init', array($this, '_register_post_type') );
		add_action( 'contextual_help', array($this, '_help_text'), 10, 3 );
		add_action( 'update_option_recently_activated', array($this, '_rewrite_flush') );
		add_action( 'pre_get_posts', array($this, 'pre_get_posts'), 20, 1 );
		add_action( "manage_{$this->_post_type}_posts_custom_column", array($this, 'table_cells'), 20, 2 );
		add_action( 'loop_start', array($this, 'loop_start') );
		add_action( 'loop_end', array($this, 'loop_end') );
		
		add_action( 'wp_ajax_redrokk-add-post-type-archive-links', array( $this, 'ajax_add_post_type'));
		add_action( 'wp_loaded', array($this,'set_archive_rewrite'), 99 );
		#add_action( 'wp_loaded', array($this,'set_single_rewrite'), 100 );
		add_action( 'update_option_'.$this->_post_type.'_structure', array($this, 'flush_rules'), 10, 2 );
		add_action( 'update_option_no_taxonomy_structure', array($this, 'flush_rules'), 10, 2 );
		add_action( 'admin_init', array($this, 'admin_init'), 30 );
		
		add_filter( 'wp_setup_nav_menu_item',  array($this, 'setup_archive_item') );
		add_filter( 'wp_nav_menu_objects', array($this, 'maybe_make_current') );
		add_filter( 'get_redrokk_custom_post_types', array($this, 'get_post_type_object'), 20, 1 );
		add_filter( 'post_updated_messages', array($this, '_update_messages') );
		add_filter( "manage_edit-{$this->_post_type}_columns", array($this, 'table_headers'), 10, 1);
		
		add_filter( 'post_type_link', array($this,'set_permalink'), 10, 3 );
		add_filter( 'getarchives_where', array($this,'get_archives_where'), 10, 2 );
		add_filter( 'get_archives_link', array($this,'get_archives_link'), 20, 1 );
	}
	
	/**
	 *
	 * @param array $vars
	 */
	function query( $args = array(), $wp_query = null )
	{
		// initializing
		if (!array_key_exists('nopaging', $args) || $args['nopaging'] == false)
		{
			$args['paged'] = get_query_var('paged') ? get_query_var('paged') : 1;
				
			if ($args['posts_per_page'] <= 0) {
				$args['posts_per_page'] = 10;
			}
				
			// deprecation support
			$args['showposts'] = $args['posts_per_page'];
				
			if ($wp_query === null) {
				$wp_query = $GLOBALS['wp_query'];
			}
		}
		
		$wp_query->query( $args );
		$this->_query_hash = md5( $wp_query->query_vars );
	}
	
	/**
	 * Method displays generic pagination for any current post loop
	 *
	 * @param object $wp_query
	 */
	function pagination( $wp_query = null )
	{
		// reasons to fail
		if ($this->_query_hash !== md5($wp_query->query_vars)) return false;
	
		$big = 999999999; // need an unlikely integer
	
		?>
		<style type="text/css">
		.wp-paginate {padding:0; margin:0;}
		body .wp-paginate ul.page-numbers li {background-image: none !important;padding:0px;background-position: left center;padding:0;display:inline; list-style:none;}
		.wp-paginate a {background:#ddd; border:1px solid #ccc; color:#666; margin-right:4px; padding:3px 6px; text-align:center; text-decoration:none;}
		.wp-paginate a:hover, .wp-paginate a:active {background:#ccc; color:#888;}
		.wp-paginate .title {color:#555; margin-right:4px;}
		.wp-paginate .gap {color:#999; margin-right:4px;}
		.wp-paginate .current {color:#fff; background:#5f87ae; border:1px solid #89adcf; margin-right:4px; padding:3px 6px;}
		.wp-paginate .page {}
		.wp-paginate .prev, .wp-paginate .next {}
		</style>
		
		<div class="wp-paginate">
		<?php
		echo paginate_links( array(
			'base' 		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' 	=> '?paged=%#%',
			'current' 	=> max( 1, get_query_var('paged') ),
			'total' 	=> $wp_query->max_num_pages,
			'type'		=> 'list'
		) );
		?></div><?php 
	}
	
	/**
	 *
	 * @param unknown_type $columns
	 */
	function table_headers( $columns )
	{
		if (empty($this->_columns)) return $columns;
		
		print_r($columns);
		$newColumns = array();
		foreach ((array)$this->_columns as $params)
		{
			if (!array_key_Exists('id', $params)) continue;
			if (!array_key_Exists('title', $params)) continue;
			$newColumns[ $params['id'] ] = $params['title'];
		}
		
		return $newColumns;
	}
	
	/**
	 *
	 * @param string $column_id
	 * @param int $post_id
	 */
	function table_cells( $column_id, $post_id )
	{
		if (empty($this->_columns)) return false;
		
		foreach((array)$this->_columns as $params)
		{
			if ($params['id'] !== $column_id) continue;
			
			// initializing
			$related = array();
			$html = '';
			
			// if a meta_key is specified
			if (array_key_exists('meta_key', $params)) {
				if (is_array($params['meta_key'])) {
					foreach ((array)$params['meta_key'] as $key => $property) 
					{
						$html .= !is_int($key) ?$key :' ';
						$html .= get_post_meta($post_id, $property, true);
					}
				} else {
					$html .= get_post_meta($post_id, $params['meta_key'], true);
				}
			}
			
			// catching the relationships
			if (array_key_exists('relationship', $params) && class_exists('redrokk_relationship_class')) {
				$rel = redrokk_relationship_class::getInstance($params['relationship']);
				
				$post = get_post($post_id);
				$related = $rel->getRelated( $post );
				$relationships = array();
				
				foreach ((array)$related as $object)
				{
					$relationships[] = '<a href="'.get_permalink($object->ID).'">'.$object->post_title.'</a>';
				}
				
				$html .= implode(', ', $relationships);
			}
			
			// if a callback is specified
			if (array_key_exists('callback', $params)) {
				ob_start();
				$html .= call_user_func_array($params['callback'], array($post_id, $this, $html, $related));
				$html .= ob_get_clean();
			}
			
			// show it off
			echo $html;
		}
	}
	
	/**
	 *
	 * @param object $wp_query
	 */
	function loop_start( $wp_query = null )
	{
		// reasons to fail
		if (is_admin()) return false;
		if ($wp_query->query_vars['post_type'] !== $this->_post_type) return false;
		
		//ob_start();
	}
	
	/**
	 *
	 * @param object $wp_query
	 */
	function loop_end( $wp_query = null )
	{
		// reasons to fail
		if (is_admin()) return false;
		if ($wp_query->query_vars['post_type'] !== $this->_post_type) return false;
		
		//ob_get_clean();
	}
	
	/**
	 * Flush rules
	 *
	 * @since 0.7.9
	 *
	 */
	function flush_rules()
	{
		$this->set_archive_rewrite();
		$this->set_rewrite();
		flush_rewrite_rules();
	}
	
	/**
	 * 
	 * @param unknown_type $post_types
	 */
	function get_post_type_object( $post_types = null )
	{
		$post_type_object = get_post_type_object($this->_post_type);
		
		if ($post_types === null) {
			$post_types = $post_type_object;
		}
		
		if (is_array($post_types)) {
			$post_types[] = $post_type_object;
		}
		
		return $post_types;
	}
	
	/**
	 * 
	 */
	function metabox()
	{
		global $nav_menu_selected_id;
		$post_types = apply_filters( 'get_redrokk_custom_post_types', array()); 
		
		?>
		<!-- Post type checkbox list -->
		<ul id="redrokk-post-type-archive-checklist">
		<?php foreach ($post_types as $type): ?>
			<li><label><input type="checkbox" value ="<?php echo esc_attr($type->name); ?>" /> <?php echo esc_attr($type->labels->name); ?> </label></li>
		<?php endforeach; ?>
		</ul><!-- /#post-type-archive-checklist -->
		
		<!-- 'Add to Menu' button -->
		<p class="button-controls" >
			<span class="add-to-menu" >
				<img class="waiting" src="<?php echo admin_url('images/wpspin_light.gif') ?>" alt="">
				<input type="submit" id="submit-post-type-archives" <?php disabled( $nav_menu_selected_id, 0 ); ?> value="<?php esc_attr_e('Add to Menu'); ?>" name="add-post-type-menu-item"  class="button-secondary submit-add-to-menu" />
			</span>
		</p>
		
		<script type="application/javascript">
		jQuery(document).ready(function($) {
			$('#submit-post-type-archives').click(function(event) {
				event.preventDefault();
				$('#<?php echo __CLASS__ ?> .waiting').show();
				
				// Get checked boxes 
				var postTypes = [];
				$('#redrokk-post-type-archive-checklist li :checked').each(function() {
					postTypes.push($(this).val());
				});
				// Send checked post types with our action, and nonce 
				$.post( ajaxurl, {
						action: "redrokk-add-post-type-archive-links",
					//	posttypearchive_nonce: MyPostTypeArchiveLinks.nonce,
						post_types: postTypes
					},
					// AJAX returns html to add to the menu 
					function( response ) {
						$('#menu-to-edit').append(response);
						$('#<?php echo __CLASS__ ?> .waiting').hide();

						$('#redrokk-post-type-archive-checklist li :checked').each(function() {
							$(this).attr('checked', false);
						});
					}
				);
			})
		});
		</script>
		<?php
	}
	
	/**
	 * 
	 */
	function ajax_add_post_type()
	{
		if ( ! current_user_can( 'edit_theme_options' ) )
			die('-1');
		
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
		if(empty($_POST['post_types']))
			exit;
		
		// Create menu items and store IDs in array
		$item_ids = array();
		foreach ( (array) $_POST['post_types'] as $post_type) 
		{
			$post_type_obj = get_post_type_object($post_type);
			if(!$post_type_obj)
				continue;
			
			$menu_item_data= array(
					'menu-item-title' => esc_attr($post_type_obj->labels->name),
					'menu-item-type' => 'post_type_archive',
					'menu-item-object' => esc_attr($post_type),
					'menu-item-url' => get_post_type_archive_link($post_type)
			);
			//Collect the items' IDs.
			$item_ids[] = wp_update_nav_menu_item(0, 0, $menu_item_data );
		}
		
		// If there was an error die here
		if ( is_wp_error( $item_ids ) )
			die('-1');
		
		// Set up menu items
		foreach ( (array) $item_ids as $menu_item_id ) 
		{
			$menu_obj = get_post( $menu_item_id );
			if ( ! empty( $menu_obj->ID ) ) {
				$menu_obj = wp_setup_nav_menu_item( $menu_obj );
				$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
				$menu_items[] = $menu_obj;
			}
		}
		
		// This gets the HTML to returns it to the menu
		if ( ! empty( $menu_items ) ) 
		{
			$args = array(
					'after' => '',
					'before' => '',
					'link_after' => '',
					'link_before' => '',
					'walker' => new Walker_Nav_Menu_Edit
			);
			echo walk_nav_menu_tree( $menu_items, 0, (object) $args );
		}
		
		// Finally don't forget to exit
		exit;
	}
	
	/**
	 * 
	 * @param unknown_type $menu_item
	 * @return unknown
	 */
	function setup_archive_item( $menu_item )
	{
		if ($menu_item->type != 'post_type_archive')
			return $menu_item;
		
		$post_type = $menu_item->object;
		if ($post_type !== $this->_post_type) return $menu_item;
		
		$menu_item->url = $this->get_post_type_archive_link($this->_post_type);
		return $menu_item;
	}
	
	/**
	 * Retrieve the permalink for a post type archive.
	 *
	 * @since 3.1.0
	 *
	 * @param string $post_type Post type
	 * @return string
	 */
	function get_post_type_archive_link( $post_type ) 
	{
		global $wp_rewrite;
		if ( ! $post_type_obj = get_post_type_object( $post_type ) )
			return false;
	
		if ( get_option( $this->_post_type.'_structure' ) && is_array( $post_type_obj->rewrite ) ) 
		{
			$struct = $post_type_obj->rewrite['slug'];
			if ( $post_type_obj->rewrite['with_front'] ) {
				$struct = $wp_rewrite->front . $struct;
			} else {
				$struct = $wp_rewrite->root . $struct;
			}
			$link = home_url( user_trailingslashit( $struct, 'post_type_archive' ) );
		} else {
			$link = home_url( '?post_type=' . $post_type );
		}
		
		return apply_filters( 'post_type_archive_link', $link, $post_type );
	}
	
	/**
	 * 
	 * @param unknown_type $items
	 */
	function maybe_make_current( $items )
	{
		foreach ($items as $item) {
			if('post_type_archive' != $item->type)
				continue;
			$post_type = $item->object;
			if(!is_post_type_archive($post_type)&& !is_singular($post_type))
				continue;
			// Make item current
			$item->current = true;
			$item->classes[] = 'current-menu-item';
			// Get menu item's ancestors:
			$_anc_id = (int) $item->db_id;
			$active_ancestor_item_ids=array();
			while(( $_anc_id = get_post_meta( $_anc_id, '_menu_item_menu_item_parent', true ) ) && ! in_array( $_anc_id, $active_ancestor_item_ids )  ) {
				$active_ancestor_item_ids[] = $_anc_id;
			}
			// Loop through ancestors and give them 'ancestor' or 'parent' class
			foreach ($items as $key=>$parent_item) {
				$classes = (array) $parent_item->classes;
				// If menu item is the parent
				if ($parent_item->db_id == $item->menu_item_parent ) {
					$classes[] = 'current-menu-parent';
					$items[$key]->current_item_parent = true;
				}
				// If menu item is an ancestor
				if ( in_array(  intval( $parent_item->db_id ), $active_ancestor_item_ids ) ) {
					$classes[] = 'current-menu-ancestor';
					$items[$key]->current_item_ancestor = true;
				}
				$items[$key]->classes = array_unique( $classes );
			}
		}
		return $items;
	}
	
	/**
	 * 
	 */
	function admin_init() 
	{
		// creating the metabox for menu items
		add_meta_box( __CLASS__, __('Post Types', __CLASS__), array($this, 'metabox'), 'nav-menus', 'side', 'low' );
		
		// adding the section
		add_settings_section($this->_post_type.'_setting_section',
			__("Permalink Setting for {$this->_single} posts"),
			array($this, 'setting_section_callback_function'),
			'permalink'
		);

		// saving the custom permalink structure
		if(isset($_POST['submit']))
		{
			if( strpos($_POST['_wp_http_referer'], 'options-permalink.php') !== FALSE ) {

				$structure = trim(esc_attr($_POST[$this->_post_type.'_structure']));#get setting

				#default permalink structure
				if( !$structure )
					$structure = $this->default_structure;

				$structure = str_replace('//', '/', '/'.$structure);# first "/"

				#last "/"
				$lastString = substr(trim(esc_attr($_POST['permalink_structure'])),-1);
				$structure = rtrim($structure,'/');

				if ( $lastString == '/')
					$structure = $structure.'/';

				update_option($this->_post_type.'_structure', $structure);
			}
		}
		
		// adding field to section
		add_settings_field($this->_post_type.'_structure',
			$this->_post_type,
			array($this, 'setting_structure_callback_function'),
			'permalink',
			$this->_post_type.'_setting_section',
			$this->_post_type.'_structure'
		);
		register_setting('permalink', $this->_post_type.'_structure');
	}
	
	/**
	 * 
	 */
	function setting_section_callback_function()
	{
		?>
		<p><?php _e("Setting permalinks of custom post type.",__CLASS__);?>
		<?php _e("The tags you can use is WordPress Structure Tags and '%\"custom_taxonomy_slug\"%'. (e.g. %actors%)",__CLASS__);?>
		<?php _e("%\"custom_taxonomy_slug\"% is replaced the taxonomy's term.'.",__CLASS__);?></p>

		<p><?php _e("Presence of the trailing '/' is unified into a standard permalink structure setting.",__CLASS__);?>
		<?php _e("If you don't entered permalink structure, permalink is configured /%postname%/'.",__CLASS__);?>
		</p>
		<?php
	}
	
	/**
	 * 
	 * @param unknown_type $option
	 */
	function setting_structure_callback_function(  $option  ) 
	{
		$post_type = str_replace('_structure', "", $option);
		$slug = get_post_type_object($post_type)->rewrite['slug'];
		if( !$slug )
			$slug = $post_type;
		
		echo '/'.$slug.' <input name="'.$option.'" id="'.$option.'" type="text" class="regular-text code" value="' . get_option($option) .'" />';
	}
	
	/**
	 * Add rewrite rules for archives.
	 *
	 */
	function set_archive_rewrite()
	{
		$permalink = get_option( $this->_post_type.'_structure' );
		$this->_post_type_obj = get_post_type_object($this->_post_type);
		$slug = $this->_post_type_obj->rewrite['slug'];
		if ( !$slug )
			$slug = $this->_post_type;
		
		if (is_string( $this->_post_type_obj->has_archive )) {
			$slug = $this->_post_type_obj->has_archive;
		}
		
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/([0-9]{1,2})/?$', 'index.php?year=$matches[1]&monthnum=$matches[2]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?year=$matches[1]&feed=$matches[2]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$', 'index.php?year=$matches[1]&feed=$matches[2]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/page/?([0-9]{1,})/?$', 'index.php?year=$matches[1]&paged=$matches[2]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/date/([0-9]{4})/?$', 'index.php?year=$matches[1]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/author/([^/]+)/?$', 'index.php?author=$matches[1]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/page/?([0-9]{1,})/?$', 'index.php?paged=$matches[1]&post_type='.$this->_post_type, 'top' );
		add_rewrite_rule( $slug.'/?$', 'index.php?post_type='.$this->_post_type, 'top' );
	}
	
	/**
	 * Add Rewrite rule for single posts.
	 *
	 */
	public function set_rewrite()
	{
		global $wp_rewrite;
		
		$permalink = get_option( $this->_post_type.'_structure' );
		
		if( !$permalink )
			$permalink = self::$default_structure;
		
		$permalink = str_replace( '%postname%', '%'.$this->_post_type.'%', $permalink );
		$permalink = str_replace( '%post_id%', '%'.$this->_post_type.'_id%', $permalink );
		
		$slug = get_post_type_object($this->_post_type)->rewrite['slug'];
		
		if( !$slug )
			$slug = $this->_post_type;
		
		$permalink = '/'.$slug.'/'.$permalink;
		$permalink = $permalink.'/%'.$this->_post_type.'_page%';
		$permalink = str_replace( '//', '/', $permalink );
		
		$wp_rewrite->add_rewrite_tag( '%post_type%', '([^/]+)', 'post_type=' );
		$wp_rewrite->add_rewrite_tag( '%'.$this->_post_type.'_id%', '([0-9]{1,})','post_type='.$this->_post_type.'&p=' );
		$wp_rewrite->add_rewrite_tag( '%'.$this->_post_type.'_page%', '([0-9]{1,}?)',"page=" );

		$wp_rewrite->generate_rewrite_rules( $permalink, EP_NONE, true, true, true,true);
		$wp_rewrite->add_permastruct( $this->_post_type, $permalink, false );
		
		$wp_rewrite->use_verbose_page_rules = true;
	}
	
	/**
	 * Fix permalinks output.
	 *
	 */
	public function set_permalink( $post_link, $post, $leavename ) 
	{
		// intiailizing
		global $wp_rewrite;
		$draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );
		
		//reasons to fail
		if ($post->post_type != $this->_post_type) return $post_link;
		if( $draft_or_pending and !$leavename ) return $post_link;

		$permalink = $wp_rewrite->get_extra_permastruct( $this->_post_type );
		
		//reasons to fail
		if (!$permalink)  return $post_link;
		
		$permalink = str_replace( '%post_type%', get_post_type_object($this->_post_type)->rewrite['slug'], $permalink );
		$permalink = str_replace( '%'.$this->_post_type.'_id%', $post->ID, $permalink );
		$permalink = str_replace( '%'.$this->_post_type.'_page%', "", $permalink );
		$permalink = str_replace( '%'.$this->_post_type.'_cpage%', "", $permalink );
		
		$parentsDirs = "";
		$postId = $post->ID;
		while ($parent = get_post($postId)->post_parent) {
			$parentsDirs = get_post($parent)->post_name."/".$parentsDirs;
			$postId = $parent;
		}

		$permalink = str_replace( '%'.$this->_post_type.'%', $parentsDirs.'%'.$this->_post_type.'%', $permalink );

		if( !$leavename ){
			$permalink = str_replace( '%'.$this->_post_type.'%', $post->post_name, $permalink );
		}

		$taxonomies = get_taxonomies( array('show_ui' => true),'objects' );
		
		foreach ( $taxonomies as $taxonomy => $objects ) {
			if ( strpos($permalink, "%$taxonomy%") !== false ) {
				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( $terms ) {
					usort($terms, '_usort_terms_by_ID'); // order by ID
					$term = $terms[0]->slug;

					if ( $parent = $terms[0]->parent )
						$term = $this->get_taxonomy_parents( $parent,$taxonomy, false, '/', true ) . $term;
				}

				if( isset($term) ) {
					$permalink = str_replace( "%$taxonomy%", $term, $permalink );
				}
			}
		}

		$user = get_userdata( $post->post_author );
		$permalink = str_replace( "%author%", $user->user_nicename, $permalink );

		$post_date = strtotime( $post->post_date );
		$permalink = str_replace( "%year%",     date("Y",$post_date), $permalink );
		$permalink = str_replace( "%monthnum%", date("m",$post_date), $permalink );
		$permalink = str_replace( "%day%",	      date("d",$post_date), $permalink );
		$permalink = str_replace( "%hour%",     date("H",$post_date), $permalink );
		$permalink = str_replace( "%minute%",   date("i",$post_date), $permalink );
		$permalink = str_replace( "%second%",   date("s",$post_date), $permalink );

		$permalink = str_replace('//', "/", $permalink );

		$permalink = home_url( user_trailingslashit( $permalink ) );
		$str = rtrim( preg_replace("/%[a-z,_]*%/","",get_option("permalink_structure")) ,'/');
		return $permalink = str_replace($str, "", $permalink );
	}
	
	/**
	 * wp_get_archives fix for custom post
	 * Ex:wp_get_archives('&post_type='.get_query_var( 'post_type' ));
	 *
	 */
	public function get_archives_where( $where, $r ) 
	{
		$this->get_archives_where_r = $r;
		if ( isset($r['post_type']) )
			$where = str_replace( '\'post\'', '\'' . $r['post_type'] . '\'', $where );

		return $where;
	}

	/**
	 * 
	 * @param unknown_type $link
	 */
	public function get_archives_link( $link ) 
	{
		if (isset($this->get_archives_where_r['post_type'])  and  $this->get_archives_where_r['type'] != 'postbypost')
		{
			$blog_url = get_bloginfo("url");

			// /archive/%post_id%
			if ($str = rtrim( preg_replace("/%[a-z,_]*%/","",get_option("permalink_structure")) ,'/')) {
				$ret_link = str_replace($str, '/'.'%link_dir%', $link);
			} else {
				$blog_url = rtrim($blog_url,"/");
				$ret_link = str_replace($blog_url,$blog_url.'/'.'%link_dir%',$link);
			}
			$link_dir = $this->get_archives_where_r['post_type'];

			if (!strstr($link,'/date/')){
				$link_dir = $link_dir .'/date';
			}

			$ret_link = str_replace('%link_dir%',$link_dir,$ret_link);

			return $ret_link;
		}
		return $link;
	}
		
	/**
	 * 
	 * @param object $wp_query
	 */
	function pre_get_posts( $wp_query )
	{
		// reasons to fail
		if (is_admin()) return false;
		if ($wp_query->query_vars['post_type'] !== $this->_post_type) return false;
		if ($wp_query->query_vars['nopaging'] === true) return false;
		if (array_key_exists('paged', $wp_query->query_vars) && $wp_query->query_vars['paged']) return false;
		
		// initialize the pagination
		$this->query($wp_query->query_vars, $wp_query);
		
		add_action( 'loop_end', array($this, 'pagination'), 20, 1 );
	}
	
	/**
	 * Method returns properly formed labels
	 * 
	 */
	function getLabels()
	{
		$labels = array();
		foreach ((array)$this->_labels as $k => $label) {
			$labels[$k] = sprintf( __($label), $this->_single, $this->_plural );
		}
		return $labels;
	}
	
	/**
	 * Method provides updates messages to notify the user about what they have just
	 * done to the custom post type.
	 * 
	 * @param array $messages
	 */
	function _update_messages( $messages )
	{
		global $post, $post_ID;
		
		$messages[$this->_post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __($this->_messages[1]),
					$this->_single, 
					esc_url( get_permalink($post_ID) ) 
				),
			2 => __($this->_messages[2]),
			3 => __($this->_messages[3]),
			4 => sprintf( __($this->_messages[4]), $this->_single ),
			5 => isset($_GET['revision']) ? sprintf( __($this->_messages[5]), $this->_single, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __($this->_messages[6]), 
					$this->_single, 
					esc_url( get_permalink($post_ID) ) 
				),
			7 => sprintf( __($this->_messages[7]), $this->_single ),
			8 => sprintf( __($this->_messages[8]), 
					$this->_single, 
					esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) 
				),
			9 => sprintf( __($this->_messages[9]), 
					$this->_single, 
					date_i18n( __( 'M j, Y @ G:i' ), 
					strtotime( $post->post_date ) ), 
					esc_url( get_permalink($post_ID) ) 
				),
			10 => sprintf( __($this->_messages[10]), 
					$this->_single, 
					esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) )
				),
		);
		
		return $messages;
	}
	
	/**
	 * Method displays the help text in the drop down
	 * 
	 * @param string $contextual_help
	 * @param string $screen_id
	 * @param string $screen
	 */
	function _help_text( $contextual_help, $screen_id, $screen )
	{
		$url = 'http://www.redrokk.com';
		$urle = urlencode($url);
		
		$title = 'RedRokk Interactive Media';
		
		$description = 'Are you using RedRokk Interactive Media for your software development?';
		$desc = urlencode($description);
		
		ob_start(); 
		?>
		<style>
		.twc-hr {margin: 20px 0 10px;border: none;border-bottom: 1px dashed #CCC;}
		.twc-share {position:relative;float:right;width:200px;}
		.twc-avatar {margin-right:20px;position:relative;float:left;width:100px;}
		</style>
		<hr class="twc-hr"/>
		<div class="twc-share">
			<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo $urle; ?>&amp;layout=box_count&amp;show_faces=false&amp;width=50&amp;action=like&amp;colorscheme=light&amp;height=65" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:50px; height:65px;margin-bottom: -5px;" allowTransparency="true"></iframe>
			
			<a href="http://twitter.com/share" class="twitter-share-button" data-url="<?php echo $url; ?>" data-text="<?php echo $description; ?>" data-count="vertical">Tweet</a>
			<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
			
			<a class="DiggThisButton DiggMedium" href="http://digg.com/submit?url=<?php echo $urle; ?>&bodytext=<?php echo $description; ?>">
				<img src="http://developers.diggstatic.com/sites/all/themes/about/img/digg-btn.jpg" alt="<?php echo $description; ?>" title="<?php echo $title; ?>" />
				<?php echo $title; ?>
			</a>
			<script type="text/javascript">
			(function() {
			var s = document.createElement('SCRIPT'), s1 = document.getElementsByTagName('SCRIPT')[0];
			s.type = 'text/javascript';
			s.async = true;
			s.src = 'http://widgets.digg.com/buttons.js';
			s1.parentNode.insertBefore(s, s1);
			})();
			</script>
		</div>
		
		<img class="twc-avatar" style="margin:10px 20px;" src="<?php echo $url; ?>/logo.png" />
		<h4 style="margin:0px;"><?php echo $title; ?></h4>
		<p><?php _e('<a href="'.$url.'">This Plugin</a> is provided by '.$title.'. Please support us, <br/>so that we can continue to support you.','twc');?></p>
		<?php 
		$_help = ob_get_clean();
		
		//$contextual_help .= var_dump( $screen ); // use this to help determine $screen->id
		if ( $this->_post_type == $screen->id ) {
			$contextual_help = $this->_help.$_help;
		} 
		elseif ( 'edit-'.$this->_post_type == $screen->id ) {
			$contextual_help = $this->_help_edit.$_help;
		}
		return $contextual_help;
	}
	
	/**
	 * To get permalinks to work when you activate the plugin use the 
	 * following example, paying attention to how my_cpt_init is called 
	 * in the register_activation_hook callback
	 * 
	 */
	function _rewrite_flush()
	{
		// First, we "add" the custom post type via the above written function.
		// Note: "add" is written with quotes, as CPTs don't get added to the DB,
		// They are only referenced in the post_type column with a post entry, 
		// when you add a post of this CPT.
		$this->_register_post_type();
	
		// ATTENTION: This is *only* done during plugin activation hook in this example!
		// You should *NEVER EVER* do this on every page load!!
		flush_rewrite_rules();
	}
	
	/**
	 * Method registers this post type with WordPress
	 * 
	 */
	function _register_post_type()
	{
		$options = $this->getProperties();
		$options['labels'] = $this->getLabels();
		
		register_post_type( $this->_post_type, $options );
	}
	
	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src	An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 */
	public function bind($src, $ignore = array())
	{
		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			trigger_error('Bind failed as the provided source is not an array.');
			return false;
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->getProperties() as $k => $v)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				if (isset($src[$k]))
				{
					$this->$k = $src[$k];
				}
			}
		}

		return true;
	}
	
	/**
	 * Set the object properties based on a named array/hash.
	 *
	 * @param   mixed  $properties  Either an associative array or another object.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 *
	 * @see	set() 
	 */
	public function setProperties($properties)
	{
		if (is_array($properties) || is_object($properties))
		{
			foreach ((array) $properties as $k => $v)
			{
				// Use the set function which might be overridden.
				$this->set($k, $v);
			}
			return true;
		}

		return false;
	}
	
	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value	The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 *
	 * @since   11.1
	 */
	public function set($property, $value = null)
	{
		$previous = isset($this->$property) ? $this->$property : null;
		$this->$property = $value;
		return $previous;
	}
	
	/**
	 * Returns an associative array of object properties.
	 *
	 * @param   boolean  $public  If true, returns only the public properties.
	 *
	 * @return  array 
	 *
	 * @see	get()
	 */
	public function getProperties($public = true)
	{
		$vars = get_object_vars($this);
		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1))
				{
					unset($vars[$key]);
				}
			}
		}

		return $vars;
	}
	
	/**
	 * 
	 * contains the current instance of this class
	 * @var object
	 */
	static $_instances = null;
	
	/**
	 * Method is called when we need to instantiate this class
	 * 
	 * @param array $options
	 */
	public static function getInstance( $_post_type, $options = array() )
	{
		if (!isset(self::$_instances[$_post_type]))
		{
			$options['_post_type'] = $_post_type;
			$class = get_class();
			self::$_instances[$_post_type] =& new $class($options);
		}
		return self::$_instances[$_post_type];
	}
}
endif;