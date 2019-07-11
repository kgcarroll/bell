<?php get_header();?>
	<?php print_hero_image('blog'); ?>
	<div id="blog" class="container" role="main">
	  <div class="content">
			<?php print_blog_categories(); ?>
			<?php print_blog_posts(); ?>
			<?php print_page_building_blocks('blog'); ?>
			<?php print_crosslink_block('blog'); ?>
	  </div>
	</div>
<?php get_footer(); ?>