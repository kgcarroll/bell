<!DOCTYPE html>
<html>
	<head>
	  <title><?php wp_title(''); ?></title>
    <?php print_favicons(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <?php print_header_GTM_code(); ?>

	  <?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>

    <?php print_body_GTM_code(); ?>

    <div id="wrapper">
      <header id="header" class="header <?php print_slideout(false); ?>">
        <?php print_slideout(); ?>
        <div class="header-wrapper">    
          <?php print_logo(); ?>
          <?php print_phone_number(); ?>
          <nav id="secondary-navigation">
            <?php wp_nav_menu( array( 'container' => '', 'theme_location' => 'secondary_menu', 'menu_class'=>'menu-secondary-menu') ); ?>
          </nav>

          <?php print_mobile_phone_link(); ?>
          <?php print_mobile_address_link(); ?>
          
        </div>
      </header>
      <?php print_trigger(); ?>
      <?php get_template_part('template-parts/navigation', 'main'); ?>