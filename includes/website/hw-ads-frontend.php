<?php
#/root>includes/website/hw-settings-implementation.php
/**
 * Class NHP_Options_ads_Frontend
 */
class NHP_Options_ads_Frontend extends NHP_Options_ads {
    /**
     * Main class constructor
     */
    function __construct() {
        add_action('wp_footer', array($this, '_hw_footer_setup_flra'));
        #add_action('init', array($this, '_init'));
        add_action('wp_enqueue_scripts', array($this, '_wp_enqueue_scripts'));

    }

    /**
     * @hook wp_enqueue_scripts
     */
    public function _wp_enqueue_scripts() {
        //to add new library
        HW_Libraries::registers_jquery_libs('visible', array(
            'scripts' => array(
                'jquery.visible.min.js' => array(
                    'file' => 'jquery.visible.min.js',      //relative path
                    'depends' => array('jquery'),
                    'required' => true
                )
            ),

        ));
        HW_Libraries::enqueue_jquery_libs('visible');
        HW_Libraries::get('sticky')->enqueue_scripts('jquery.sticky.js');   //sticky js
    }
    protected function render_fixed2top_ads() {

    }
    /**
     * display ads on the website
     */
    public function _hw_footer_setup_flra(){
        if(function_exists('hwlib_load_library')) {
            $mobile_detect = hwlib_load_library('HW_Mobile_Detect');    //
            if($mobile_detect->object->isMobile() && !hw_option('ads_active_mobile')) {
                return ;    //do not show ads on mobile device
            }
        }
        //show ads
        if(hw_option('enable_flra')){
            $id = rand();
            $id_left_ad = 'hw_divAdLeft_'.$id;
            $id_right_ad = 'hw_divAdRight_'.$id;

            $effect = hw_option('ads_effects');
            $lad_width = hw_option('lad_width',150);    //left ad width
            $rad_width = hw_option('rad_width', 150);    //right ad width


            $mcontent_div = hw_option('mcontent_div');  //div main wrapper
            #$mcontent_width = hw_option('mcontent_width', 1000);
            ?>
            <style>
                #<?php echo $id_right_ad?>, #<?php echo $id_left_ad?> {
                    display: block;z-index: 1e+19;position: absolute;
                }
                .hw-side-ads{
                    position: absolute !important;
                    width: <?Php echo $lad_width?>px;
                    display: none;
                    top:0px;
                }
                <?php if($effect == 'fixed_to_top'){?>
                <?php if($mcontent_div){
                echo "
                    {$mcontent_div} {position:relative !important;}
                ";
                }
                ?>
                #<?php echo $id_right_ad?> .hw-ad-right{
                    <?php if($rad_width){
                        echo "width: {$rad_width}px";
                    }
                    ?>
                }
                #<?php echo $id_left_ad?> .hw-ad-left{
                    left:-<?php echo $lad_width+10?>px;

                    <?php if($rad_width){
                        echo "width: {$lad_width}px";
                    }
                    ?>
                }
                <?php }?>
            </style>
            <!-- banner truot 2 ben -->
            <div id="<?php echo $id_right_ad?>" style=""><!-- left: 286.5px; -->
                <div class="hw-side-ads hw-ad-right"><?php echo hw_option('ad_left')?></div>

            </div>
            <div id="<?php echo $id_left_ad?>" style=""><!-- left: 1486.5px; -->
                <div class="hw-side-ads hw-ad-left"><?php echo hw_option('ad_right')?></div>
            </div>
            <script>
                var hw_ads = {
                    /**
                     * return ads setting
                     */
                    hw_ads_settings: function() {
                        var mcontent_div = '<?Php echo hw_option('mcontent_div', '');?>';
                        var MainContentW = 0;    //for default
                        var LeftBannerW = <?php echo hw_option('lad_width',150) ?>;
                        var RightBannerW = <?php echo hw_option('rad_width', 150) ?>;
                        var LeftAdjust = 0;
                        var RightAdjust = 0;
                        var TopAdjust = <?php echo hw_option('top_adjust', 20)?>;
                        return {
                            mcontent_div: mcontent_div,
                            MainContentW: MainContentW,
                            LeftBannerW:LeftBannerW,
                            RightBannerW:RightBannerW,
                            LeftAdjust: LeftAdjust,
                            RightAdjust: RightAdjust,
                            TopAdjust : TopAdjust
                        }
                    },
                    /**
                     * This will check if the element is entirely visible in the current viewport
                     */
                    elementInViewport:function (el) {
                        if(typeof el =='string') el = jQuery(el).get(0);
                        return jQuery(el).visible();
                        /*
                        var top = el.offsetTop;
                        var left = el.offsetLeft;
                        var width = el.offsetWidth;
                        var height = el.offsetHeight;

                        while(el.offsetParent) {
                            el = el.offsetParent;
                            top += el.offsetTop;
                            left += el.offsetLeft;
                        }

                        return (
                            top >= window.pageYOffset &&
                                left >= window.pageXOffset &&
                                (top + height) <= (window.pageYOffset + window.innerHeight) &&
                                (left + width) <= (window.pageXOffset + window.innerWidth)
                            );
                            */
                    },
                    /**
                     * You could modify this simply to determine if any part of the element is visible in the viewport:
                     * @param el
                     * @returns {boolean}
                     */
                    elementInViewport2: function(el) {
                        if(typeof el =='string') el = jQuery(el).get(0);
                        var top = el.offsetTop;
                        var left = el.offsetLeft;
                        var width = el.offsetWidth;
                        var height = el.offsetHeight;

                        while(el.offsetParent) {
                            el = el.offsetParent;
                            top += el.offsetTop;
                            left += el.offsetLeft;
                        }

                        return (
                            top < (window.pageYOffset + window.innerHeight) &&
                                left < (window.pageXOffset + window.innerWidth) &&
                                (top + height) > window.pageYOffset &&
                                (left + width) > window.pageXOffset
                            );
                    }
                };
                jQuery(document).ready(function(){
                    hw_ads.ads_settings = hw_ads.hw_ads_settings();
                    //update MainContentW
                    if(hw_ads.ads_settings.mcontent_div && jQuery(hw_ads.ads_settings.mcontent_div).length) {
                        hw_ads.ads_settings.MainContentW = jQuery(hw_ads.ads_settings.mcontent_div).width();
                        hw_ads.ads_settings.ads_container = jQuery(hw_ads.ads_settings.mcontent_div);
                    }
                    else {
                        var ads_container = jQuery('<div/>').addClass('hw-ads-container').css({ position: 'absolute',width:'100%',margin:'auto',top: hw_ads.ads_settings.TopAdjust + 'px'});
                        ads_container.appendTo('body:eq(0)');
                        hw_ads.ads_settings.MainContentW = ads_container.width();   //update main content width
                        hw_ads.ads_settings.ads_container = ads_container;
                    }
                });
            </script>
            <?php
            //following scrollbar
            if($effect == 'follow_scrollbar'){
                ?>
            <script>

            (function(){
                /**
                 * float top div
                 */
                function hw_FloatTopDiv()
                {
                    startLX = ((document.body.clientWidth - hw_ads.ads_settings.MainContentW)/2)- hw_ads.ads_settings.LeftBannerW-hw_ads.ads_settings.LeftAdjust , startLY = hw_ads.ads_settings.TopAdjust+80;
                    startRX = ((document.body.clientWidth - hw_ads.ads_settings.MainContentW)/2)+ hw_ads.ads_settings.MainContentW+ hw_ads.ads_settings.RightAdjust , startRY = hw_ads.ads_settings.TopAdjust+80;
                    var d = document;
                    function ml(id)
                    {
                        var el=d.getElementById?d.getElementById(id):d.all?d.all[id]:d.layers[id];
                        el.sP=function(x,y){this.style.left=x + 'px';this.style.top=y + 'px';};
                        el.x = startRX;
                        el.y = startRY;
                        return el;
                    }
                    function m2(id)
                    {
                        var e2=d.getElementById?d.getElementById(id):d.all?d.all[id]:d.layers[id];
                        e2.sP=function(x,y){this.style.left=x + 'px';this.style.top=y + 'px';};
                        e2.x = startLX;
                        e2.y = startLY;
                        return e2;
                    }
                    window.stayTopLeft=function()
                    {
                        if (document.documentElement && document.documentElement.scrollTop)
                            var pY =  document.documentElement.scrollTop;
                        else if (document.body)
                            var pY =  document.body.scrollTop;
                        if (document.body.scrollTop > 30){startLY = 3;startRY = 3;} else {
                            startLY = hw_ads.ads_settings.TopAdjust;
                            startRY = hw_ads.ads_settings.TopAdjust;
                        };
                        ftlObj.y += (pY+startRY-ftlObj.y)/16;
                        ftlObj.sP(ftlObj.x, ftlObj.y);
                        ftlObj2.y += (pY+startLY-ftlObj2.y)/16;
                        ftlObj2.sP(ftlObj2.x, ftlObj2.y);
                        setTimeout("stayTopLeft()", 1);
                    }
                    ftlObj = ml("<?Php echo $id_right_ad?>");
                    //stayTopLeft();
                    ftlObj2 = m2("<?php echo $id_left_ad?>");
                    stayTopLeft();
                }

                /**
                 * show ads div
                 */
                function hw_ShowAdDiv()
                {
                    var objAdDivRight = document.getElementById("<?php echo $id_right_ad?>");
                    var objAdDivLeft = document.getElementById("<?php echo $id_left_ad?>");
                    if (document.body.clientWidth < hw_ads.ads_settings.MainContentW)
                    {
                        objAdDivRight.style.display = "none";
                        objAdDivLeft.style.display = "none";
                    }
                    else
                    {
                        objAdDivRight.style.display = "block";
                        objAdDivLeft.style.display = "block";
                        hw_FloatTopDiv();
                    }
                }

                //events
                jQuery(document).ready(function(){
                    //show ad div
                    hw_ShowAdDiv();
                    window.onresize=hw_ShowAdDiv;
                    //show ads
                    jQuery(".hw-side-ads").show();
                });

            })();

            </script>
                <?php }
            //fixed to top
            elseif($effect == 'fixed_to_top'){
                HW_Libraries::enqueue_jquery_libs('sticky');
                ?>
                <script>
                    jQuery(document).ready(function(){
                        var ads_container= null,
                            ad_right =hw_ads.ads_settings.MainContentW+10,
                            ad_left = hw_ads.ads_settings.LeftBannerW+10;
                        //setting
                        /*if(!hw_ads.elementInViewport('#<?php echo $id_right_ad?>')) {

                        }*/
                        hw_ads.ads_settings.ads_container.prepend(jQuery("#<?php echo $id_left_ad?>")/*.html()*/);
                        hw_ads.ads_settings.ads_container.prepend(jQuery("#<?php echo $id_right_ad?>")/*.html()*/);

                        //jQuery(hw_ads.ads_settings.mcontent_div).remove();
                        if(hw_ads.ads_settings.MainContentW < jQuery(window).width()) {
                            ad_right +=hw_ads.ads_settings.RightBannerW;
                        }
                        jQuery('#<?php echo $id_right_ad?> .hw-ad-right').css({"right": - (ad_right)});
                        //fixed ad left
                        setTimeout(function(){
                            console.log(hw_ads.elementInViewport('#<?php echo $id_left_ad?> .hw-side-ads'),ad_left);
                            if(hw_ads.elementInViewport('#<?php echo $id_left_ad?> .hw-side-ads') == false){
                                ad_left = -10;
                            }
                            jQuery('#<?php echo $id_left_ad?> .hw-ad-left').css({"left": - (ad_left)});
                        },50);
                        //show ads
                        jQuery(".hw-side-ads").show();
                        jQuery("#<?php echo $id_right_ad?>").sticky({topSpacing: hw_ads.ads_settings.TopAdjust});
                        jQuery("#<?php echo $id_left_ad?>").sticky({topSpacing: hw_ads.ads_settings.TopAdjust});
                    });
                </script>
                <?php
            }
            ?>
        <?php
        }
    }
}
HW_Options_Frontend::add_fragment(new NHP_Options_ads_Frontend());