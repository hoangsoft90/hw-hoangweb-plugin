<?php
/**
* Widget Social Share
*/
class HW_SocialShare_widget extends WP_Widget{
    /**
     * @var
     */
    public $socials = null;

    /**
     * list socials buttons
     * @var array|null
     */
    static $socials_button = null;

    /**
     * list avaiable sharing services
     */
    static $sharing_services;

    /**
     * constructor
     */
    public function __construct() {
		
	  parent::__construct(
	  // Base ID of your widget
	  'hwss',

	  // Widget name will appear in UI
	  __('Nút Chia sẻ  - Hoangweb', 'hwss'),

	  // Widget description
	  array( 'description' => __( 'Các nút chia sẻ facebook, google+,twitter..', 'hwss' ), )
	  );
	  //you should check if whether this widget actived on frontend or neither maybe you can get widget data by get_option($this->option_name)
	  #if(!is_admin() && !is_active_widget( false, false, $this->id_base, true)) return; //don't we need using the_widget function to clone this widget
	  
	  
	  self::$socials_button = $this->socials = array(
		'facebook_share' => 'Facebook Share',
		'facebook_like' => 'Facebook Like',
		'facebook_recommend_button' => 'Facebook Recommend Button',
		'googleplus' => 'Google +',
		'twitter' => 'Twitter',
		'twitter_follow_button' => 'Twitter Follow Button',
		'linkedin' => 'LinkedIn',
		'pinterest' => 'Pinterest',
		#'buffer' => 'Buffer',
		#'digg' => 'Digg',
		
		#'reddit' => 'Reddit',
		#	'dzone' => 'dZone',
		#	'delicious' => 'Delicious',
		#	'tumblr' => 'Tumblr',
		#	'bitly' => 'Bit.ly',
		#	'email' => 'Email'
		);	//socials service
		
		//sharing services
	  self::$sharing_services = $this->services = array(
			'addthis' => array(
				'text'=>'AddThis',
				#$this->number => array('settings_id'=>$this->get_field_id('addthis').'_settings'),
				'enable' => true
				),
			'sharethis' => array(
				'text' => 'Sharethis',
				#$this->number => array('settings_id' => $this->get_field_id('sharethis').'_settings'),
				'enable' => false
			),
			'socialite' => array(
				'text' => 'Socialite','description' => 'Lazy load socials button.','enable' => true
			)
		);	
		add_action('admin_enqueue_scripts',array(&$this , '_admin_init_style_script'),10,3);
		add_action('wp_enqueue_scripts',array(&$this , '_init_style_script') );
		
	}
	/**
	register this widget
	*/
	static function init(){
		register_widget( 'HW_SocialShare_widget' );
	}
	/**
	admin init scripts css
	*/
	public function _admin_init_style_script(){
		wp_enqueue_style('hwss-admin-css',plugins_url('css/admin-style.css',__FILE__));
		#wp_enqueue_script('hwss-widget-js',plugins_url('js/hwss-widget.js',__FILE__));
		wp_localize_script('hwss-admin-js','HW_SS',array('services'=>$this->services));
	}
	/**
	init scripts css
	*/
	public function _init_style_script(){
		$instance = $this->get_instance();
		if(!is_admin()){
			wp_enqueue_style('hwss-css',plugins_url('css/style.css',__FILE__));
			
			if(isset($instance['sharing_service']) && $instance['sharing_service'] == 'socialite'){
				// Register Socialite
				wp_register_script( 'socialite', plugins_url('js/socialite.min.js',__FILE__) , array(), '', true );

				// Now enqueue Socialite
				wp_enqueue_script( 'socialite' );
			}
		}
		//wp_enqueue_script('hwss-js');	//init script, already exists
	}
	/*-------------AddThis--------------*/
	public static function addthis_get_comtactbtns(){
		$compact_url = plugins_url('images/addthis_compactbtn',__FILE__);	
		$compact_btns = (plugin_dir_path(__FILE__)).'images/addthis_compactbtn';
		if ($handle = opendir($compact_btns)) {
			$images = array();
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") { 
					$images[] = $compact_url.'/'.$entry;
				}
			}
			closedir($handle);
			return $images;
		}
	}
	/**
	return sharing service
	*/
	private function is_sharing_service($name){
		if(!isset($this->instance)) return ;	//invalid
		$instance = $this->get_instance();
		return $instance['sharing_service'] == $name;
	}
	/**
	 * check if social service is actived
	 */
	private function is_enable_button($social){
		if(!isset($this->instance)) return ;	//invalid
		$instance = $this->get_instance();
		return in_array($social,$instance['pick_socials']);
	}
	/**
     * get social button size
     */
	private function get_btn_size($default = ''){
		if(!isset($this->instance)) return ;	//invalid
		$instance = $this->get_instance();
		return isset($instance['button_size'])? $instance['button_size'] : $default;
	}
	/**
     * order socials in select tag
     */
	private function order_socials_display(){
		if(!isset($this->instance)) return ;	//invalid
		$instance = $this->get_instance();
		
		if(!isset($instance['save_order_items_pick_socials'])) $instance['save_order_items_pick_socials'] = array_keys($this->socials);
		elseif(is_string($instance['save_order_items_pick_socials'])){
			$instance['save_order_items_pick_socials'] = explode(',', $instance['save_order_items_pick_socials'] );
		}
		//order socials in select tag
		if(is_array($instance['save_order_items_pick_socials'])){
            self::order_assoc_array_base_other($instance['save_order_items_pick_socials'] , $this->socials);
        }

		/*foreach($instance['save_order_items_pick_socials'] as $key){
		  $val = $this->socials[$key];
		  unset($this->socials[$key]);
		  $this->socials[$key] = $val;
		}*/
	}

    /**
     * order array base other
     * @param $ordered
     * @param $from
     * @return array
     */
    public static function order_assoc_array_base_other($ordered , &$from){
		if(is_array($ordered) && is_array($from))
		foreach($ordered as $key){
			if(!isset($from[$key])) continue;
		  $val = $from[$key];
		  unset($from[$key]);
		  $from[$key] = $val;
		}
		return $from;
	}
	/**
	 * get widget instance
	 */
	private function get_instance(){
		//get current widget instance
		if(!isset($this->instance) && $this->number){
			$options = get_option($this->option_name);
			if(isset($options[$this->number])) {
                $instance = $options[$this->number];
                $this->instance = $instance;
            }
            else $this->instance = array(); //empty widget instance
		}
		return $this->instance;
	}
	/**
	 * echo wrapper class
     * @param $class
     * @param $prefix
	 */
	private function wrapper_class($class = '', $prefix = ''){
		$classes = is_string($class)? explode(' ',$class) : (array)$class;
		$instance = $this->get_instance();
		$classes[] = $prefix? $prefix.'_'.$instance['sharing_service'] : $instance['sharing_service'];
		echo 'class="'.implode(' ',$classes).'"';
	}
	/*---------------------------------------------------*/
    /**
     * display widget content
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
		$instance['class_socials_button_wrapper'] = isset($instance['class_socials_button_wrapper'])? $instance['class_socials_button_wrapper'] : 'socials-share-bar';
		$instance['addthis_compactbtn'] = isset($instance['addthis_compactbtn'])? $instance['addthis_compactbtn'] : '';
		$instance['button_style'] = isset($instance['button_style'])? $instance['button_style'] : 'standard';
		$instance['save_order_items_pick_socials'] = isset($instance['save_order_items_pick_socials'])? explode(',',$instance['save_order_items_pick_socials']) : array_keys($this->socials);
		
		  
		//init js & css
		if($instance['sharing_service'] == 'addthis'){
			wp_enqueue_script('addthis','http://s7.addthis.com/js/300/addthis_widget.js');
		}
		add_action('wp_head',function() use ($instance){
			if(isset($instance['custom_css'])) echo '<style type="text/css">'.$instance['custom_css'].'</style>';
		});
		$this->instance = $instance;
		//button style
		$is_vertical_counter = isset($instance['button_style']) && $instance['button_style'] == 'vertical-counter' ;
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		// before and after widget arguments are defined by themes
          echo $args['before_widget'];
          if ( ! empty( $title ) )
          echo $args['before_title'] . $title . $args['after_title'];
	  
	  //order socials display
	  $this->order_socials_display();
	  ?>
		<div <?php $this->wrapper_class($instance['class_socials_button_wrapper'],'wrap'); ?>>
			<div <?php $this->wrapper_class('socials-buttons')?>>
				<ul>
		<?php 
		//for addthis compact shareing button
		if($instance['sharing_service'] == 'addthis' && $instance['addthis_compactbtn'] == 'on'){
			echo '<a class="addthis_button_compact">';
			if(isset($instance['addthis_compactbtn_style'])) echo '<img src="'.$instance['addthis_compactbtn_style'].'" class="addthis-compact-btn"/>';
			else echo '<img src="http://s7.addthis.com/static/btn/sm-plus.gif" width="16" height="16" border="0" alt="Share" />';	//default button
			echo '</a>';
		}
		else{
		foreach($this->socials as $key=>$val):
			//google +
			if($this->is_enable_button($key)){	
				if($this->is_sharing_service('addthis')){
					if($key == 'googleplus'){	#google plus
						echo '<li>';
						if($is_vertical_counter) echo '<a class="addthis_button_google_plusone" g:plusone:size="tall"></a>';
						else echo '<a class="addthis_button_google_plusone" g:plusone:size="'.$this->get_btn_size().'"></a>';
						echo '</li>';
					}
					if($key == 'facebook_share'){	#facebook share/send
						echo '<li>';
						echo '<a class="addthis_button_facebook_send"></a>';
						echo '</li>';
					}
					if($key == 'facebook_recommend_button'){
						echo '<li>';
						if($is_vertical_counter) echo '<a class="addthis_button_facebook_like" fb:like:layout="box_count"></a> ';
						else echo '<a class="addthis_button_facebook_like" fb:like:layout="button_count" fb:like:action="recommend"></a>';
						echo '</li>';
					}
					if($key == 'facebook_like'){	#facebook like
						echo '<li>';
						if($is_vertical_counter) echo '<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>';
						else echo '<a class="addthis_button_facebook_like"></a>';

						echo '</li>';
					}
					if($key == 'twitter'){	#twitter
						echo '<li>';
						if($is_vertical_counter) echo '<a class="addthis_button_tweet" tw:count="vertical"></a>';
						else echo '<a class="addthis_button_tweet" tw:via="addthis" style="width:47px; overflow:hidden;position:relative; top:auto;  bottom:-4px; left:-20px;"></a>';
						echo '</li>';
					}
					if($key == 'twitter_follow_button'){
						echo '<li>';
						echo '<div class="addthis_toolbox addthis_default_style"><a class="addthis_button_twitter_follow_native"></a></div> ';
						echo '</li>';
					}
					if($key == 'linkedin'){	//LinkedIn Button
						echo '<li>';
						if($is_vertical_counter) echo '<a class="addthis_button_linkedin_counter" li:counter="top"></a>';
						else echo '<a class="addthis_button_linkedin_counter"></a>';
						echo '</li>';
					}
					if($key == 'pinterest'){	//Pinterest Button
						echo '<li>';
						if($is_vertical_counter) echo '<a class="addthis_button_pinterest_pinit" pi:pinit:layout="vertical" pi:pinit:description="Hoangweb"></a>';
						else echo '<a class="addthis_button_pinterest_pinit" pi:pinit:layout="horizontal" pi:pinit:description="Hoangweb"></a>';
						echo '</li>';
					}
					if($key == 'xxx'){
						
					}
                    //scale share buttons
                    if($this->get_btn_size() == 'medium') {	//Resize/scale facebook like button
                        $scale = '1.5';

                    }
                    elseif(!empty($instance['scale']) && $this->get_btn_size() == 'custom') {
                        $scale = $instance['scale'];
                    }

                    if(isset($scale)) {
                        echo '
                        <style>
                        iframe
                        {
                        transform: scale('.$scale.');
                        -ms-transform: scale('. $scale .');
                        -webkit-transform: scale('. $scale .');
                        -o-transform: scale('. $scale .');
                        -moz-transform: scale('. $scale .');
                        transform-origin: top left;
                        -ms-transform-origin: top left;
                        -webkit-transform-origin: top left;
                        -moz-transform-origin: top left;
                        -webkit-transform-origin: top left;
                        }
                        </style>
                        ';
                    }

				}
				if($this->is_sharing_service('sharethis')){
				}
				if($this->is_sharing_service('socialite') && $is_vertical_counter){
					if($key == 'twitter'){#twitter
						echo '<li>';
						if($is_vertical_counter){
						echo '<a class="socialite twitter-share" href="http://twitter.com/share" rel="nofollow" target="_blank" data-text="'.get_the_title().'" data-url="'.get_permalink().'" data-count="vertical" data-via="twitter-username-here"><span class="vhidden">Share on Twitter</span></a>';
						}
						echo '</li>';
					}
					if($key == 'googleplus'){	#googleplus
						echo '<li>';
						if($is_vertical_counter)
							echo '<a class="socialite googleplus-one" href="https://plus.google.com/share?url='.get_permalink().'" rel="nofollow" target="_blank" data-size="tall" data-href="'.get_permalink().'"><span class="vhidden">Share on Google+</span></a>';
						echo '</li>';
					}
					if($key == 'facebook_like'){	#facebook_like
						echo '<li>';
						if($is_vertical_counter) echo '<a class="socialite facebook-like" href="https://www.facebook.com/sharer.php?u='.get_permalink().'" rel="nofollow" target="_blank" data-href="'.get_permalink().'" data-send="false" data-layout="box_count" data-width="60" data-show-faces="false"><span class="vhidden">Share on Facebook</span></a>';
						echo '</li>';
					}
					if($key == 'linkedin'){	#//LinkedIn Button
						echo '<li>';
						if($is_vertical_counter) echo '<a class="socialite linkedin-share" href="http://www.linkedin.com/shareArticle?mini=true&url='.get_permalink().'&title='.get_the_title().'" rel="nofollow" target="_blank" data-url="'.get_permalink().'" data-counter="top"><span class="vhidden">Share on LinkedIn</span></a>';
						echo '</li>';
					}
				}
			}
		endforeach;
		}
			?></ul>
			</div>
		</div>
	  <?php
		echo $args['after_widget'];
	}
	/**
	 * return current widget instance
	 */
	private function get_this_instance(){
		if(isset($this->option_name) && isset($this->number)){
			$widgets = get_option($this->option_name);
			if(isset($widgets[$this->number])) return $widgets[$this->number];
		}
	}
	
	/**
     * Widget Backend
     * @param $instance
     */
     public function form( $instance ) {
		 $this->instance = $instance;	//save widget instance
         //widget title
          if ( isset( $instance[ 'title' ] ) ) {
               $title = $instance[ 'title' ];
          }
          else {
               $title = __( 'Nút chia sẻ', 'hwss' );
          }
         if(!isset($instance['button_style'])) $instance['button_style']='';
         if(!isset($instance['button_size'])) $instance['button_size']='';
         if(!isset($instance['pick_socials'])) $instance['pick_socials']=array();

		  $instance['class_socials_button_wrapper'] = isset($instance['class_socials_button_wrapper'])? $instance['class_socials_button_wrapper'] : 'socials-share-bar';
		  $instance['sharing_service'] = isset($instance['sharing_service'])? (array)$instance['sharing_service'] : array();
		  $instance['addthis_compactbtn'] = isset($instance['addthis_compactbtn'])? $instance['addthis_compactbtn'] : '';
		  $instance['save_order_items_pick_socials'] = isset($instance['save_order_items_pick_socials'])? explode(',',$instance['save_order_items_pick_socials']) : array_keys($this->socials);

         //scale button size
          $instance['scale'] = isset($instance['scale'])? (float) $instance['scale'] : 0;

		  //order socials in select tag
		  $this->order_socials_display();
		  
		  ?>
		  <p>
          <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
          </p>
		  <p>
				<label for="<?php echo $this->get_field_id( 'sharing_service' ); ?>"><?php _e('Dịch vụ')?></label>
				<select name="<?php echo $this->get_field_name( 'sharing_service' ); ?>" id="<?php echo $this->get_field_id( 'sharing_service' ); ?>" onchange="HW_SS.change_sharing_service(this)">
					<?php foreach($this->services as $serv => $name):
						
					?>
						<option value="<?php echo $serv?>" <?php if(isset($name['enable']) && !$name['enable']) echo 'disabled'?> <?php selected($instance['sharing_service']==$serv? 1 :0)?>><?php echo ucfirst($name['text'])?></option>
					<?php endforeach;?>
				</select>
		  </p>
		  <!-- addthis settings -->
		  <div id="<?php echo $this->get_field_id('addthis')?>_settings" class="<?php if(!in_array('addthis',$instance['sharing_service'])) echo 'hwss-hidden'?>"><!--  -->
			<p>
				<label><?php _e('Nhóm nút chia sẻ')?></label>
				<input type="checkbox" name="<?php echo $this->get_field_name('addthis_compactbtn')?>" id="<?php echo $this->get_field_id('addthis_compactbtn')?>" <?php checked($instance['addthis_compactbtn']=='on'?1:0)?> onclick="$('#<?php echo $this->get_field_id('addthis_compactbtn_styles')?>').toggle((this.checked));"/>
			</p>
			<div id="<?php echo $this->get_field_id('addthis_compactbtn_styles')?>" class="<?php if($instance['addthis_compactbtn']!=='on') echo 'hwss-hidden'?>">
				<label ><?php _e('Template')?></label>
				<div id="" style="width:100%;overflow:auto;max-height:150px;border:1px solid gray;padding:3px;">
					<table>
					<?php 
					$list = self::addthis_get_comtactbtns();
					if(is_array($list))
					foreach($list as $img){
					?>
						<tr class="addthis-compact-btn">
							<td valign="center"><input type="radio" <?php checked(($img==$instance['addthis_compactbtn_style']? 1:0))?> id="" name="<?php echo $this->get_field_name('addthis_compactbtn_style')?>" value="<?php echo $img?>"/></td>
							<td valign="center"><img src="<?php echo $img?>"/></td>
						</tr>
					<?php }?>
					</table>
				</div>
			</div>
		  </div>
		  <!-- sharethis settings -->
		  <div id="<?php echo $this->get_field_id('sharethis')?>_settings" class="<?php if(!in_array('sharethis',$instance['sharing_service'])) echo 'hwss-hidden'?>"><!--  -->
		  sharethis settings
		  </div>
		  <!-- Socialite settings -->
		  <div id="<?php echo $this->get_field_id('socialite')?>_settings" class="<?php if(!in_array('socialite',$instance['sharing_service'])) echo 'hwss-hidden'?>"><!--  -->
			<p>Hiện tại chỉ áp dụng cho kiểu nút 'vertical counter'. <?php echo $this->services['socialite']['description']?></p>
		  </div>
		  <p>
		  <?php 
		  $pick_socials_tag = $this->get_field_id('pick_socials');
		  ?>
				<label for="<?php echo $this->get_field_id( 'pick_socials' ); ?>"><?php _e('Chọn mạng xã hội')?></label>
				<select class="widefat" size="10" multiple="multiple" name="<?php echo $this->get_field_name('pick_socials')?>[]" id="<?php echo $pick_socials_tag?>">
					<?php foreach($this->socials as $id=>$name){
						printf(
							'<option value="%s" %s style="margin-bottom:3px;">%s</option>',
							$id,
							in_array( $id, $instance['pick_socials']) ? 'selected="selected"' : '',
							$name
						);
					}?>
				</select>
				<a href="JavaScript:void(0);" id="<?php echo $this->get_field_id('btn-up')?>">Up</a> | 
				<a href="JavaScript:void(0);" id="<?php echo $this->get_field_id('btn-down')?>">Down</a>
				<input type="hidden" name="<?php echo $this->get_field_name('save_order_items_pick_socials')?>" id="<?php echo $this->get_field_id('save_order_items_pick_socials')?>" value="<?php echo isset($instance['save_order_items_pick_socials'])? implode(',',$instance['save_order_items_pick_socials']) : ''?>"/>
		  </p>
		  <!-- button style -->
		  <p>
			<label for="<?php echo $this->get_field_id('button_style')?>"><?php _e('Share/Like Button Style')?></label>
			<select name="<?php echo $this->get_field_name('button_style')?>" id="<?php echo $this->get_field_id('button_style')?>">
				<option value="vertical-counter" <?php selected($instance['button_style']=='vertical-counter'?1:0)?>>Vertical counter</option>
				<option value="stardard" <?php selected($instance['button_style']=='stardard'?1:0)?>>Horizontal(standard)</option>
			</select>
		  </p>
		  <!-- button size -->
		  <p>
			<label for="<?php echo $this->get_field_id('button_size')?>"><?php _e('Button Size')?></label>
			<select name="<?php echo $this->get_field_name('button_size')?>" id="<?php echo $this->get_field_id('button_size')?>">
				<option value="small" <?php selected($instance['button_size']=='small'?1:0)?>>Small</option>
				<option value="medium" <?php selected($instance['button_size']=='medium'?1:0)?>>medium</option>
				<option value="custom" <?php selected($instance['button_size']=='custom'?1:0)?>>Custom</option>

			</select>
		  </p>
         <p>
             <label for="<?php echo $this->get_field_id('scale')?>"><?php _e('Phóng')?></label><br/>
             <input type="text" name="<?php echo $this->get_field_name('scale')?>" id="<?php echo $this->get_field_id('scale')?>" value="<?php echo $instance['scale']?>"/><br/>
             <em>Lưu ý: Để phóng mọi kích thước, chọn custom cho "Button Size"</em>
         </p>
		  <p>
				<label for="<?php echo $this->get_field_id('class_socials_button_wrapper')?>">Tên DIV class bao nút chia sẻ</label>
				<input type="text" name="<?php echo $this->get_field_name('class_socials_button_wrapper')?>" id="<?php echo $this->get_field_id('class_socials_button_wrapper')?>" value="<?php echo $instance['class_socials_button_wrapper']?>"/>
		  </p>
		  <p>
			<label for="<?php echo $this->get_field_id('custom_css')?>">CSS</label><br/>
			<textarea rows="10" style="width:100%" name="<?php echo $this->get_field_name('custom_css')?>" id="<?php echo $this->get_field_id('custom_css')?>"><?php echo isset($instance['custom_css'])? $instance['custom_css']: '/*your custom css here*/'?></textarea>
		  </p>

		  <script>
		  HW_SS.change_sharing_service = function(obj){
			  var serv = obj.value;
			  <?php 
			  $objs = array();
			  foreach( $this->services as $serv=>$item) $objs[$serv] = $this->get_field_id($serv).'_settings';
			  echo 'var objs = '.json_encode($objs).';';
			  ?>
			  for(var name in objs){
				  if(serv == name)  jQuery('#'+objs[name]).show();
				  else jQuery('#'+objs[name]).hide();
			  }
			  
		  };
		  //when ready dom, to initial something
		  jQuery(document).ready(function(){
			  $('#<?php echo $this->get_field_id( 'sharing_service' ); ?>').trigger('change');
			  
			  /*pick_socials event*/
			  function get_items_pick_socials(){
				  var options = $('#<?php echo $pick_socials_tag?> option');
				  var values = $.map(options ,function(option) {
						return option.value;
					});
					return values.join(',');
			  }
			  
			  //move item to up
			  $('#<?php echo $this->get_field_id('btn-up')?>').bind('click', function() {
				$('#<?php echo $pick_socials_tag?> option:selected').each( function() {
						var newPos = $('#<?php echo $pick_socials_tag?> option').index(this) - 1;
						if (newPos > -1) {
							$('#<?php echo $pick_socials_tag?> option').eq(newPos).before("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
							$(this).remove();
						}
					});
					$('#<?php echo $this->get_field_id('save_order_items_pick_socials')?>').val(get_items_pick_socials());
				});
				//move item to bottom
				$('#<?php echo $this->get_field_id('btn-down')?>').bind('click', function() {
					var countOptions = $('#<?php echo $pick_socials_tag?> option').size();
					$('#<?php echo $pick_socials_tag?> option:selected').each( function() {
						var newPos = $('#<?php echo $pick_socials_tag?> option').index(this) + 1;
						if (newPos < countOptions) {
							$('#<?php echo $pick_socials_tag?> option').eq(newPos).after("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
							$(this).remove();
						}
					});
					$('#<?php echo $this->get_field_id('save_order_items_pick_socials')?>').val(get_items_pick_socials());
				});
		  });
		  //don't need
        jQuery( document ).ajaxSend(function(event, request, settings) {
          if ( settings.url == "ajax/test.html" ) {	//building
            $( ".log" ).text( "Triggered ajaxSend handler." );
          }

        });
		  </script>
		  <?php
	 }
	 /**
      * Updating widget replacing old instances with new
      * @param $new_instance
      * @param $old_instance
      */
     public function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
         if(! is_numeric($instance['scale'])) $instance['scale'] = 0;
		return $instance;
	 }
}
#add_action( 'widgets_init', 'HW_SocialShare_widget::init' );
add_action( 'hw_widgets_init', 'HW_SocialShare_widget::init' );
