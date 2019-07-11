<?php
/*
* Template Name: Blog
*
*/
get_header(); ?>
<div id="blog" class="container">
    <div class="left">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <div class="post">
                    <div class="post-content">
                        <h1 class="post-title"><?php wp_title(''); ?></h1>
                        <div class="image">
                            <?php
                            if ( has_post_thumbnail() ) {
                                the_post_thumbnail();
                            } ?>
                        </div>
                        <?php the_content(); ?>
                    </div>
                </div>
            <?php endwhile;?>
        <?php endif;?>
    </div>
    <div class="right">
        <?php get_sidebar(); ?>
    </div>
</div>
<?php get_footer(); ?>