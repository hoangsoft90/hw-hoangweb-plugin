<?php 

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

define('HW_WEA_EXR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HW_WEA_EXR_PLUGIN_URL', plugins_url('', __FILE__));

//require HW_HOANGWEB plugin
/*register_activation_hook( __FILE__, 'hwtygia_require_plugins_activate' );
function hwtygia_require_plugins_activate(){
    if(function_exists('hw_require_plugins_list_before_active')){
        hw_require_plugins_list_before_active(array(
            'hw-hoangweb/hoangweb.php' => 'hw-hoangweb',

        ));
    }
    else wp_die(__('Sory, please install HW Hoangweb plugin first before active this plugin.', 'hwtg'));

}
*/
//load plugin text domain
function hwtg_wnb_load_textdomain() {
    load_plugin_textdomain( 'hwtg', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
#add_action('plugins_loaded', 'hwtg_wnb_load_textdomain');
add_action('hw_plugins_loaded', 'hwtg_wnb_load_textdomain');

/**
 * @Class hw_widget_weather
 */
class hw_widget_weather extends WP_Widget {
	var $nonce;
    /**
     * @var
     */
    static $yahoo_weather_xml;
    /**
     * @var array
     */
    var $locations = array(
		'hochiminh'=> array('name' => 'TP.HCM', 'zipcode' => '700000'),
		'sonla' => array('name' => 'Sơn la', 'zipcode' => ''),
		'haiphong' => array('name' => 'Hải Phòng', 'zipcode' => ''),
		'ha-noi'=>  array('name' => 'Hà Nội', 'zipcode'=>''),
		'vinh'=>  array('name' => 'Vinh', 'zipcode' => '') ,
		'danang' => array('name' => 'Đà nẵng', 'zipcode' => ''),
		'nhatrang' => array('name' => 'Nha Trang', 'zipcode' => ''),
		'pleiku' => array('name' => 'Pleiku', 'zipcode' => '')
	);

    /**
     * main class constructor
     */
    function __construct() {
          parent::__construct(
          // Base ID of your widget
          'hw_wexr',

          // Widget name will appear in UI
          __('Weather, exchange rate widget', 'hwtg'),

          // Widget description
          array( 'description' => __( 'Add exchange rate widget & weather information', 'hwtg' ), )
          );
		  define('wexr_plugin_url',plugins_url('',__FILE__));
		  
		  add_action('wp_enqueue_scripts',array(&$this,'enqueue_scripts'));
		  add_action( 'admin_enqueue_scripts', array(&$this,'load_custom_wp_admin_style') );
		//for ajax
		add_action("wp_ajax_hwwexr", array(&$this,"widget_wexr"));
		add_action("wp_ajax_nopriv_hwwexr", array(&$this,"widget_wexr"));
		//prepare ajax url
		$this->nonce = wp_create_nonce("my_wexr_nonce");
		
		//sources
		$this->weather_sources = array(
			'yahoo' => 'Yahoo weather',
			'vnexpress' => 'VnExpress'
			);
		$this->giavang_sources = array(
			'sjc' => 'SJC.COM.VN'
		);
		#self::$yahoo_weather_xml =  plugins_url('weather.bylocation.xml',__FILE__);
		self::$yahoo_weather_xml =  'http://github.com/yql/yql-tables/raw/master/weather/weather.bylocation.xml';
     }
	 /**
	  * call by ajax
	  */
	 function widget_wexr(){
		 if (!wp_verify_nonce( $_REQUEST['nonce'], "my_wexr_nonce")) {
			  exit("No naughty business please");
		   }
		//get specific location to get weather
		$id = isset($_GET['id'])?$_GET['id'] : ' hochiminh';
		$source = $this->get_weather_option('source');
		if($source == 'yahoo'):
		$weather = self::get_yahoo_weather_by_location($id.',vietnam');
        if(!isset($weather->condition->temp))    return;

		$temp = str_split($weather->condition->temp);
		
		ob_start();
		?>
		<table cellpadding="0" cellspacing="0">
			<tr><td valign="middle">
			<img src="<?php echo $weather->temp_image?>" align="left" />
			<img src="<?php echo wexr_plugin_url?>/images/<?php echo $temp[0]; ?>.gif" align="left" />
			<img src="<?php echo wexr_plugin_url?>/images/<?php echo $temp[1]; ?>.gif" align="left" />
			<img src="<?php echo wexr_plugin_url?>/images/c.gif" />
			</td>
			</tr>
			<tr>
			<td>
			<?php echo __('Humidity','hwtg').':'.$weather->atmosphere.'<br/>'.__('Wind speed', 'hwtg').':'.$weather->wind; ?>
			</td>
			</tr>
		</table>
		<?php
		$html = ob_get_contents();
		ob_clean();
		endif;
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			  //$result = json_encode($result);
			  if(isset($html)) echo $html;
		   }
		   else {
			  header("Location: ".$_SERVER["HTTP_REFERER"]);
		   }

		   die();
	 }

    /**
     * enqueue scripts
     */
    function enqueue_scripts(){
		wp_enqueue_style('wexr-style', plugins_url('style.css',__FILE__));
        wp_enqueue_script('jquery');    //load jquery from wp core
		wp_enqueue_script('wexr-lionbars',  plugins_url('js/jquery.lionbars.0.3.js',__FILE__), array('jquery'));
		wp_enqueue_script('wexr-ajax', plugins_url('js/ajax.js',__FILE__), array('jquery'));
		wp_localize_script( 'wexr-ajax', 'myAjax', array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'wexr_plugin_url' => plugins_url('',__FILE__)
			));
		
	}
	function load_custom_wp_admin_style(){
		wp_enqueue_script('wexr-admin', plugins_url('js/admin-js.js',__FILE__), array('jquery'));
		wp_enqueue_style('wexr-style', plugins_url('style.css',__FILE__));
	}
	/**
	get widget instance
	*/
	private function get_instance(){
		//get current widget instance
		if(!isset($this->instance) && $this->number){
			$options = get_option($this->option_name);
			$instance = $options[$this->number];
			$this->instance = $instance;
		}
	}
	/**
	get gold price
	*/
	private function get_gold_price(){
		$links = array(
			'sjc' => 'http://www.sjc.com.vn/xml/tygiavang.xml'
		);
		$source = $this->get_gold_price_option('source');
		if(!isset($links[$source])) return;
		$content = @file_get_contents($links[$source]);
		$p = xml_parser_create();
		xml_parse_into_struct($p, $content, $goldrates);
		$data = array();
		$cur_city = '';
		foreach($goldrates as $xml){
			if($xml['tag'] == 'CITY' && $xml['type'] == 'open' && isset($xml['attributes']['NAME'])){
				$cur_city = sanitize_title($xml['attributes']['NAME']);
				if(!isset($data[$cur_city])) $data[$cur_city] = array('NAME' => $xml['attributes']['NAME']);
			}
			if($xml['tag'] == 'ITEM' && $xml['type'] == 'complete' && isset($xml['attributes'])){
				$data[$cur_city] = array_merge($data[$cur_city], $xml['attributes']);
			}
		}
		Return $data;
	}
	/**
	* get exchange rates
	*/
	private function get_exchange_rate(){
		$this->get_instance();	//fetch data
		$links = array(
			'vietcombank' => 'http://vietcombank.com.vn/ExchangeRates/ExrateXML.aspx'
		);
		$dir = plugin_dir_path(__FILE__).'cache/';
		if(!is_dir($dir)) mkdir($dir,0755,true);
		$Link = $dir.'ExchangeRates.xml';
		$source = $this->get_exchange_rate_option('source');
		if(!isset($links[$source])) return;
		$Link2 = $links[$source];
		$content = @file_get_contents($Link2);
		if($content==''){
			$content = @file_get_contents($Link);
		}else{
			copy($Link2,$Link);
		}
		if($content!='' and preg_match_all('/Exrate CurrencyCode="(.*)" CurrencyName="(.*)" Buy="(.*)" Transfer="(.*)" Sell="(.*)"/',$content,$matches) and count($matches)>0){
			$exchange_rates=array(
							'USD'=>array()
							,'EUR'=>array()
							,'GBP'=>array()
							,'HKD'=>array()
							,'JPY'=>array()
							,'CHF'=>array()
							,'AUD'=>array()
							,'CAD'=>array()
							,'SGD'=>array()
							,'THB'=>array()
			);
			foreach($matches[1] as $key=>$value){
				if(isset($exchange_rates[$value])){
					$exchange_rates[$value]=array(
									'id'=>$value
									,'name'=>$matches[2][$key]
									,'buy'=>$matches[3][$key]
									,'transfer'=>$matches[4][$key]
									,'sell'=>$matches[5][$key]
					);
				}
			}
			Return $exchange_rates;
		}
	}
	/**
	 * get weather information from yahoo
	 * @param $location: format in 'city,country'
	*/
	static function get_yahoo_weather_by_location($location){
		$BASE_URL = "http://query.yahooapis.com/v1/public/yql";
		$yql_query = 'use "'.self::$yahoo_weather_xml.'" as we;select * from we where location="'.$location.'" and unit="c"';
		$yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json&u=v&diagnostics=false&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
		// Make call with cURL
		$session = curl_init($yql_query_url);
		curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
		$json = curl_exec($session);
		
		// Convert JSON to PHP object
		$phpObj =  json_decode($json);
		
		if(isset($phpObj->query->results->weather->rss->channel)){
			$data = $phpObj->query->results->weather->rss->channel;
			$atmosphere = $data->atmosphere->humidity.' %';
			$wind = $data->wind->speed.' km/h';
			$condition = $data->item->condition;
			#temp
			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $data->item->description, $matches);
			$first_img = $matches [1] [0];
			return (object)array('atmosphere' => $atmosphere,'wind' =>$wind,'temp_image' => $first_img, 'condition' => $condition);
		}
	}
	/**
	 * get options
	 */
	function get_options($opt='',$key=''){
		$this->get_instance();
		$c_opts = $this->instance['show_opts'];
		if($opt && $key && isset($c_opts[$key.'_'.$opt])) return $c_opts[$key.'_'.$opt];
		return $c_opts;
	}

    /**
     * @param $opt
     * @return mixed
     */
    function get_weather_option($opt){
		return $this->get_options($opt,'c_weather');
	}

    /**
     * @param $opt
     * @return mixed
     */
    function get_exchange_rate_option($opt){
		return $this->get_options($opt,'c_exr');
	}

    /**
     * @param $opt
     * @return mixed
     */
    function get_gold_price_option($opt){
		return $this->get_options($opt,'c_giavang');
	}

     /**
      * // Creating widget front-end
      * This is where the action happens
      */
     public function widget( $args, $instance ) 
	 {
          $title = apply_filters( 'widget_title', $instance['title'] ,$instance, $this->id_base);
		  #$def_location = $instance['def_location'];
          // before and after widget arguments are defined by themes
          echo $args['before_widget'];
          if ( ! empty( $title ) )
          echo $args['before_title'] . $title . $args['after_title'];
			$show_opts =  $instance['show_opts'];
			/*display weather*/
			if(isset($show_opts['c_weather']) && $show_opts['c_weather']=='on'){
				$def_location = $this->get_weather_option('def_location');
				?>
				<div class="exr_weather">
				<div><img title="<?php _e('Update weather, exchange rates, gold prices from vnexpress', 'hwtg')?>"  src="<?php echo wexr_plugin_url?>/images/cloud.png" border="0"  width="25px" style="vertical-align:middle" alt="Cập nhật thời tiết" /> <b><?php _e('The weather', 'hwtg');?></b></div>
				
             <form name="form1" method="post">
				   <select name="select" class="wexr_pick_weather" id="wexr_pick_weather" onChange="wexr_Weather(this.value,'<?php echo $this->nonce?>','<?php echo $this->get_field_id('exr_display_Weather')?>');">
					 <?php foreach($this->locations as $loc => $text){?>
					 <option value="<?php echo $loc?>" <?php selected($loc,$def_location)?>><?php echo $text['name']?></option>
					 <?php }?>
					</select>
                </form>
                <div id="<?php echo $this->get_field_id('exr_display_Weather')?>"><script>wexr_Weather('<?php echo $def_location?>','<?php echo $this->nonce?>','<?php echo $this->get_field_id('exr_display_Weather')?>')</script></div>
                </div>
				<?php
			}
			/*display giavang*/
			if(isset($show_opts['c_giavang']) && $show_opts['c_giavang']=='on'){
				$goldprices = $this->get_gold_price();
				?>
			<table border="0" cellpadding="0" cellspacing="0" width="95%" class="exr_giavang">
         <tr><td colspan="2">
         <img title="<?php _e('Update weather, exchange rates, gold prices from vnexpress', 'hwtg')?>"  border="0" src="<?php echo wexr_plugin_url?>/images/money.png" style="vertical-align:middle" width="25px" alt="<?php _e('Gold price', 'hwtg')?>" />  
               <b>Giá vàng</b>
          </td></tr>
         <tr><td>
        <table class="bor_ctd" border="0"  cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff">
            <tr>
				<td class="ctd" align="center"  bgcolor="#ffffff"><?php _e('Location','hwtg')?></td>
				<td class="ctd"  align="center"  bgcolor="#ffffff"><?php _e('Item','hwtg')?></td>
				<td class="ctd"  align="center"  bgcolor="#ffffff"><?php _e('Buy', 'hwtg')?></td>
				<td class="ctd"  align="center"  bgcolor="#ffffff"><?php _e('Sell', 'hwtg')?></td>
			</tr>
			<?php
			if(is_array($goldprices)):
				foreach($goldprices as $giavang)
				{
			?>
			<tr>
				<td class="ctd"  bgcolor="#ffffff" align="center"><?php echo $giavang['NAME'];?></td>
				<td class="ctd" align="center"  bgcolor="#ffffff"><?php echo $giavang['TYPE'];?></td>
				<td class="ctd" align="center"  bgcolor="#ffffff"><?php echo $giavang['BUY'];?></td>
				<td align="center" class="ctd"  bgcolor="#ffffff"><?php echo $giavang['SELL'];?></td>
			</tr>
			<?php
			}
			else:
			echo '<tr><td>'.__('fetching error Resource', 'hwtg').'</td></tr>';
			endif;
			?>
             </table>
            </td></tr>
       </table>	
				<?php
			}
			/*display exchange rate*/
			if(isset($show_opts['c_exr']) && $show_opts['c_exr']=='on'){
				$exrs = $this->get_exchange_rate();
				$exr_fields = $this->get_exchange_rate_option('fields');
				if(!is_array($exr_fields)) $exr_fields = array();
				?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="exr_exchange_rate">
				<tr>
					<td colspan="2">
						   <img src="<?php echo wexr_plugin_url?>/images/circle-chart.png" style="vertical-align:middle" border="0" title="<?php _e('Update weather, exchange rates, gold prices from vnexpress', 'hwtg')?>"  alt="<?php _e('Exchange rate','hwtg')?>" />
					  <b><?php _e('Exchange rate','hwtg')?></b></td>
				</tr>
				<tr>
					<td colspan="2" width="95%">
						<div id="AdTyGiaLoc">
							<div class="demo tygia_container">
								<table class="bor_ctd" border="0"  cellpadding="0" cellspacing="0" width="95%" bgcolor="#ffffff">
									<tr>
										<td><?php _e('Currency', 'hwtg')?></td>
										<?php if(($exr_fields && in_array('buy',$exr_fields))|| !isset($show_opts['c_exr_fields'])){?><td><?php _e('Buy', 'hwtg')?></td><?php }?>
										<?php if(($exr_fields && in_array('transfer',$exr_fields)) || !$exr_fields){?><td><?php  _e('Transfer', 'hwtg')?></td><?php }?>
										<?php if(($exr_fields && in_array('sell',$exr_fields)) || !$exr_fields){?><td><?php _e('Sell', 'hwtg')?></td><?php }?>
									</tr>
							<?php
							if(is_array($exrs)):
							foreach($exrs as  $currency =>$tygia)
							{
						?>
							<tr>
							<td class="ctd" bgcolor="#ffffff">&nbsp;&nbsp;<?php echo $tygia['name'];?></td>
							<?php if(($exr_fields && in_array('buy',$exr_fields))
									|| !isset($show_opts['c_exr_fields'])){?>
							<td class="ctd" bgcolor="#ffffff">&nbsp;<?php echo number_format($tygia['buy']);?></td>
							<?php }?>
							<?php if(($exr_fields && in_array('transfer',$exr_fields))
									|| !$exr_fields){?>
							<td class="ctd" bgcolor="#ffffff">&nbsp;<?php echo number_format($tygia['transfer']);?></td>
							<?php }?>
							<?php if(($exr_fields && in_array('sell',$exr_fields))
									|| !$exr_fields){?>
							<td class="ctd" bgcolor="#ffffff">&nbsp;<?php echo number_format($tygia['sell']);?></td>
							<?php }?>
							</tr>
							<?php
							}
							else:
								echo '<tr><td>'.__('fetching error Resource', 'hwtg').'</td></tr>';
							endif;
							?>
							</table></div>
						</div>
					</td>
				</tr>
			</table> 
			<script>
			<?php if(isset($show_opts['c_exr_opts']['allow_scrollbar'])){?>
			$(function($)
			{
				$('.tygia_container').lionbars();	/*init scrollbar to tygia table*/
			});
			<?php }?>
			</script>
			<style>
			.tygia_container{
			background: white;
				 float: left;
				 <?php if($show_opts['c_exr_opts']['width']) echo 'width: '.$show_opts['c_exr_opts']['width'].'px;';?>
				 <?php if($show_opts['c_exr_opts']['height']) echo 'max-height: '.$show_opts['c_exr_opts']['height'].'px;';?>
				 padding-right:0px;
				 color: #222;
				 font: 12px/18px helvetica, tahoma, sans-serif; overflow: auto; 
			}
			</style>
				<?php
			}
          
          echo $args['after_widget'];
     }
         
     /**
      * Widget Backend
      * @param $instance
      */
     public function form( $instance ) {
          if ( isset( $instance[ 'title' ] ) ) {	//widget title
               $title = $instance[ 'title' ];
          }
          else {
               $title = __( 'Weather, exchange rate widget', 'hwtg' );
          }
		  $c_opts = isset($instance['show_opts'])? $instance['show_opts'] : array(
			'c_weather'=>1,
			'c_exr'=>1,
			'c_giavang'=>1
			);
		  
		  $display_opts = array(
			'c_weather'=>__('The weather', 'hwtg'),
			'c_exr'=>__('Exchange rate', 'hwtg'),
			'c_giavang'=>__('Gold price', 'hwtg')
			);
          // Widget admin form
		  
          ?>
          <p>
          <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title' ,'hwtg'); ?>:</label>
          <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
          </p>
		  <p>
			
		  </p>
		  <p>
			<label><?php _e( 'Display:' ,'hwtg'); ?></label>
			<?php 
			foreach($display_opts as $name=>$text){
				$c_val = isset($c_opts[$name])? $c_opts[$name] : '0';
				?>
				
          <p><input class="widefat" id="<?php echo $this->get_field_id( 'show_opts').$name; ?>" name="<?php echo $this->get_field_name( 'show_opts'); ?>[<?php echo $name?>]" type="checkbox" <?php checked(isset($c_opts[$name])? 1:0);?> onclick="hw_wexr.showMore(this,'<?php echo $name?>','<?php echo $this->get_field_id( $name)?>')"/>
		  <label for="<?php echo $this->get_field_id('show_opts').( $name ); ?>"><?php _e( $text.':' ); ?></label>
			<?php 
				switch(true){
					case  $name == 'c_exr' /*&& isset($c_opts[$name])*/:
						$cols= array('buy'=> __('Buy', 'hwtg') ,'transfer' => __('Transfer', 'hwtg'), 'sell'=> __('Sell', 'hwtg'));
						if(!isset($c_opts[$name.'_fields'])) $c_opts[$name.'_fields'] = array_keys($cols);	//enable all fields at first
					?>
					
					<div id="<?php echo $this->get_field_id( 'c_exr')?>" class="<?php echo !isset($c_opts[$name])? 'wexr-hidden':''?>">
					<p>
						<label for="<?php echo $this->get_field_id($name.'_source')?>"><?php _e('Source','hwtg')?></label>
						<select name="<?php echo $this->get_field_name( 'show_opts'); ?>[<?php echo $name.'_source'?>]" id="<?php echo $this->get_field_id($name.'_source')?>" class="widefat">
							<option value="vietcombank" <?php selected(isset($c_opts[$name.'_source']) && $c_opts[$name.'_source'] == 'vietcombank'? 1:0)?>>Vietcombank</option>
						</select>
					</p>
					<p>
						<label for="<?php echo $this->get_field_id( $name.'_fields');?>"><?php _e('Fields', 'hwtg')?></label>
						<select multiple="multiple" id="<?php echo $this->get_field_id( $name.'_fields'); ?>" name="<?php echo $this->get_field_name( 'show_opts'); ?>[<?php echo $name.'_fields'?>][]">
						<?php foreach($cols as $col => $disp){
							$selected = is_array($c_opts[$name.'_fields']) && in_array($col,$c_opts[$name.'_fields'])? 1 : 0;
							?>
						<option value="<?php echo $col?>" <?php selected($selected)?>><?php echo $disp?></option>
						<?php }?>
						</select>
				 	</p>	
						<p>
							<label for=""><?php _e('Enable scrollbar', 'hwtg')?></label>
							<input type="checkbox" name="<?php echo $this->get_field_name( 'show_opts').'['.$name.'_opts][allow_scrollbar]'; ?>" <?php checked(isset($c_opts[$name.'_opts']['allow_scrollbar'])? 1:0);?>/>
						</p>
						<p>
							<label ><?php _e('Width')?></label>
							<input type="text" size="5" name="<?php echo $this->get_field_name( 'show_opts').'['.$name.'_opts][width]'; ?>" value="<?php echo isset($c_opts[$name.'_opts']['width'])? $c_opts[$name.'_opts']['width'] : ''?>"/>px
						</p>
						<p>
							<label ><?php _e('Height')?></label>
							<input type="text" size="5" name="<?php echo $this->get_field_name( 'show_opts').'['.$name.'_opts][height]'; ?>" value="<?php echo isset($c_opts[$name.'_opts']['height'])? $c_opts[$name.'_opts']['height'] : ''?>"/>px
						</p>
						<hr/>
					</div>	
					<?php
					break;
					case $name == 'c_weather':
						?>
						<div id="<?php echo $this->get_field_id( 'c_weather')?>" class="<?php echo !isset($c_opts[$name])? 'wexr-hidden':''?>">
							<p>
							<label for="<?php echo $this->get_field_id($name.'_source')?>"><?php _e('Source', 'hwtg')?></label>
							<select name="<?php echo $this->get_field_name( 'show_opts'); ?>[<?php echo $name.'_source'?>]" id="<?php echo $this->get_field_id($name.'_source')?>" class="widefat">
								<?php foreach($this->weather_sources as $id => $text){
									?>
								<option value="<?php echo $id?>" <?php selected(isset($c_opts[$name.'_source']) && $c_opts[$name.'_source'] == $id? 1:0)?>><?php echo $text?></option>
								<?php }?>
							</select>
							</p>
							<p>
								<label for="<?php echo $this->get_field_id($name.'_def_location')?>"><?php _e( 'Default location' ,'hwtg'); ?></label>
								<select id="<?php echo $this->get_field_id( $name.'_def_location' ); ?>" name="<?php echo $this->get_field_name( 'show_opts' ); ?>[<?php echo $name.'_def_location'?>]">
									<option value="auto_detect" disabled><?php _e('Manual', 'hwtg')?></option>
									<?php foreach($this->locations as $loc => $text){?>
									 <option value="<?php echo $loc?>" <?php selected(isset($c_opts[$name.'_def_location']) && $c_opts[$name.'_def_location'] == $loc ? 1:0)?>><?php echo $text['name']?></option>
									 <?php }?>
								</select>
						  </p>
							<hr/>
						</div>
						<?php
					break;
					case $name == 'c_giavang':
						?>
						<div id="<?php echo $this->get_field_id( 'c_giavang')?>" class="<?php echo !isset($c_opts[$name])? 'wexr-hidden':''?>">
							<p>
							<label for="<?php echo $this->get_field_id($name.'_source')?>"><?php _e('Source', 'hwtg')?></label>
							<select name="<?php echo $this->get_field_name( 'show_opts'); ?>[<?php echo $name.'_source'?>]" id="<?php echo $this->get_field_id($name.'_source')?>" class="widefat">
								<?php foreach($this->giavang_sources as $id => $text){
									?>
								<option value="<?php echo $id?>" <?php selected(isset($c_opts[$name.'_source']) && $c_opts[$name.'_source'] == $id? 1:0)?>><?php echo $text?></option>
								<?php }?>
							</select>
							</p><hr/>
						</div>
						<?php
					break;
				}
			?>
		  </p>
				<?php
			}
			?>
		  </p>
		  
		  
          <?php
     }
    
     /**
      * Updating widget replacing old instances with new
      * @param $new_instance
      * @param $old_instance
      */
     public function update( $new_instance, $old_instance ) {
          $instance = array();
          $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
          
			$instance['show_opts'] = ( ! empty( $new_instance['show_opts'] ) ) ?  $new_instance['show_opts'] : array();
		if(isset($instance['show_opts']['c_exr_opts']['width'])) 
			$instance['show_opts']['c_exr_opts']['width'] = (float)$instance['show_opts']['c_exr_opts']['width'];
		if(isset($instance['show_opts']['c_exr_opts']['height'])) 
			$instance['show_opts']['c_exr_opts']['height'] = (float)$instance['show_opts']['c_exr_opts']['height'];
          return $instance;
     }
	
}
/**
 * Register and load the widget
 */
function hw_wexr_load_widget() {
     register_widget( 'hw_widget_weather' );
}
#add_action( 'widgets_init', 'hw_wexr_load_widget' );
add_action( 'hw_widgets_init', 'hw_wexr_load_widget' );
?>