<?php

	function my_acf_admin_head() {
	  // Admin Stylesheet (compiled sass)
	  wp_register_style('admin-css', get_bloginfo('template_directory') . '/css/admin.css' );
	  wp_enqueue_style('admin-css');
	}

	add_action('acf/input/admin_head', 'my_acf_admin_head');


	// This enqueues the Metro Admin stylesheet (compiled sass), and hides Metro Options on the admin screen, rendering them useless.
	function hide_metro_options() {
	  wp_register_style('metro-css', get_bloginfo('template_directory') . '/css/hide_metro.css' );
	  wp_enqueue_style('metro-css');
	}
	if ( get_field('metro_sylvan', 'options') === 'sylvan') {
		add_action('acf/input/admin_head', 'hide_metro_options');
	}

	// This enqueues the Sylvan Admin stylesheet (compiled sass), and hides Sylvan Optons on the admin screen, rendering them useless.
	function hide_sylvan_options() {
	  wp_register_style('sylvan-css', get_bloginfo('template_directory') . '/css/hide_sylvan.css' );
	  wp_enqueue_style('sylvan-css');
	}
	if ( get_field('metro_sylvan', 'options') === 'metro') {
		add_action('acf/input/admin_head', 'hide_sylvan_options');
	}


		
	add_action('acf/input/admin_footer', 'my_acf_admin_footer');
	function my_acf_admin_footer() {
	
	// use the javascript below to change labels on admin screen.
?>
	<script type="text/javascript">
	(function($) {
		
		$('.acf-field-5cbb6ec6306ca a.acf-gallery-add').text('Add Floor Plan Image'); // Change label of Floor Plan Image Upload
		$('.acf-field-5cbb6eea306cb a.acf-gallery-add').text('Add Floor Plan PDF'); // Change label of Floor Plan PDF Upload
		$('.acf-field-5cbb7654925cb a.acf-gallery-add').text('Add Floor Plan Image'); // Change label of Floor Plan PDF Upload
		
	})(jQuery);	
	</script>
<?php } ?>