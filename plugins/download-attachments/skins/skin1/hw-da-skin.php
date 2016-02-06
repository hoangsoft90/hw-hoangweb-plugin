<?php
/**
 * HW Template: skin 1
 */
#(new HW_SKIN)->get_skin_url()
?>
<div class="downloadBlock">
    <div class="contentFrame">
        <div class="contentBackGround">

            <div class="downloadHeader">
                <div class="leftCol">
                    STT</div>
                <div class="centerCol">
                    Hỗ trợ tìm kiếm
                    <form action="<?php echo  esc_url( home_url( '/'  ) )?>" method="post" name="form-search">
                        <div class="inputMark">
                            <input name="txtDownloadSearch" type="text" value="<?php echo get_search_query() ?>" id="txtDownloadSearch" class="textBox">
                        </div>
                        <div class="btnSearch">
                            <input type="image" name="btnSearch" id="btnSearch" src="<?php echo HW_SKIN::current()->get_skin_url('images/search-icon.png')?>" style="border-width:0px;width:25px;outline:none;" onclick="this.form.submit()">
                        </div>
                    </form>
                </div>
                <div class="rightCol">
                    Tải về</div>
            </div>
            <div class="clear"></div>
            <div class="downloadContent">
                <?php foreach($attachments as $file) {?>
                <div class="item <?php echo $file['index']%2? 'odd':''?>">
                    <div class="leftCol">
                        <?php echo $file['index']?>
                    </div>
                    <div class="centerCol">
                        <p style="margin-top: 11px;">
                                                        <span class="docNo">
                                                            </span>
                            <?php printf('%s %s %s %s', $file['icon'], $file['link_before'], $file['link'], $file['link_after']);?>
                        </p>
                    </div>
                    <div class="rightCol">
                        <div class="downloadButton">
                            <?php printf('<a href="%s" target="_blank" ><input type="image" name="btnDownload" id="" src="%s" style="border-width:0px;width:25px"></a>', $file['url'], HW_SKIN::current()->get_skin_url('images/Sign-Download-icon.png'))?>

                        </div>
                        <div class="downloadNo">
                            (<?php echo $file['count']?>)
                        </div>
                    </div>
                </div>
                <?php }?>


            </div>

        </div>
    </div>
</div>