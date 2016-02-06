<?php
/**
 * HW Template: skin 1
 */
?>
<?php

?>
<h3><?php echo $hwrp->title?></h3>
<?php if (have_posts()):?>
    <ol>
        <?php while (have_posts()) : the_post(); ?>
            <li><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a><!-- (<?php the_score(); ?>)--></li>
        <?php endwhile; ?>
    </ol>
<?php else: ?>

<?php endif; ?>
