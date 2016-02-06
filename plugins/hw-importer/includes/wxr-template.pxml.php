<rss version="2.0"
     xmlns:excerpt="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/excerpt/"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:wp="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/"
     xmlns:hw="http://hoangweb.com/export/<?php echo HW_WXR_VERSION;?>/"
     xmlns:param="http://hoangweb.com/export/<?php echo HW_WXR_VERSION?>/param/"
      xmlns:params="http://hoangweb.com/export/<?php echo HW_WXR_VERSION?>/params/"
      xmlns:skin="http://hoangweb.com/export/<?php echo HW_WXR_VERSION?>/skin/"
    >
    <title><?php bloginfo_rss( 'name' ); ?></title>
    <link><?php bloginfo_rss( 'url' ); ?></link>
    <description><?php bloginfo_rss( 'description' ); ?></description>
    <pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
    <language><?php bloginfo_rss( 'language' ); ?></language>
    <wp:wxr_version><?php echo WXR_VERSION; ?></wp:wxr_version>
    <wp:base_site_url><?php echo wxr_site_url(); ?></wp:base_site_url>
    <wp:base_blog_url><?php bloginfo_rss( 'url' ); ?></wp:base_blog_url>

    <?php $this->wxr_authors_list($post_ids)?>
    <?php foreach ( $cats as $c ) : ?>
        <wp:category><wp:term_id><?php echo $c->term_id ?></wp:term_id><wp:category_nicename><?php echo $c->slug; ?></wp:category_nicename><wp:category_parent><?php echo $c->parent ? $cats[$c->parent]->slug : ''; ?></wp:category_parent><?php $this->wxr_cat_name( $c ); ?><?php $this->wxr_category_description( $c ); ?></wp:category>
    <?php endforeach; ?>
    <?php foreach ( $tags as $t ) : ?>
        <wp:tag><wp:term_id><?php echo $t->term_id ?></wp:term_id><wp:tag_slug><?php echo $t->slug; ?></wp:tag_slug><?php $this->wxr_tag_name( $t ); ?><?php $this->wxr_tag_description( $t ); ?></wp:tag>
    <?php endforeach; ?>
    <?php foreach ( $terms as $t ) : ?>
        <wp:term><wp:term_id><?php echo $t->term_id ?></wp:term_id><wp:term_taxonomy><?php echo $t->taxonomy; ?></wp:term_taxonomy><wp:term_slug><?php echo $t->slug; ?></wp:term_slug><wp:term_parent><?php echo $t->parent ? $terms[$t->parent]->slug : ''; ?></wp:term_parent><?php $this->wxr_term_name( $t ); ?><?php $this->wxr_term_description( $t ); ?></wp:term>
    <?php endforeach; ?>
    <?php if ( 'all' == $args['content'] ) $this->wxr_nav_menu_terms(); ?>

    <?php
    /** This action is documented in wp-includes/feed-rss2.php */
    do_action( 'rss2_head' );
    ?>
    <posts>
    <?php if ( $post_ids ) {
        global $wp_query;

        // Fake being in the loop.
        $wp_query->in_the_loop = true;

        // Fetch 20 posts at a time rather than loading the entire table into memory.
        while ( $next_posts = array_splice( $post_ids, 0, 20 ) ) {
            $where = 'WHERE ID IN (' . join( ',', $next_posts ) . ')';
            $posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} $where" );

            // Begin Loop.
            foreach ( $posts as $post ) {
                setup_postdata( $post );
                $is_sticky = is_sticky( $post->ID ) ? 1 : 0;
                ?>
                <item>
                    <title><?php
                        /** This filter is documented in wp-includes/feed.php */
                        echo apply_filters( 'the_title_rss', $post->post_title );
                        ?></title>
                    <link><?php the_permalink_rss() ?></link>
                    <pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
                    <dc:creator><?php echo $this->wxr_cdata( get_the_author_meta( 'login' ) ); ?></dc:creator>
                    <guid isPermaLink="false"><?php the_guid(); ?></guid>

                    <description></description>
                    <content:encoded><?php
                        /**
                         * Filter the post content used for WXR exports.
                         *
                         * @since 2.5.0
                         *
                         * @param string $post_content Content of the current post.
                         */
                        echo $this->wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) );
                        ?></content:encoded>
                    <excerpt:encoded><?php
                        /**
                         * Filter the post excerpt used for WXR exports.
                         *
                         * @since 2.6.0
                         *
                         * @param string $post_excerpt Excerpt for the current post.
                         */
                        echo $this->wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) );
                        ?></excerpt:encoded>
                    <!--
                    <wp:post_id><?php echo $post->ID; ?></wp:post_id>
                    <wp:post_date><?php echo $post->post_date; ?></wp:post_date>
                    <wp:post_date_gmt><?php echo $post->post_date_gmt; ?></wp:post_date_gmt>
                    -->
                    <wp:comment_status><?php echo $post->comment_status; ?></wp:comment_status>
                    <wp:ping_status><?php echo $post->ping_status; ?></wp:ping_status>
                    <wp:post_name><?php echo $post->post_name; ?></wp:post_name>
                    <wp:status><?php echo $post->post_status; ?></wp:status>
                    <wp:post_parent><?php echo $post->post_parent; ?></wp:post_parent>
                    <wp:menu_order><?php echo $post->menu_order; ?></wp:menu_order>
                    <wp:post_type><?php echo $post->post_type; ?></wp:post_type>
                    <wp:post_password><?php echo $post->post_password; ?></wp:post_password>
                    <wp:is_sticky><?php echo $is_sticky; ?></wp:is_sticky>
                    <?php	if ( $post->post_type == 'attachment' ) : ?>
                        <wp:attachment_url><?php echo wp_get_attachment_url( $post->ID ); ?></wp:attachment_url>
                    <?php 	endif; ?>
                    <?php 	$this->wxr_post_taxonomy(); ?>
                    <?php	$postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post->ID ) );
                    foreach ( $postmeta as $meta ) :
                        /**
                         * Filter whether to selectively skip post meta used for WXR exports.
                         *
                         * Returning a truthy value to the filter will skip the current meta
                         * object from being exported.
                         *
                         * @since 3.3.0
                         *
                         * @param bool   $skip     Whether to skip the current post meta. Default false.
                         * @param string $meta_key Current meta key.
                         * @param object $meta     Current meta object.
                         */
                        if ( apply_filters( 'wxr_export_skip_postmeta', false, $meta->meta_key, $meta ) )
                            continue;
                        ?>
                        <wp:postmeta>
                            <wp:meta_key><?php echo $meta->meta_key; ?></wp:meta_key>
                            <wp:meta_value><?php echo $this->wxr_cdata( $meta->meta_value ); ?></wp:meta_value>
                        </wp:postmeta>
                    <?php	endforeach;

                     ?>
                    <!-- for hoangweb -->
                    <skin>

                    </skin>
                    <?php

                    ?>
                </item>
            <?php
            }
        }
    } ?>
        </posts>
</rss>    