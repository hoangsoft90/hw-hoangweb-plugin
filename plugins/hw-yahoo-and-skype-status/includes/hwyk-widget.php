<?php
//if(class_exists('HW')) HW::load('hw_skin');
#if(!class_exists('HW_SKIN')) include(WP_PLUGIN_DIR.'/hw-skin.php');
/**
 * Class HW_Yahoo_Skype_status
 */
class HW_Yahoo_Skype_status extends WP_Widget {
    /**
     * number of items
     * @var unknown
     */
    public  $_number = 10;
    /**
     * skin object
     * @var unknown
     */
    public $skin;
    /**
     * backup widget instance
     * @var unknown
     */
    private $instance;	//
    /**
     * list supporters
     * @var unknown
     */
    private $services_list = array('yahoo'=>'Yahoo','skype'=>'Skype');	//

    /**
     * constructor
     */
    public function HW_Yahoo_Skype_status() {
        parent::WP_Widget(false, $name = __('Online yahoo skype status','hwyk'),array(
            'description' => __('Display yahoo skype status widget.','hwyk')
        ));

        //you should check if whether this widget actived on frontend or neither maybe you can get widget data by get_option($this->option_name)
        if(!is_admin() && !is_active_widget( false, false, $this->id_base, true)) return;

        //instance skin
        if(class_exists('HW_SKIN')){
            $this->skin = new HW_SKIN($this,HW_YK_PLUGIN_PATH,'hw_yahooskype','yahooskype.php','skins');
            $this->skin->plugin_url = HW_YK_PLUGIN_URL;
            $this->skin->create('phone_icons','resources')->set_resource_path_skins('phone-icons'); //create phones icon skin manager
            $this->skin->create('mail_icons','resources')->set_resource_path_skins('mail-icons'); //create mail icon skin manager
            $this->skin->create('yahoo_icons','resources')->set_resource_path_skins('yahoo-icons'); //create yahoo icon skin manager
            $this->skin->create('skype_icons','resources')->set_resource_path_skins('skype-icons'); //create skype icon skin manager
            //$this->skin->create('avatars','resources')->set_resource_path_skins('avatars'); //avatars image

            $this->skin->get_skin_instance('phone_icons')->enable_external_callback = false;
            $this->skin->get_skin_instance('mail_icons')->enable_external_callback = false;
            $this->skin->get_skin_instance('yahoo_icons')->enable_external_callback = false;
            $this->skin->get_skin_instance('skype_icons')->enable_external_callback = false;
            //$this->skin->get_skin_instance('avatars')->enable_external_callback = false;
            $this->skin->enable_template_engine();
        }
        //ajax callback
        #add_action("wp_ajax_hw_yahooskype_add_contact", array('HW_Yahoo_Skype_status',"add_new"));
        #add_action("wp_ajax_nopriv_hw_yahooskype_add_contact", array('HW_Yahoo_Skype_status',"add_new"));
        add_action('admin_enqueue_scripts',array(&$this,'_admin_enqueue_scripts'));

    }

