<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 26/10/2015
 * Time: 11:00
 */
/**
 * pre-load jquery libs when active the plugin
 */
$hw_config['jquery-libs'] = function() {
    /**
     * jquery libraries
     */
    HW_Libraries::add('jquery-libs', array(
        'scripts' => array(
            'jquery-ui-1.8.23.js' => array(
                'file' => 'jqueryui/jquery-ui-1.8.23.custom.min.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            'jquery-ui-1.11.4.js' => array(
                'file' => 'jqueryui/jquery-ui-1.11.4.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            'jquery.mousewheel.min.js' => array(
                'file' => 'jquery.mousewheel.min.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            'jquery.easing.1.3.js' => array(
                'file' => 'jquery.easing.1.3.js',
                'required' => false,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'jquery-ui-1.11.4.css' => array(
                'file' => 'jqueryui/jquery-ui-1.11.4.css',
                'required' => false
            )
        )
    ));
    //canvas
    HW_Libraries::add('jquery-libs/canvas', array(
        'scripts' => array(
            'jquery.kinetic.min.js' => array(
                'file' => 'jquery.kinetic.min.js',
                'required' => false,
                'depends' => array('jquery')
            ),
        )
    ));
    HW_Libraries::add('jquery-libs/form', array(
        'scripts' => array(
            'jquery.form.min.js' => array(
                'file' => 'jquery.form.min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        )
    ));
    //block ui
    HW_Libraries::add('blockUI', array(
        'scripts' => array(
            'jquery.blockUI.js' => array(
                'file' => 'jquery.blockUI.js',
                'required' => true,
                'depends' => array('jquery')
            )
        )
    ));
    /**
     * background effects
     */
    HW_Libraries::add('bg-effects', array(
        'scripts' => array(
            'FireWorksNewYear.js' => array(
                'file' => 'FireWorksNewYear.js',
                'required' => false,
                'depends' => array()
            ),
            'snow.js' => array(
                'file' => 'snow.js',
                'required' => false,
                'depends' => array()
            )
        )
    ));
    /**
     * menu
     */
    HW_Libraries::add('menus/ddsmoothmenu', array(
        'scripts' => array(
            'ddsmoothmenu.js' => array(
                'file' => 'ddsmoothmenu.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'ddsmoothmenu.css' => array(
                'file' => 'ddsmoothmenu.css',
                'required' => true
            ),
            'ddsmoothmenu-v.css' => array(
                'file' => 'ddsmoothmenu-v.css',
                'required' => false
            )
        )
    ));
    //superfish
    HW_Libraries::add('menus/superfish', array(
        'scripts' => array(
            'superfish.js' => array(
                'file' => 'js/superfish.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'superfish.css' => array(
                'file' => 'css/superfish.css',
                'required' => true,
            )
        )
    ));

    //jquery-colorbox
    HW_Libraries::add('jquery-colorbox', array(
        'scripts' => array(
            'jquery.colorbox.js' => array(
                'file' => 'jquery.colorbox-min.js',   //absolute path
                'depends' => array('jquery'),
                'required' => true
            )
        ),
        'styles' => array(
            'colorbox.css' => array(
                'file' => 'colorbox.css',
                'required' => true
            )
        )
    ));
    //fancybox
    HW_Libraries::add('fancybox', array(
        'scripts' => array(
            'jquery.fancybox.pack.js' => array(
                'file' => 'source/jquery.fancybox.pack.js' ,
                'depends' => array('jquery')
            ),
            'jquery.easing-1.3.pack.js' => array(
                'file' => 'lib/jquery.easing-1.3.pack.js',
                'depends' => array('jquery')
            ),
            'jquery.mousewheel-3.0.6.pack.js' => array(
                'file' => 'lib/jquery.mousewheel-3.0.6.pack.js',
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'jquery.fancybox.css' => 'source/jquery.fancybox.css'
        )
    ));
    /*self::$jquery_libs['jquery-colorbox'] = array(    ->no longer support
        'scripts' => array(
            'jquery.colorbox.js' => array(
                'file' => HW_HOANGWEB_JQUERY_LIBS. '/jquery-colorbox/jquery.colorbox-min.js',   //absolute path
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'colorbox.css' => HW_HOANGWEB_JQUERY_LIBS . '/jquery-colorbox/colorbox.css'
        )
    );*/
    /**
     * tooltips
     */
    //tooltipster
    HW_Libraries::add('tooltips/tooltipster', array(
        'scripts' => array(
            'jquery.tooltipster.min.js' => array(
                'file' => 'jquery.tooltipster.min.js',      //relative path
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'tooltipster.css' => 'tooltipster.css'  //relative path
        )
    ));

    //qtip2: http://qtip2.com/
    HW_Libraries::add('tooltips/qtip2', array(
        'scripts' => array(
            'jquery.qtip.min.js' => array(
                'file' => 'jquery.qtip.min.js',   //relative path
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'jquery.qtip.min.css' => 'jquery.qtip.min.css'
        )
    ));

    /**
     * scrolling
     */
    //marquee
    HW_Libraries::add('marquee', array(
        'scripts' => array(
            'marquee' => array(
                'file' => 'marquee.js',
                'depends' => array('jquery')
            )
        ),
        /*'styles' => array(
            'style.css' => 'source/style.css'
        )*/
    ));
    //marqueue by everwebcodebox
    HW_Libraries::add('marquee/everwebcodebox', array(
        'scripts' => array(
            'jquery.marquee.min.js' => array(
                'file' => 'jquery.marquee.min.js',
                'depends' => array('jquery')
            )
        ),
        /*'styles' => array(
            'style.css' => 'source/style.css'
        )*/
    ));
    //jQuery Endless Div Scroll v1.0
    HW_Libraries::add('marquee/endless-div-scroll', array(
        'scripts' => array(
            'endless_scroll_min.js' => array(
                'file' => 'endless_scroll_min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        )
    ));
    //simplyscroll
    HW_Libraries::add('marquee/simplyscroll', array(
        'scripts' => array(
            'jquery.simplyscroll.js' => array(
                'file' => 'jquery.simplyscroll.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'jquery.simplyscroll.css' => array(
                'file' => 'jquery.simplyscroll.css',
                'required' => true
            )
        )
    ));
    /**
     * ckeditor
     */
    //ckeditor
    HW_Libraries::add('ckeditor', array(
        'scripts' => array(
            'ckeditor.js' => array(
                'file' => 'ckeditor.js',
                'depends' => array('jquery'),
                'required' => true
            ),
            'hw-ckeditor-config' => array(
                'file' => 'hw-config.js',
                'depends' => array('ckeditor.js'),  //allow other depends in global libs scope
                'required' => true
            )
        )
    ));
    //jquery sticky
    HW_Libraries::add('sticky', array(
        'scripts' => array(
            'jquery.sticky.js' => array(
                'file' => 'jquery.sticky.js',
                'depends' => array('jquery'),
                'required' => false
            ),
            'jquery.sticky-kit.min.js' => array(
                'file' => 'jquery.sticky-kit.min.js',
                'depends' => array('jquery'),
                'required' => false
            )
        )
    ));
    //syntax highlighter (lib name match it' folder name)
    HW_Libraries::add('syntaxhighlighter_3.0.83', array(
        'scripts' => array(
            'shCore.js' => array(
                'file' => 'scripts/shCore.js',
                'depends' => array('jquery'),
                'required' => true
            ),
            //javascript
            'shBrushJScript.js' => array(
                'file' => 'scripts/shBrushJScript.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            //xml, xhtml, xslt, html, xhtml
            'shBrushXml.js' => array(
                'file' => 'scripts/shBrushXml.js',
                'depends' => array('jquery'),
                'required' => false
            ),
            //php
            'shBrushPhp.js' => array(
                'file' => 'scripts/shBrushPhp.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            //CSS
            'shBrushCss.js' => array(
                'file' => 'scripts/shBrushCss.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            //plain
            'shBrushPlain.js' => array(
                'file' => 'scripts/shBrushPlain.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            //SQL
            'shBrushSql.js' => array(
                'file' => 'scripts/shBrushSql.js',
                'required' => false,
                'depends' => array('jquery')
            ),
            //python
            'shBrushPython.js' => array(
                'file' => 'scripts/shBrushPython.js',
                'required' => false,
                'depends' => array('jquery')
            ),
        ),
        'styles' => array(
            //required default
            'shCore.css' => 'styles/shCore.css',
            'shThemeDefault.css' => 'styles/shThemeDefault.css',

        )
    ));
    //jquery photowall
    HW_Libraries::add('jquery-photowall-master', array(
        'scripts' => array(
            'jquery-photowall.js' => array(
                'file' => 'jquery-photowall.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'jquery-photowall.css' => array(
                'file' => 'jquery-photowall.css',
                'required' => true
            )
        ),
        'helps' => 'help.html'
    ));
    //easytabs
    HW_Libraries::add('easytabs', array(
        'scripts' => array(
            'jquery.easytabs.min.js' => array(
                'file' => 'jquery.easytabs.min.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'jquery.ba-hashchange.js' => array(
                'file'=>'jquery.ba-hashchange.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'jquery.vietcodex.track-content-tabs-scroll.js' => array(
                'file' => 'jquery.vietcodex.track-content-tabs-scroll.js',
                'required' => false,
                'depends' => array('jquery'/*,'jquery.scrolltracker.js'*/)
            ),
            'jquery.scrolltracker.js' => array(
                'file' => 'jquery.scrolltracker.js',
                'required' => false,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'easytabs.css' => array(
                'file' => 'easytabs.css',
                'required' => true
            )
        ),
        #'helps' => 'help.html'
    ));
    //cloudzoom
    HW_Libraries::add('cloudzoom', array(
        'scripts' => array(
            'cloud-zoom.1.0.2.min.js' => array(
                'file' => 'cloud-zoom.1.0.2.min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'cloud-zoom.css' => array(
                'file' => 'cloud-zoom.css',
                'required' => true
            )
        )
    ));
    //jquery photowall
    HW_Libraries::add('galleries/jquery-photowall', array(
        'scripts' => array(
            'jquery-photowall.js' => array(
                'file' => 'jquery-photowall.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'jquery-photowall.css' => array(
                'file' => 'jquery-photowall.css',
                'required' => true,
            )
        )
    ));
    //jgallery
    HW_Libraries::add('galleries/jgallery', array(
        'scripts' => array(
            'jgallery.min.js' => array(
                'file' => 'js/jgallery.min.js?v=1.5.0',
                'required' => true,
                'depends' => array('jquery')
            ),
            'touchswipe.min.js' => array(
                'file' => 'js/touchswipe.min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'font-awesome.min.css' => array(
                'file' => 'css/font-awesome.min.css',
                'required' => true
            ),
            'jgallery.min.css' => array(
                'file' => 'css/jgallery.min.css?v=1.5.0',
                'required' => true
            )
        )
    ));
    //pikachoose slideshow
    HW_Libraries::add('galleries/pikachoose-96', array(
        'scripts' => array(
            'jquery.jcarousel.min.js' => array(
                'file' => 'lib/jquery.jcarousel.min.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'jquery.pikachoose.min.js' => array(
                'file' => 'lib/jquery.pikachoose.min.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'jquery.touchwipe.min.js' => array(
                'file' => 'lib/jquery.touchwipe.min.js',
                'required' => true,
                'depends' => array('jquery')
            ),
        ),
        'styles' => array(
            'base.css' => array(
                'file' => 'styles/base.css',
                'required' => true
            ),
            'bottom.css' => array(
                'file' => 'styles/bottom.css',
                'required' =>false
            ),
            'css3.css' => array(
                'file' => 'styles/css3.css',
                'required' =>false
            ),
            'left.css' => array(
                'file' => 'styles/left.css',
                'required' =>false
            ),
            'left-without.css' => array(
                'file' => 'styles/left-without.css',
                'required' =>false
            ),
            'right.css' => array(
                'file' => 'styles/right.css',
                'required' =>false
            ),
            'right-without.css' => array(
                'file' => 'styles/right-without.css',
                'required' =>false
            ),
            'simple.css' => array(
                'file' => 'styles/simple.css',
                'required' =>false
            ),
            'tooltip.css' => array(
                'file' => 'styles/tooltip.css',
                'required' =>false
            ),
            'transitions.css' => array(
                'file' => 'styles/transitions.css',
                'required' =>false
            ),

        )
    ));
    //photor
    HW_Libraries::add('galleries/photor', array(
        'scripts' => array(
            'photor.min.js' => array(
                'file' => 'photor.min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'photor.min.css' => array(
                'file' => 'photor.min.css',
                'required' => true
            )
        )
    ));
    /**
     * sliders/ scrolling
     */
    //jcarousellite
    HW_Libraries::add('sliders/jcarousellite', array(
        //'libs' => 'lib-name',
        //'libs' => array('lib-name')
        'libs' => array(
            'jquery-libs' => array(
                'scripts' => ['jquery.mousewheel.min.js' ]
            ),
            'jquery-libs/canvas' => array(
                'scripts' => ['jquery.kinetic.min.js']
            )
        ),
        'scripts' => array(
            'jquery.jcarousellite.min.js' => array(
                'file' => 'jquery.jcarousellite.min.js',
                'required' => true,
                'depends' => array('jquery')
            ),

            'jquery.jcarousellite.pauseOnHover.js' => array(
                'file' => 'jquery.jcarousellite.pauseOnHover.js',
                'required' => true,
                'depends' => array('jquery')
            )
        )
    ));
    //jcarousel
    HW_Libraries::add('sliders/jcarousel', array(
        'scripts' => array(
            'jquery.jcarousel.min.js' => array(
                'file' => 'jquery.jcarousel.min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        )
    ));
    //smoothDivScroll
    HW_Libraries::add('sliders/smoothDivScroll', array(
        //'libs' => array('sliders/div', array('sliders/lbi1'=>array('scripts'=>'js1')))
        'libs' => array(array(
            'jquery-libs'=>array('scripts'=> ['jquery-ui-1.8.23.js', 'jquery.mousewheel.min.js']),
            'jquery-libs/canvas' => array('scripts'=>['jquery.kinetic.min.js'])
        )),
        'scripts' => array(
            'jquery.smoothdivscroll-1.3-min.js' => array(
                'file' => 'jquery.smoothdivscroll-1.3-min.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'jquery.smoothDivScroll-vertical-1.2.js' => array(
                'file' => 'jquery.smoothDivScroll-vertical-1.2.js',
                'required' => false,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'smoothDivScroll.css' => array(
                'file' => 'smoothDivScroll.css',
                'required' => true
            )
        )
    ));
    //nivoSlider
    HW_Libraries::add('sliders/nivoSlider', array(
        'scripts' => array(
            'jquery.nivo.slider.pack.js' => array(
                'file' => 'asset/jquery.nivo.slider.pack.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'nivo-slider.css' => array(
                'file' => 'asset/nivo-slider.css',
                'required' => true
            )
        )
    ));
    //Skitter
    HW_Libraries::add('sliders/Skitter', array(
        'libs' => array(array('jquery-libs'=>array('scripts'=> ['jquery.easing.1.3.js']))),
        'scripts' => array(
            'jquery.skitter.min.js' => array(
                'file' => 'asset/jquery.skitter.min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'skitter.styles.css' => array(
                'file' => 'skitter.styles.css',
                'required' => true
            )
        )
    ));
    //wonderpluginslider
    HW_Libraries::add('sliders/wonderpluginslider', array(
        'scripts' => array(
            'wonderpluginslider.js' => array(
                'file' => 'wonderpluginslider.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'wonderpluginsliderskins.js' => array(
                'file' => 'wonderpluginsliderskins.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'wonderpluginsliderengine.css' => array(
                'file' => 'wonderpluginsliderengine.css',
                'required' => true
            )
        )
    ));
    //bxslider
    HW_Libraries::add('sliders/bxSlider', array(
        'scripts' => array(
            'jquery.bxslider.min.js' => array(
                'file' => 'jquery.bxslider.min.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'jquery.bxslider.css' => array(
                'file' => 'jquery.bxslider.css',
                'required' => true
            )
        )
    ));
    //
    /**
     * scrollbars
     */
    //lionbars
    HW_Libraries::add('scrollbars/lionbars', array(
        'scripts' => array(
            'jquery.lionbars.0.3.js' => array(
                'file' => 'jquery.lionbars.0.3.js',
                'required' => true,
                'depends' => true
            )
        ),
        'styles' => array(
            'style.css' => array(
                'file' => 'style.css',
                'required' => true,
                'depends' => true
            )
        )
    ));
    //perfect-scrollbar
    HW_Libraries::add('scrollbars/perfect-scrollbar', array(
        'scripts' => array(
            'perfect-scrollbar.js' => array(
                'file' => 'js/perfect-scrollbar.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'perfect-scrollbar.jquery.js' => array(
                'file' => 'js/perfect-scrollbar.jquery.js',
                'required' => true,
                'depends' => array('jquery')
            )
        ),
        'styles' => array(
            'style.css' => array(
                'file' => 'style.css',
                'required' => true
            ),
            'perfect-scrollbar.min.css' => array(
                'file' => 'css/perfect-scrollbar.min.css',
                'required' => true
            )
        )
    ));
    /**
     * UI Components
     */
    //ddslick
    HW_Libraries::add('components-ui/dropdown_ddslick', array(
        'scripts' => array(
            'jquery.ddslick.min.js' => array(
                'file' => 'jquery.ddslick.min.js',
                'required'=> true,
                'depends' => array('jquery')
            )
        )
    ));
    //jquery collapse
    HW_Libraries::add('components-ui/collapse/jQuery-Collapse', array(
        'scripts' => array(
            'jquery.collapse.js' => array(
                'file' => 'jquery.collapse.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'jquery.collapse_cookie_storage.js' => array(
                'file' => 'jquery.collapse_cookie_storage.js',
                'required' => true,
                'depends' => array('jquery')
            ),
            'jquery.collapse_storage.js' => array(
                'file' => 'jquery.collapse_storage.js',
                'required' => true,
                'depends' => array('jquery')
            )
        )
    ));
    /**
     * colors
     */
    //jscolor
    HW_Libraries::add('colors/jscolor', array(
        'scripts' => array(
            'jscolor.js' => array(
                'file' => 'jscolor.js',
                'required' => true,
                'depends' => array('jquery')
            )
        )
    ));
    //color picker
    HW_Libraries::add('colors/spectrum', array(
        'scripts' => array(
            'file' => 'spectrum.js',
            'required' => true,
            'depends' => array('jquery')
        ),
        'styles' => array(
            'file' => 'spectrum.css',
            'required' => true,
        )
    ));
    //page loading progressbar
    HW_Libraries::add('pageload/nprogress', array(
        'scripts' => array(
            'nprogress.js' => array(
                'file' => 'nprogress.js',
                'required'=> true,
                'depends' => array('jquery')
            )

        ),
        'styles' => array(
            'nprogress.css' => array(
                'file' => 'nprogress.css',
                'required' => true
            )
        )
    ));
};