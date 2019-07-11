<?php 
/*
* Template Name: Contact
*
*/
get_header(); ?>
  <?php print_hero_image(); ?>
  <div id="contact" class="container" role="main">
    <div class="content">
    	<div class="contact-info">
        <div class="property-info">
      		<?php echo '<h2 class="label">'.get_bloginfo('name').'</h2>'; ?>
      		<?php print_address(); ?>
      		<?php print_phone_number(); ?>
      		<?php print_social_icons(); ?>
        </div>
    		<?php print_office_hours(); ?>
      </div>
      <div class="contact-form">
        <?php if(get_property_type() === 'sylvan') { echo '<h2 class="contact-form-header">Contact Us</h2>'; } ?>
        <?php echo FrmFormsController::get_form_shortcode( array( 'id' => 2, 'title' => false, 'description' => false ) ); ?> 
      </div>
      <?php if(get_property_type() === 'metro') {
        print_crosslink_block();
        get_kingsley_reviews();
      } else {
        print_pattern_break_block('0');
        get_kingsley_reviews();
        print_pattern_break_block('1');
        print_crosslink_block();
      } ?>
    </div>
  </div>
<?php get_footer(); ?>