    /**
     * setup js
     * @hook admin_enqueue_scripts
     */
    public function _admin_enqueue_scripts(){
        wp_enqueue_script('jquery');    //load jquery from wp core
        wp_enqueue_script('yahooskype-js',HW_YK_PLUGIN_URL.('/js/yahooskype.js'),array('jquery'));	//load plugin js file
        //css
        wp_enqueue_style('yk-style',HW_YK_PLUGIN_URL.('/style.css'));
        //jquery ui
        #wp_enqueue_style('jquery-ui-css','http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
        #wp_enqueue_script('jquery-ui','http://code.jquery.com/ui/1.11.4/jquery-ui.js',array('jquery'));
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script("jquery-effects-core");
    }
    /**
     * add new support item holder (nerver use)
     */
    /*public static function add_new() {
        $result=array();
        $excludes=hw_exclude_cats();

       if ( !wp_verify_nonce( $_REQUEST['nonce'], "hw_yahooskype_add_contact")) {
          exit("Access Deny");
       }
        ?>

        <?php
            $result['html']= $html;
            $result['type']='success';

            $result['type'] = "error";
          $result['found'] = 0;


       if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
          //$result = json_encode($result);
          if(isset($result['html'])) echo $result['html'];
       }
       else {
          header("Location: ".$_SERVER["HTTP_REFERER"]);
       }

       die();

    }*/
    /**
     * generate item ui id for each staff
     * @param string $str: suffix string
     */
    private function generate_id($str){
        //$this->get_field_id('');
        return $this->id_base.$this->number.'_yk_item_'.$str;
    }
    /**
     * display widget content on website
     * @param $args
     * @param $instance
     */
    public function widget($args, $instance)
    {
        extract( $args );
        //filter widget title
        $instance['title'] 	 = apply_filters('widget_title', $instance['title'],$instance, $this->id_base ); // Title
        $hash_skin = empty($instance['skin'])? 'default':$instance['skin'];
        //valid instance
        if(!isset($instance['phone_icon'])) $instance['phone_icon'] = '';
        if(!isset($instance['mail_icon'])) $instance['mail_icon'] = '';

        //by hoangweb.com
        if($this->skin)
        {
            $nums = $this->_number;
            //load skin
            $file = $this->skin->get_skin_file($hash_skin);
            if(file_exists($file)) {
                $skin_setting = $this->skin->get_file_skin_setting($hash_skin, false);
                $skin_options = $this->skin->get_file_skin_options($hash_skin, false);
                if(file_exists($skin_setting)){ //get setting file
                    include ($skin_setting);
                }
                //init theme setting
                if(empty($theme)) $theme = array();
                $theme['styles'] = array();
                $theme['scripts'] = array();

                //parse widget data
                $data = HW_Yahoo_Skype_status::parse_onlinesupport_data($instance);
                if(!is_array($data)) $data= array();

                include ($file);
                $this->skin->render_skin_template(array(
                    'current_skin' => HW_SKIN::current(),
                    'before_widget' => $before_widget,
                    'before_title' => $before_title,
                    'after_title' => $after_title,
                    'after_widget' => $after_widget,
                    'instance' => $instance,
                    'data' => $data,
                    'yk' => $this
                ), true, $hash_skin);

                //$this->skin->enqueue_files_from_skin($theme['styles'], $theme['scripts']); //enqueue stuff from skin
                //enqueue stuff from skin
                HW_SKIN::enqueue_skin_assets(array(
                    'instance' => $this->skin,
                    'hash_skin' => $instance['skin'],
                    'skin_file' => $file,
                    'theme_settings' => $theme,
                ));
            }
        }
        else echo 'not found class HW_SKIN.';
    }
    /**
     * widget settings
     * @param $_instance
     */
    public function form($_instance) {
        $this->instance = $_instance;
        $_instance ['title'] = isset($_instance['title'])? $_instance['title'] : __('Support chat', 'hwyk'); //title
        $_instance['addition_text'] = isset($_instance['addition_text'])? $_instance['addition_text'] : '';

        //$active_skin=$instance['skin'];
        if(isset($_instance['enable_demo_data'])) {
            unset($_instance['enable_demo_data']);    //turn of demo data after complete demo data
        }
        //hold all support items
        $yk_items_container_id = $this->get_field_id('support');
        //skin options form
        $skin_setting = isset($_instance['skin_setting'])? $_instance['skin_setting'] : null;   //skin options
        $skin = isset($_instance['skin'])? $_instance['skin'] : '';     //widget skin

        //skin condition
        $skin_condition = isset($_instance['skin_condition'])? $_instance['skin_condition'] : '';
        ?>
        <p id="<?php echo $this->get_field_id('msgs')?>">
            <?php if(isset($_instance['message'])) echo $_instance['message'];?>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id("title"); ?>">
                <strong><?php _e( 'Title' ,'hwyk'); ?>:</strong>
                <input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($_instance["title"]); ?>" />
            </label>
        </p>
        <hr size="2" />
        <!-- by hoangweb -->
        <?php if(class_exists('HW_SKIN')){?>
            <!-- choose skin -->
            <p>
                <label for="<?php echo $this->get_field_id("skin"); ?>">
                    <strong><?php _e( 'Skin', 'hwyk'); ?>:</strong>
                    <?php echo $this->skin->get_skins_select_tag('skin',null,array('class'=>'widefat'),false)?>
                </label>
            </p>
            <p>
                <?php echo $this->skin->get_skin_template_condition_selector('skin_condition', $skin_condition);?>
            </p>
            <p>
                <?php echo $this->skin->prepare_skin_options_fields('skin_setting', $skin_setting, $skin);;?>
            </p>
        <?php }?>
        <!-- choose phone icon -->
        <p>
            <label for="<?php echo $this->get_field_id('phone_icon')?>"><?php _e('Phone icon','hwyk')?></label>
            <?php echo $this->skin->get_skin_instance('phone_icons')->get_skins_select_tag('phone_icon',null,null,HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_LINKS);?>
        </p>
        <!-- choose mail icon -->
        <p>
            <label for="<?php echo $this->get_field_id('mail_icon')?>"><?php _e('Mail icon','hwyk')?></label>
            <?php echo $this->skin->get_skin_instance('mail_icons')->get_skins_select_tag('mail_icon',null,null,HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_LINKS);?>
        </p>
        <!-- yahoo icon alternately -->
        <p>
            <label for="<?php echo $this->get_field_id('yahoo_icon')?>"><?php _e('Yahoo icon','hwyk')?></label>
            <?php echo $this->skin->get_skin_instance('yahoo_icons')->get_skins_select_tag('yahoo_icon',null,null,HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_LINKS);?>
        </p>
        <!-- skype icon alternately -->
        <p>
            <label for="<?php echo $this->get_field_id('skype_icon')?>"><?php _e('Skype icon','hwyk')?></label>
            <?php echo $this->skin->get_skin_instance('skype_icons')->get_skins_select_tag('skype_icon',null,null,HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_LINKS);?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id("addition_text"); ?>">
                <strong><?php _e( 'Other content', 'hwyk'); ?>:</strong><br/>
                <textarea style="width:100%;min-height:100px;" name="<?php echo $this->get_field_name('addition_text'); ?>" id="<?php echo $this->get_field_id("addition_text"); ?>"><?php echo $_instance['addition_text']?></textarea>
            </label>
        </p>
        <p>
            <span><?php _e('Note: You can group nicks by matching phone for those items.', 'hwyk')?></span>
        </p>
        <div id="<?php echo $yk_items_container_id; ?>">
            <?php

            if(!isset($_instance['items'])) $_instance['items'] = array();
            $instance_ = $this->valid_instance($_instance['items']);    //valid instance
            //don't need to sort because it'self fields in sort order.
            //self::sksort($instance_,'pos',true);
            //$instance_ = $this->valid_instance($instance_);    //valid instance again

            foreach($instance_ as $i=>$instance):
                //valid instance again but don;t need
                if(!isset($instance["pos"]) || !is_numeric($instance['pos'] || !$instance['pos'])) $instance["pos"] = $i;
                /*if(!isset($instance['nick'])) $instance['nick'] = __('');
                 if(!isset($instance["is_show_name"])) $instance["is_show_name"] = 1;
                 if(!isset($instance["nick_name"])) $instance["nick_name"] = '';
                 if(!isset($instance["avatar"])) $instance["avatar"] = '';
                 if(!isset($instance["phone"])) $instance["phone"] = '';
                 if(!isset($instance["group"])) $instance["group"] = '';
                 if(!isset($instance["email"])) $instance["email"] = '';
                 if(!isset($instance["nick_type"])) $instance["nick_type"] = '';
                 if(!isset($instance["yahoo_status_type"])) $instance["yahoo_status_type"] = '';
                 //sort
                 if(!isset($instance["pos"])) $instance["pos"] = '0';*/

                ?>
                <script>
                    //init support item
                    if(typeof hwYahooskype !='undefined') {
                        hwYahooskype.create(<?php echo $i?>,'<?php echo $this->id?>','#<?php echo $this->get_field_id('yk_item_'.$i)?>');
                    }
                </script>
                <div class="yk-item-tog ui-state-default" id="<?php echo $this->get_field_id('yk_item_'.$i)?>" data-id="<?php echo $i?>">
                    <div class="tog-bar">
                        <input type="hidden" size="1" class="position" name="<?php echo $this->get_field_name('items')?>[<?php echo $i?>][pos]" value="<?php echo $instance['pos']?>"/>
                        <a class="positionText" ><?php echo $i; ?></a>
                        <?php echo isset($instance["nick"]) && esc_attr($instance["nick"])? esc_attr($instance["nick_name"]).' - <em>'.esc_attr($instance["phone"]).'</em>' : __('Empty', 'hwyk'); ?>

                        <div class="togbar collapse-button closed"></div>
                    </div>
                    <table width="100%" bgcolor="#EEEEEE" class="yk-support" >
                        <tr><td colspan="2">
                            </td></tr>
                        <tr>
                            <td>
                                <label for="<?php echo $this->get_field_id('nick_'.$i); ?>"><strong><?php _e( 'Nick*' ); ?>:</strong></label></td>
                            <td><input id="<?php echo $this->get_field_id('nick_'.$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][nick]" type="text" value="<?php echo esc_attr($instance["nick"]); ?>" />
                                <?php if($instance["nick"]){?>
                                    <script>if(typeof hwYahooskype !='undefined') hwYahooskype.get('<?php echo $this->id?>',<?php echo $i?>).nick = '<?php echo $instance["nick"]?>';</script>
                                <?php }?>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="<?php echo $this->get_field_id("is_show_name_".$i); ?>">
                                    <?php _e('Display name', 'hwyk'); ?>:</label></td>
                            <td>
                                <select id="<?php echo $this->get_field_id("is_show_name_".$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][is_show_name]">
                                    <option value="1" <?php selected( $instance["is_show_name"], "1" ); ?>><?php _e('Yes', 'hwyk')?></option>
                                    <option value="0" <?php selected( $instance["is_show_name"], "0" ); ?>><?php _e('No', 'hwyk')?></option>
                                </select>
                            </td>
                        </tr>
                        <!-- staff basic info -->
                        <tr>
                            <td> <label for="<?php echo $this->get_field_id("nick_name_".$i); ?>"><?php _e( 'Fullname', 'hwyk'); ?>:</label></td>
                            <td><input id="<?php echo $this->get_field_id("nick_name_".$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][nick_name]" type="text" value="<?php echo esc_attr($instance["nick_name"]); ?>" /></td>
                        </tr>
                        <!-- by hoangweb -->
                        <tr>
                            <td> <label for="<?php echo $this->get_field_id("avatar_".$i); ?>"><?php _e( 'Avatar'); ?>:</label></td>
                            <td>
                                <input id="<?php echo $this->get_field_id("avatar_".$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][avatar]" type="text" value="<?php echo esc_attr($instance["avatar"]); ?>" />

                                <?php //echo $this->skin->get_skin_instance('avatars')->get_skins_select_tag('avatar_img',null,array('name'=>$this->get_field_name('items')."[$i][avatar]"),HW_SKIN::DROPDOWN_DDSSLICK_THEME,HW_SKIN::SKIN_LINKS);?>
                            </td>
                        </tr>
                        <tr>
                            <td> <label for="<?php echo $this->get_field_id("phone_".$i); ?>"><strong><?php _e( 'Phone*'); ?></strong>:</label></td>
                            <td><input id="<?php echo $this->get_field_id("phone_".$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][phone]" type="text" value="<?php echo esc_attr($instance["phone"]); ?>" /></td>
                        </tr>
                        <tr>
                            <td><label for="<?php echo $this->get_field_id("email_".$i); ?>"><?php _e( 'Email'); ?>:</label></td>
                            <td><input id="<?php echo $this->get_field_id("email_".$i); ?>" type="text" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][email]" value="<?php echo esc_attr($instance["email"]); ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="<?php echo $this->get_field_id("group_".$i); ?>"><?php _e( 'Group', 'hwyk'); ?>:</label></td>
                            <td><input id="<?php echo $this->get_field_id("group_".$i); ?>" type="text" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][group]" value="<?php echo esc_attr($instance["group"]); ?>"/></td>
                        </tr>
                        <!-- services chooser -->
                        <tr>
                            <td><label for="<?php echo $this->get_field_id("nick_type_".$i); ?>">
                                    <?php _e('Service', 'hwyk'); ?>:</label>

                            </td>
                            <td>
                                <select id="<?php echo $this->get_field_id("nick_type_".$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php  echo $i?>][nick_type]">
                                    <?php foreach($this->services_list as $serv=>$name){?>
                                        <option value="<?php echo $serv?>" <?php selected( $instance["nick_type"], $serv ); ?>><?php echo $name?></option>
                                    <?php }?>
                                </select>
                                <?php if($instance["nick_type"]){?>
                                    <script>
                                        jQuery(function(){
                                            if(typeof hwYahooskype !='undefined')  hwYahooskype.get('<?php echo $this->id?>',<?php echo $i?>).changeService('<?php echo $this->get_field_id("yk_item_".$i); ?>','<?php echo $instance["nick_type"]?>');
                                            //or
                                            //$('#<?php echo $this->get_field_id("nick_type_".$i); ?>').trigger('change');
                                        });
                                        jQuery('#<?php echo $this->get_field_id("nick_type_".$i); ?>').bind('change',function(e){
                                            if(typeof hwYahooskype !='undefined') hwYahooskype.get('<?php echo $this->id?>',<?php echo $i?>).changeService('<?php echo $this->get_field_id("yk_item_".$i); ?>',jQuery(this)[0]);
                                            switch(jQuery(this).val()){
                                                case 'yahoo':
                                                    jQuery('#<?php echo $this->get_field_id("yahoo_status_type_".$i); ?>').trigger('change');	//also show service status by nick
                                                    break;
                                                case 'skype':
                                                    jQuery('#<?php echo $this->get_field_id("skype_status_type_".$i); ?>').trigger('change');	//change skype status icon with test nick
                                                    break;
                                            }
                                        });
                                    </script>
                                <?php }?>
                                <script>
                                    <?php if($instance['nick_type'] == 'yahoo' && isset($instance["yahoo_status_type"])){?>
                                    jQuery(function($){$('#<?php echo $this->get_field_id("yahoo_status_type_".$i); ?>').trigger('change');});
                                    <?php }?>
                                    <?php if($instance['nick_type'] == 'skype' && isset($instance["skype_status_type"])){?>
                                    jQuery(function($){$('#<?php echo $this->get_field_id("skype_status_type_".$i); ?>').trigger('change');});
                                    //jQuery('#<?php echo $this->get_field_id("nick_type_".$i) ?>').trigger('change');
                                    <?php }?>
                                </script>
                            </td>
                        </tr>
                        <!-- yahoo setting -->
                        <tr class="yk-group-services-<?php echo $this->get_field_id("yk_item_".$i); ?>" id="<?php echo $this->get_field_id("yk_item_".$i); ?>_yahoo">
                            <td>Yahoo smile<br /><small><em>Yahoo only</em></small></td>
                            <td>
                                <select onchange="hwYahooskype.get('<?php echo $this->id?>',<?php echo $i?>).pickIconStatus('yahoo',this.value,'#<?php echo $this->get_field_id("displayStatus".$i); ?>')" id="<?php echo $this->get_field_id("yahoo_status_type_".$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][yahoo_status_type]" >
                                    <?php for($k=0;$k<25;$k++):?>
                                        <option value="<?php echo $k; ?>" <?php if($instance["yahoo_status_type"] == $k) echo 'selected="selected"'; ?>><?php echo $k; ?></option>
                                    <?php endfor;?>
                                </select> <?php //_print($instance['replace_yahoo_icon_'.$i])?>
                                <input type="checkbox" name="<?php echo $this->get_field_name('items')?>[<?php echo $i?>][replace_yahoo_icon]" id="<?php echo $this->get_field_id('replace_yahoo_icon_'.$i)?>" <?php checked(isset($instance['replace_yahoo_icon']) && $instance['replace_yahoo_icon'] == 'on'? 1:0)?>/><span><?php _e('Use icon above','hwyk')?></span>
                            </td>
                        </tr>
                        <!-- skype setting -->
                        <tr class="yk-group-services-<?php echo $this->get_field_id("yk_item_".$i); ?>" id="<?php echo $this->get_field_id("yk_item_".$i); ?>_skype">
                            <td>Skype smile<br /><small><em>Skype only</em></small></td>
                            <td>
                                <select onchange="hwYahooskype.get('<?php echo $this->id?>',<?php echo $i?>).pickIconStatus('skype',this.value,'#<?php echo $this->get_field_id("displayStatus".$i); ?>')" id="<?php echo $this->get_field_id("skype_status_type_".$i); ?>" name="<?php echo $this->get_field_name('items'); ?>[<?php echo $i?>][skype_status_type]" >
                                    <?php $sk_stt = array('smallclassic','bigclassic','balloon','smallicon','mediumicon');
                                    foreach($sk_stt as $stt){
                                        ?>
                                        <option value="<?php echo $stt?>" <?php selected(isset($instance["skype_status_type"]) && $instance["skype_status_type"]== $stt? 1:0 ); ?>><?php echo $stt?></option>
                                    <?php }?>
                                </select>
                                <input type="checkbox" name="<?php echo $this->get_field_name('items')?>[<?php echo $i?>][replace_skype_icon]" id="<?php echo $this->get_field_id('replace_skype_icon_'.$i)?>" <?php checked(isset($instance['replace_skype_icon']) && $instance['replace_skype_icon'] == 'on'? 1:0)?>/><span><?php _e('Use icon above','hwyk')?></span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center"><span id="<?php echo $this->get_field_id("displayStatus".$i); ?>" class="yk-status-preview"></span></td>
                        </tr>
                        <tr><td colspan="2"><hr size="1" color="#666666"  /></td></tr>
                    </table>
                </div>

            <?php endforeach;?>
        </div>
        <div>
            <p>
                <label for="<?php echo $this->get_field_id('enable_demo_data')?>">
                    <input type="checkbox" id="<?php echo $this->get_field_id('enable_demo_data')?>" name="<?php echo $this->get_field_name('enable_demo_data')?>" <?php checked((isset($instance['enable_demo_data']) && $instance['enable_demo_data'])=='on'? 1:0)?>/>
                    <span><?php _e('Enable demo data', 'hwyk')?></span>
                </label>
            </p>
        </div>
        <script>
            if(typeof hwYahooskype !='undefined') hwYahooskype.init('#<?php echo $yk_items_container_id; ?>');
            //make sortable items
            jQuery(function($) {
                var sortableEle = $('#<?php echo $yk_items_container_id?>');
                $(sortableEle).sortable({
                    placeholder: "ui-state-highlight",
                    change: function( event, ui ) {

                    },
                    stop: function(event, ui) {
                        console.log("New position: " + ui.item.index());
                        var idsInOrder = hwYahooskype.get_sortable_dataAttr(sortableEle,'data-id');console.log(idsInOrder);
                        //var index = $.inArray("idToLookFor", idsInOrder);
                        hwYahooskype.sortableItems_change('<?php echo $this->id?>',idsInOrder);

                    }
                });
                $( "#<?php echo $yk_items_container_id; ?>" ).disableSelection();
            });
        </script>
        <style>
            #<?php echo $yk_items_container_id?> { list-style-type: none; margin: 0; padding: 0; }
            #<?php echo $yk_items_container_id?> li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; height: 2em; }
            html>body #<?php echo $yk_items_container_id?> li { height: 2em; line-height: 1.2em; }
            #<?php echo $yk_items_container_id?> .ui-state-highlight { height: 2em; line-height: 1.2em; }
        </style>
    <?php }
    /**
     * load sample data for widget
     */
    private function load_demo_data(){
        $skins_data = $this->skin->get_skin_data();
        if(empty($skins_data)) {
            //$this->skin->skins_data = $this->skin->load_skins_data();
            $skins_data = $this->skin->load_skins_data();
            $skins_data = $skins_data['skins'];
        }
        $skins = array_filter(array_keys($skins_data));
        $demo = array(
            'title'=> __('Support chat', 'hwyk'),
            'addition_text'=>'Email: hotro@hoangweb.com',
            'enable_demo_data' => 'on',	#turn it on when load demo data
            //'skin'=>$skins[]
            'message' => 'Demo data refreshed, but this option will be turn off automatically for security.',
            'phone_icon'=>"",
            'mail_icon' => '',
            'yahoo_icon' =>'',
            'skype_icon' => '',
            'items' => array()
        );
        //random skin
        $i=array_rand($skins,1);
        if(isset($skins[$i])) $demo['skin'] = $skins[$i];	//get first skin rather than default

        $sample= array(
            array('nick'=>'quachhoang_2005','phone'=>'01663.930.250','email'=>'hoangsoft90@gmail.com','type'=>'yahoo','status'=>'2'),
            array('nick'=>'oneloveme90','phone'=>'01663.930.250','email'=>'hoangsoft90@gmail.com','type'=>'skype','status'=>'balloon'),
            array('nick'=>'quanghuy_20062001','phone'=>'0910.126.910','email'=>'hotro@hoangweb.com','type'=>'yahoo','status'=>'2'),
            array('nick'=>'huy.freedom','phone'=>'0910.126.910','email'=>'hotro@hoangweb.com','type'=>'skype','status'=>'mediumicon'),
        );
        for($i=1;$i<=5;$i++){
            if(!isset($sample[$i-1])) continue;  //limit 4 items
            $demo['items'][$i]["nick"] = $sample[$i-1]['nick'];
            $demo['items'][$i]["is_show_name"] = 1;
            $demo['items'][$i]["nick_name"]="Tư vấn $i";
            $demo['items'][$i]["avatar"]='';
            $demo['items'][$i]["group"]='';
            $demo['items'][$i]["nick_type"]= $sample[$i-1]['type'];
            $demo['items'][$i]["phone"] = $sample[$i-1]['phone'];
            $demo['items'][$i]["email"] = $sample[$i-1]['email'];
            $demo['items'][$i]["yahoo_status_type"] = isset($sample[$i-1]['status'])? $sample[$i-1]['status']: '2';
            $demo['items'][$i]["skype_status_type"] = isset($sample[$i-1]['status'])? $sample[$i-1]['status']:'balloon';
            $demo['items'][$i]['pos'] = $i;
        }

        return $demo;
    }
    /**
     * valid widget instance
     * @param array $instance: widget instance
     * @return multitype:
     */
    private function valid_instance($instance = array()){
        $num =$this->_number;
        //default values
        $default = array(
            'nick' => '',
            'is_show_name' => 1,
            'nick_name' => '',
            'avatar' => '',
            'group' => '',
            'nick_type' => '',
            'phone' => '',
            'email' => '',
            'yahoo_status_type' => '',
            'skype_status_type' => ''	,
            'pos' =>0
        );
        for($i=0; $i<$num; $i++){
            if(!isset($instance[$i])){
                $instance[$i] = $default;
                continue;
            }
            $instance[$i] = array_merge($default,$instance[$i]);   //fill staff info
        }
        return $instance;
    }

    /**
     * get yahoo status image link
     * @param array $item: staff data info
     */
    public function nick_yahoo_status_link($staff){
        //$id = $staff['id'];
        //get yahoo status image link
        if(isset($staff['replace_yahoo_icon']) && $staff['replace_yahoo_icon'] == 'on') {
            $link = $this->skin->get_skin_link($staff['yahoo_icon']);
        }
        else $link = 'http://opi.yahoo.com/online?u='.$staff['nick'].'&g=m&t='.$staff['yahoo_status_type'];
        return '<a class="chat-yahoo-status" href="ymsgr:sendim?'.$staff["nick"].'" rel="nofollow"><img border="0" class="" src="'.$link.'" alt="'.$staff["nick_name"].'" border="0" style="vertical-align:middle;"/></a>';
    }
    /**
     * get skype status image link
     * @param unknown $staff
     */
    function nick_skype_status_link($staff){
        //$id = $staff['id'];
        //get skype status image link
        if(isset($staff['replace_skype_icon']) && $staff['replace_skype_icon'] == 'on') {
            $link = $this->skin->get_skin_link($staff['skype_icon']);
        }
        else $link = 'http://mystatus.skype.com/'.$staff['skype_status_type'].'/'.$staff['nick'];
        return '<a class="chat-skype-status" href="skype:'.$staff["nick"].'?call" title="Talk with me via Skype" rel="nofollow"><img border="0" class="" src="'.$link.'" alt="'.$staff["nick_name"].'" border="0" style="vertical-align:middle;"/></a>';
    }
    /**
     * parse widget instance
     * @param array $data: staffs data
     * @return data of staff items
     */
    static function parse_onlinesupport_data($data){
        //$nums = $this->_number;
        $result=array();
        $count_colspans=array();
        if(isset($data['items']) && is_array($data['items']))
            foreach($data['items'] as $i=>$instance){
                if(!empty($instance["nick"])){
                    //save some fields into each staff
                    $instance['phone_icon'] = $data['phone_icon'];
                    $instance['mail_icon'] = $data['mail_icon'];
                    $instance['yahoo_icon'] = $data['yahoo_icon'];
                    $instance['skype_icon'] = $data['skype_icon'];

                    $count_colspans[$instance['nick_type']]=1;

                    $instance['id']=$i;
                    if(!isset($result[$instance['phone']])) $result[$instance['phone']]=array_merge(array(),$instance);

                    if(!isset($result[$instance['phone']]['services'])){
                        $result[$instance['phone']]['services']=array();
                    }
                    $result[$instance['phone']]['services'][$instance['nick_type']]=1;
                    $result[$instance['phone']][$instance['nick_type']]=$instance;
                }
            }
        return $result;
    }
    /**
     * sort an array by the key of his sub-array.
     * @param unknown $array
     * @param string $subkey
     * @param string $sort_ascending
     */
    static function sksort(&$array, $subkey="id", $sort_ascending=false) {

        if (count($array))
            $temp_array[key($array)] = array_shift($array);

        foreach($array as $key => $val){
            $offset = 0;
            $found = false;
            foreach($temp_array as $tmp_key => $tmp_val)
            {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                {
                    $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                        array($key => $val),
                        array_slice($temp_array,$offset)
                    );
                    $found = true;
                }
                $offset++;
            }
            if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
        }

        if ($sort_ascending) $array = array_reverse($temp_array);

        else $array = $temp_array;
    }
    /**
     * update widget instance
     * @param $new_instance
     * @param $old_instance
     */
    function update($new_instance, $old_instance) {
        //update demo widget data/settings
        if(isset($new_instance['enable_demo_data']) && $new_instance['enable_demo_data']=='on'){
            return $this->load_demo_data();
        }#end
        if(isset($new_instance['message'])) unset($new_instance['message']);  //remove unuse data
        if(isset($old_instance['message'])) unset($old_instance['message']);  //remove unuse data

        foreach($new_instance['items'] as $i=>&$item){
            if(!empty($item['nick']) && empty($item['nick_name'])){
                $item['nick_name']	=	$item['nick'];
            }
        }
        //save current skin to db for this widget
        $this->skin->save_skin_assets(array(
            'skin' => array(
                'hash_skin' => $new_instance['skin'],
                'hwskin_condition' => $new_instance['skin_condition'],
                'theme_options' => $new_instance['skin_setting']
            ),
            #'object' => 'hw-yahooskype'
        ));
        return $new_instance;
    }
}
//register widget
#add_action( 'widgets_init', create_function('', 'return register_widget("HW_Yahoo_Skype_status");') );
add_action( 'hw_widgets_init', create_function('', 'return register_widget("HW_Yahoo_Skype_status");') );