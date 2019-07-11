<?php
/*
* Template Name: Floor Plans
*
*/
get_header(); ?>
	<!-- <script type="text/javascript">// var fp_email = "<?php the_field('floor_plan_contact_email'); ?>";</script> -->
	<?php print_hero_image(); ?>
  <div id="floorplans" class="container" role="main">
    <div class="content">
    	<div class="ddl-label">Select Apartment Type:</div>
    	<div class="category-filter">
	    	<ul id="floor-plan-categories"></ul>
	    </div>
    	<div id="floor-plan-results"><div id="loader"><i class="fas fa-spinner fa-pulse"></i></div></div>
    	<table id="result-template-container" style="display:none;">
	    	<tr id="result-template" role="row">
	    		<td class="apt-name"></td>
	    		<td class="price"></td>
	    		<td class="available"></td>
	    		<td class="apply"><div class="button"><a href="">Apply</a></div></td>
	    		<td class="mobile">
		    		<div class="apt-name"></div>
		    		<div class="price"></div>
		    		<div class="available"></div>
		    		<div class="apply"><div class="button"><a href="">Apply</a></div></div>
	    		</td>
	    	</tr>
	    </table>

			<?php print_no_results_copy(); ?>
			<?php print_floorplan_disclaimer_copy(); ?>
    	<?php print_crosslink_block(); ?>
    </div>
  </div>
<?php get_footer(); ?>