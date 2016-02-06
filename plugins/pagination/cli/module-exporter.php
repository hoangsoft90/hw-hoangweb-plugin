<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Created by PhpStorm.
 * User: Hoang
 * Date: 19/11/2015
 * Time: 13:03
 */
/**
 * Class Pagination_Exporter
 */
class Pagination_Exporter extends HW_Module_Export {
    /**
     * export module data to wxr format (module data mean short data written for module)
     * @param $xml
     */
    function export_wxr_data($xml=null) {
        if(empty($xml)) $xml = $this->xml_data;
        foreach($this->get_options_xml() as $item) {
            $hw = $item->children($this->namespaces['hw']);
            //get skins
            $skins = $this->fetch_skins($hw->skin);

            $options = array(
                'pages_text' => 'Trang %CURRENT_PAGE% of %TOTAL_PAGES%',
                'current_text' => '%PAGE_NUMBER%',
                'page_text' => '%PAGE_NUMBER%',
                'first_text' => '« First',
                'last_text' => 'Last »',
                'prev_text' => '«',
                'next_text' => '»',
                'dotleft_text' => '...',
                'dotright_text' => '...',
                'num_pages' => !empty($hw->num_pages)? (string) $hw->num_pages : '5',
                'num_larger_page_numbers' => '3',
                'larger_page_numbers_multiple' => '10',
                'always_show' => '0',
                'use_pagenavi_css'=> '0',
                'style' => '1'
            );
            if(isset($skins['hw_skin'])) $options = array_merge($options, $skins);
            $this->options->add_option('pagenavi_options', $options);
        }
        $this->do_import();
    }
}