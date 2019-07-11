<?php
/*
  * Template Name: Neighborhood
  *
  *
  */
get_header();?>
	<div id="neighborhood" class="container" role="main">
	  <div class="content">
			<?php print_poi_map(); ?>
			<?php print_neighborhood_content(); ?>
			<?php print_page_building_blocks(); ?>
	  </div>
	</div>
<?php get_footer(); ?>
