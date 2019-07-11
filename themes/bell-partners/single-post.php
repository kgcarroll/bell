<?php get_header(); ?>
<?php print_hero_image(); ?>
    <div id="single-post" class="container">
        <div class="post-wrapper">
            <div class="post-content">
                <div class="left">
                    <?php print_page_building_blocks(); ?>                
                </div>
                <div class="right">
                    <?php print_back_to_blog(); ?>
                    <?php print_blog_categories(); ?>
                </div>
            </div>
        </div>
        <?php print_blog_page_building_blocks(); ?>
    </div>
<?php get_footer(); ?>
