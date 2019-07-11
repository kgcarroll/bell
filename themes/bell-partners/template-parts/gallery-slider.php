<div id="slider-gallery">
	<div class="ddl-label">Filter by Category:</div>
	<?php print_categories(); ?>
	<div class="slider-wrapper">
		<ul id="image-gallery" class="slider"></ul>
	</div>
	<?php if(get_field('include_slider_thumbnails')) { echo '<div class="thumbnail-slider-wrapper"><ul id="slick-thumbs" class="slick-thumbs" ></ul></div>'; }?>
</div>
</div>
<?php print_gallery_headline(); ?>
<?php print_videos(); ?>
<?php print_tours(); ?>