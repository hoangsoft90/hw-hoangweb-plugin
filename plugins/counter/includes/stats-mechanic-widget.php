<?php
/**
 * Class hw_StatsMechanic
 */
class hw_StatsMechanic extends WP_Widget{
    /**
     * @var null
     */
    private $module = null;

    /**
     * main class constructor
     */
    function __construct(){
        $params=array(
            'description' => 'Hiển thị bộ đếm truy cập', //plugin description
            'name' => 'Bộ đếm truy cập'  //title of plugin
        );

        parent::__construct('hw_StatsMechanic', '', $params);
        //for frontend
        add_action('wp_enqueue_scripts', array($this, '_enqueue_scripts') );
    }

    /**
     * put js/css file on frontend
     * @hook wp_enqueue_scripts
     */
    public function _enqueue_scripts() {
        //style and widgetne
        wp_enqueue_style('hw-stats-mechanic-style', HW_COUNTER_PLUGIN_URL. '/assets/styles/css/default.css');
    }
    // extract($instance);
    /**
     * @param array $instance
     * @return string|void
     */
    public function form($instance)  {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
        $title = $instance['title'];

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Tiêu đề: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('font_color'); ?>">Mã mầu chữ: <input class="widefat" id="<?php echo $this->get_field_id('font_color'); ?>" name="<?php echo $this->get_field_name('font_color'); ?>" type="text" value="<?php echo $instance['font_color']; ?>" /></label>
        </p>

        <!-- UPDATE PLAN -->
        <p>
            <label for="<?php echo $this->get_field_id('count_start'); ?>">Thêm lượt truy cập ảo: <input class="widefat" id="<?php echo $this->get_field_id('count_start'); ?>" name="<?php echo $this->get_field_name('count_start'); ?>" type="text" value="<?php echo $instance['count_start']; ?>" /></label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('hits_start'); ?>">Bắt đầu từ Hits: <input class="widefat" id="<?php echo $this->get_field_id('hits_start'); ?>" name="<?php echo $this->get_field_name('hits_start'); ?>" type="text" value="<?php echo $instance['hits_start']; ?>" /></label>
        </p>
        <p><font size='2'>Nhập một con số cộng vào lượt hits hiện tại (mặc định bắt đầu từ 1)</font></p>
        <!-- END UPDATE -->
        <p><font size='3'><b>Tùy chọn hiển thị</b></font></p>
        <p>
            <label for="<?php echo $this->get_field_id('today_view'); ?>"><?php _e('Truy cập hôm nay? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['today_view'], 'on' ); ?> id="<?php echo $this->get_field_id('today_view'); ?>" name="<?php echo $this->get_field_name('today_view'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('yesterday_view'); ?>"><?php _e('Truy cập hôm qua? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['yesterday_view'], 'on' ); ?> id="<?php echo $this->get_field_id('yesterday_view'); ?>" name="<?php echo $this->get_field_name('yesterday_view'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('month_view'); ?>"><?php _e('Theo tháng? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['month_view'], 'on' ); ?> id="<?php echo $this->get_field_id('month_view'); ?>" name="<?php echo $this->get_field_name('month_view'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('year_view'); ?>"><?php _e('Theo năm? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['year_view'], 'on' ); ?> id="<?php echo $this->get_field_id('year_view'); ?>" name="<?php echo $this->get_field_name('year_view'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('total_view'); ?>"><?php _e('Tổng views? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['total_view'], 'on' ); ?> id="<?php echo $this->get_field_id('total_view'); ?>" name="<?php echo $this->get_field_name('total_view'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('hits_view'); ?>"><?php _e('Hits hôm nay? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['hits_view'], 'on' ); ?> id="<?php echo $this->get_field_id('hits_view'); ?>" name="<?php echo $this->get_field_name('hits_view'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('totalhits_view'); ?>"><?php _e('Tổng Hits? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['totalhits_view'], 'on' ); ?> id="<?php echo $this->get_field_id('totalhits_view'); ?>" name="<?php echo $this->get_field_name('totalhits_view'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('online_view'); ?>"><?php _e('Số online? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['online_view'], 'on' ); ?> id="<?php echo $this->get_field_id('online_view'); ?>" name="<?php echo $this->get_field_name('online_view'); ?>" /></label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('ip_display'); ?>"><?php _e('Hiển thị IP? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['ip_display'], 'on' ); ?> id="<?php echo $this->get_field_id('ip_display'); ?>" name="<?php echo $this->get_field_name('ip_display'); ?>" /></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('server_time'); ?>"><?php _e('Thời gian? '); ?><input type="checkbox" class="checkbox" <?php checked( $instance['server_time'], 'on' ); ?> id="<?php echo $this->get_field_id('server_time'); ?>" name="<?php echo $this->get_field_name('server_time'); ?>" /></label>
        </p>

        <p>Chú ý: cấu hình hình ảnh bộ đếm <a href="<?php echo HW_Module_Settings_page::get_module_setting_page('counter')?>">tại đây</a></p>

    <?php

    }

