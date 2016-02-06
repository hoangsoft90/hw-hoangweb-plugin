<?php
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/11/2015
 * Time: 08:20
 */
class Breadcrumb_Exporter extends HW_Module_Export {
    /**
     * @param string $module
     */
    public function __construct($module) {
        parent::__construct($module);
        $this->add_module_widgets('bcn_widget');
    }
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        //set options
        foreach ($this->get_options_xml($xml) as $item) {
            $atts = $item->attributes();
            $hw = $item->children($this->namespaces['hw']);
            //fetch skins
            $skins = $this->fetch_skins($hw->skin, 'hash_skin', 'hwskin_config',0, false);

            $settings = array(
                'bmainsite_display' => '',
                'Hmainsite_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to %title%." href="%link%" class="%type%">%htitle%</a></span>',
                'Hmainsite_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',

                //home
                'bhome_display' => '1',
                'Hhome_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to %title%." href="%link%" class="%type%">%htitle%</a></span>',
                'Hhome_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',
                'bblog_display' => '',
                'Hblog_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to %title%." href="%link%" class="%type%">%htitle%</a></span>',
                'Hblog_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',

                'hseparator' => '>',
                'blimit_title' => '',
                'amax_title_length' => '20',
                'bcurrent_item_linked' => '',

                'Hpost_page_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to %title%." href="%link%" class="%type%">%htitle%</a></span>',
                'Hpost_page_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',
                'apost_page_root' => '0',

                'Hpaged_template' => 'Trang %htitle%',
                'bpaged_display' => '',

                'Hpost_post_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to %title%." href="%link%" class="%type%">%htitle%</a></span>',
                'Hpost_post_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',
                'apost_post_root' => '0',
                'bpost_post_taxonomy_display' => '1',
                'Spost_post_taxonomy_type' => 'category',

                'Hpost_attachment_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to %title%." href="%link%" class="%type%">%htitle%</a></span>',
                'Hpost_attachment_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',

                'H404_template' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',
                'S404_title' => 'Khong tim thay',

                'Hsearch_template' => 'Kết quả tìm kiếm cho <a title="Go to the first page of search results for %title%." href="%link%" class="%type%">%htitle%</a>',
                'Hsearch_template_no_anchor' => 'Kết quả tìm kiếm cho "%htitle%"',

                //tag
                'Htax_post_tag_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to the %title% tag archives." href="%link%" class="%type%">%htitle%</a></span>',
                'Htax_post_tag_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',

                'Htax_post_format_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to the %title% tag archives." href="%link%" class="%type%">%htitle%</a></span>',
                'Htax_post_format_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',
                //author
                'Hauthor_template' => 'Tac gia: <a title="Go to the first page of posts by %title%." href="%link%" class="%type%">%htitle%</a>',
                'Hauthor_template_no_anchor' => 'Tac gia: %htitle%',
                'Sauthor_name' => 'display_name',

                'Htax_category_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to the %title% category archives." href="%link%" class="%type%">%htitle%</a></span>',
                'Htax_category_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',

                'Hdate_template' => '<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" title="Go to the %title% archives." href="%link%" class="%type%">%htitle%</a></span>',
                'Hdate_template_no_anchor' => '<span typeof="v:Breadcrumb"><span property="v:title">%htitle%</span></span>',

                'hw_remove_current_item' => '0',
                'hw_allow_trail_link' => 'on',
                'hw_bcn_reverse' => '0',
                'hw_active_skin' => 'on',
            );
            if(isset($skins['hw_skin'])) {
                $settings['hw_skin'] = $skins['hw_skin']->get_skin(0);
                #$settings['hw_skin_config'] = $skins['hw_skin']->get_skinconfig(0);    //wrong
            }
            $this->options->add_option('bcn_options', $settings);
        }
        $this->add_export_widgets();    //export widget for module
        $this->do_import();
    }
}