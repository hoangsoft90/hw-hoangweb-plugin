<?php
$theme['styles']=array('style.css', 'skitter.styles.css');
$theme['scripts'] = array('jquery.easing.1.3.js','jquery.skitter.min.js');

?>
<div class="box_skitter box_skitter_large">
    <ul>

        <li>
            <a href="#cube"><img src="<?php echo $this->skin->get_skin_file('images/slide-copy.jpg')?>"  class="cube" /></a>
            <div class="label_text"><p>hình ảnh mang tính chất minh họa</p></div>
        </li>

        <li>
            <a href="#cube"><img src="<?php echo $this->skin->get_skin_file('images/slide2-copy.jpg')?>"  class="cube" /></a>
            <div class="label_text"><p>hình ảnh mang tính chất minh họa</p></div>
        </li>

        <li>
            <a href="#cube"><img src="<?php echo $this->skin->get_skin_file('images/slide3-copy.jpg')?>"  class="cube" /></a>
            <div class="label_text"><p>hình ảnh mang tính chất minh họa</p></div>
        </li>

    </ul>
</div>
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        $('.box_skitter_large').skitter({
            theme: 'clean',
            numbers_align: 'center',
            progressbar: true,
            dots: true,
            preview: true,
            with_animations: ['paralell', 'glassCube', 'swapBars']
        });
    });
</script>