    /**
     * update stats
     */
    private function update_stats() {
        global $wpdb;
        $ip      = $_SERVER['REMOTE_ADDR']; // Getting the user's computer IP
        $tanggal = date("d/m/Y"); // Getting the current date
        $waktu  = time();

        // Check your IP, whether the user has had access to today's
        $sql = $wpdb->get_results("SELECT * FROM `". HW_BMW_TABLE_NAME . "` WHERE ip='$ip' AND tanggal='$tanggal'");
        // If not there, save the user data to the database
        if(!is_wp_error($sql) && count($sql) == 0){
            $data = array(
                'ip' => $ip,
                'tanggal' => $tanggal,
                'hits' => '1',
                'online' => $waktu
            );
            $wpdb->insert( HW_BMW_TABLE_NAME , $data);
        }
        else{
            $wpdb->query( "UPDATE ".HW_BMW_TABLE_NAME . "` SET hits=hits+1, online='$waktu' WHERE ip='$ip' AND tanggal='$tanggal'");
        }
    }
    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance){
        extract($args, EXTR_SKIP);
        global $wpdb;
        $this->module = HW_Module_Counter::get();

        $ipaddress = isset($instance['ip_display']) ? $instance['ip_display'] : false ; // display ip address
        $stime = isset($instance['server_time']) ? $instance['server_time'] : false ; // display server time
        $fontcolor= $instance['font_color'];
        $style = $this->module->get_field_value('statsmechanic_style');

        $todayview = $instance ['today_view'];
        $yesview = $instance ['yesterday_view'];
        $monthview = $instance ['month_view'];
        $yearview = $instance ['year_view'];
        $totalview = $instance ['total_view'];
        $hitsview = $instance ['hits_view'];
        $totalhitsview = $instance ['totalhits_view'];
        $onlineview = $instance ['online_view'];
        $count_start = $instance ['count_start'];
        $hits_start = $instance ['hits_start'];
        $styles_url = $this->module->option('module_url'). '/assets/styles';

        echo $before_widget;
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);


        if (!empty($title))
            echo $before_title . $title . $after_title;?>
        <?php
        $ip      = $_SERVER['REMOTE_ADDR']; // Getting the user's computer IP
        $tanggal = date("y-m-d"); // Getting the current date

        $bln=date("m");
        $tgl=date("d");
        $blan=date("Y-m");
        $thn=date("Y");
        $tglk=$tgl-1;

        //count visit
        $this->update_stats();
        //variable
        if($tglk=='1' | $tglk=='2' | $tglk=='3' | $tglk=='4' | $tglk=='5' | $tglk=='6' | $tglk=='7' | $tglk=='8' | $tglk=='9'			){
            $kemarin= $wpdb->get_results("SELECT * FROM `". HW_BMW_TABLE_NAME . "` WHERE tanggal='$thn-$bln-0$tglk'");
        } else {
            $kemarin= $wpdb->get_results("SELECT * FROM `". HW_BMW_TABLE_NAME . "` WHERE tanggal='$thn-$bln-$tglk'");
        }
        $bulan=("SELECT * FROM `". HW_BMW_TABLE_NAME . "` WHERE tanggal LIKE '%$blan%'");
        $bulan1=count($wpdb->get_results($bulan));
        $tahunini=("SELECT * FROM `". HW_BMW_TABLE_NAME . "` WHERE tanggal LIKE '%$thn%'");
        $tahunini1= count($wpdb->get_results($tahunini));
        $pengunjung       = count($wpdb->get_results("SELECT * FROM `". HW_BMW_TABLE_NAME . "` WHERE tanggal='$tanggal' GROUP BY ip"));
        $totalpengunjung  = reset($wpdb->get_row("SELECT COUNT(hits) FROM `". HW_BMW_TABLE_NAME . "`") );

        $hits             = reset($wpdb->get_results("SELECT SUM(hits) as hitstoday FROM `". HW_BMW_TABLE_NAME . "` WHERE tanggal='$tanggal' GROUP BY tanggal"));
        $totalhits        = /*mysql_result,0*/reset($wpdb->get_row("SELECT SUM(hits) FROM `". HW_BMW_TABLE_NAME . "`") );

        $tothitsgbr      = /*mysql_result,0*/reset($wpdb->get_row("SELECT COUNT(hits) FROM `". HW_BMW_TABLE_NAME . "`") );
        $bataswaktu       = time() - 300;
        $pengunjungonline = count($wpdb->get_results("SELECT * FROM `". HW_BMW_TABLE_NAME . "` WHERE online > '$bataswaktu'"));
        $kemarin1 = count($kemarin);

        $ext = ".gif";
        //image print
        // UPDATE PLAN
        if ($count_start==NULL) {
            $tothitsgbr = sprintf("%06d", $tothitsgbr);
        }else{
            $tothitsgbr = sprintf("%06d", $tothitsgbr + $count_start);

        }
        /*for ($i = 0; $i <= 9; $i++) {
            $tothitsgbr = str_replace($i, "<img src='". $styles_url ."/$style/$i$ext' alt='$i'>", $tothitsgbr);
            // IF installed on sub domain
            // $tothitsgbr = str_replace($i, "<img src='http://demo.balimechanicweb.net/counter/styles/$style/$i$ext' alt='$i'>", $tothitsgbr);
        }*/
        $nums = str_split($tothitsgbr, 1);
        foreach($nums as &$num) {
            $num = "<img src='{$styles_url}/{$style}/{$num}$ext' alt='{$num}'/>";
        }
        $tothitsgbr = join('', $nums);
        //image
        $imgvisit= "<img src='".HW_COUNTER_PLUGIN_URL. ('/assets/counter/mvcvisit.png'  ). "'>";
        $yesterday="<img src='".HW_COUNTER_PLUGIN_URL .('/assets/counter/mvcyesterday.png'  ). "'>";
        $month="<img src='".HW_COUNTER_PLUGIN_URL. ('/assets/counter/mvcmonth.png'  ). "'>";
        $year="<img src='".HW_COUNTER_PLUGIN_URL. ('/assets/counter/mvcyear.png'  ). "'>";
        $imgtotal="<img src='".HW_COUNTER_PLUGIN_URL. ('/assets/counter/mvctotal.png'  ). "'>";
        $imghits="<img src='".HW_COUNTER_PLUGIN_URL. ('/assets/counter/mvctoday.png' ). "'>";
        $imgtotalhits="<img src='".HW_COUNTER_PLUGIN_URL. ('/assets/counter/mvctotalhits.png'  ). "'>";
        $imgonline="<img src='" .HW_COUNTER_PLUGIN_URL. ('/assets/counter/mvconline.png' ). "'>";

        ?>
        <div id='mvcwid' style='font-size:2; color:<?php echo $fontcolor ?>;'>
            <div id="mvccount"><?php echo $tothitsgbr ?></div>
            <div id="mvctable">
                <table width='100%'>
                    <?php if ($todayview) { ?>
                        <tr><td style='font-size:2; color:<?php echo $fontcolor ?>;'><?php echo $imgvisit ?> Hôm nay : <?php echo $pengunjung ?></td></tr>
                    <?php } ?>
                    <?php if ($yesview) { ?>
                        <tr><td style='font-size:2; color:<?php echo $fontcolor ?>;'><?php echo $yesterday ?> Hôm qua : <?php echo $kemarin1 ?></td></tr>
                    <?php } ?>
                    <?php if ($monthview) { ?>
                        <tr><td style='font-size:2; color:<?php echo $fontcolor ?>;'><?php echo $month ?> Tháng này : <?php echo $bulan1 ?></td></tr>
                    <?php } ?>
                    <?php if ($yearview) { ?>
                        <tr><td style='font-size:2; color:<?php echo $fontcolor ?>;'><?php echo $year ?> Năm này : <?php echo $tahunini1 ?></td></tr>
                    <?php } ?>

                    <?php if ($totalview) { ?>
                        <tr><td style='font-size:2;color:<?php echo $fontcolor ?>;'><?php echo $imgtotal ?> Tổng số : <?php echo $totalpengunjung ?></td></tr>
                    <?php } ?>
                    <?php if ($hitsview) { ?>
                        <tr><td style='font-size:2; color:<?php echo $fontcolor ?>;'><?php echo $imghits ?> Hits hôm nay : <?php echo $hits->hitstoday ?></td></tr>
                    <?php } ?>
                    <?php if ($totalhitsview) { ?>
                        <tr><td style='font-size:2; color:<?php echo $fontcolor ?>;'><?php echo $imgtotalhits ?> Total Hits : <?php if ($hits_start==NULL) {
                                    echo $totalhits ;
                                }else{
                                    $totalhitsfake = $totalhits + $hits_start;
                                    echo $totalhitsfake;
                                }?></td></tr>
                    <?php } ?>
                    <?php if ($onlineview) { ?>
                        <tr><td style='font-size:2; color:<?php echo $fontcolor ?>;'><?php echo $imgonline ?> Đang online : <?php echo $pengunjungonline ?></td></tr>
                    <?php } ?>
                </table>
            </div>
            <?php if ($ipaddress) { ?>
                <div id="mvcip">IP: <?php echo $ip ?></div>
            <?php } ?>
            <?php if ($stime) { ?>
                <div id="mvcserver">Thời gian: <?php echo $tanggal ?></div>
            <?php } ?>


        </div>
        <?php
        echo $after_widget;
    }
    static function register_wp_statsmechanic() {
        register_widget('hw_StatsMechanic');
    }
}
#add_action('widgets_init', 'hw_StatsMechanic::register_wp_statsmechanic');
add_action('hw_widgets_init', 'hw_StatsMechanic::register_wp_statsmechanic');