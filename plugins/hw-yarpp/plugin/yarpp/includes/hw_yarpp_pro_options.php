<?php
global $hw_yarpp;

if (isset($_GET['aid']) && isset($_GET['v']) && isset($_GET['st']) && isset($_GET['dpid'])) {
    $hw_yarpp->yarppPro['aid'] = (trim($_GET['aid']) !== '') ? $_GET['aid'] : null;
    $hw_yarpp->yarppPro['dpid']= (trim($_GET['dpid'])!== '') ? $_GET['dpid']: null;
    $hw_yarpp->yarppPro['st']  = (trim($_GET['st'])  !== '') ? rawurlencode($_GET['st']) : null;
    $hw_yarpp->yarppPro['v']   = (trim($_GET['v'])   !== '') ? rawurlencode($_GET['v'])  : null;

    update_option('hw_yarpp_pro', $hw_yarpp->yarppPro);
}

$src = urlencode(admin_url().'options-general.php?page='.$_GET['page']);
$aid = (isset($hw_yarpp->yarppPro['aid']) && $hw_yarpp->yarppPro['aid']) ? $hw_yarpp->yarppPro['aid'] : 0;
$st  = (isset($hw_yarpp->yarppPro['st'])  && $hw_yarpp->yarppPro['st'])  ? $hw_yarpp->yarppPro['st']  : 0;
$v   = (isset($hw_yarpp->yarppPro['v'])   && $hw_yarpp->yarppPro['v'])   ? $hw_yarpp->yarppPro['v']   : 0;
$d   = urlencode(get_home_url());
$url = 'https://yarpp.adkengage.com/AdcenterUI/PublisherUI/PublisherDashboard.aspx?src='.$src.'&d='.$d.'&aid='.$aid.'&st='.$st.'&plugin=1';

include(HW_YARPP_DIR.'/includes/phtmls/hw_yarpp_pro_options.phtml');