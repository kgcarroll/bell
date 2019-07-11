<div id="navigation-container" role="region" class="" aria-expanded="false">
  <div class="inner-container">
    <?php print_resident_portal_link(); ?>
    <div class="navigation-wrapper">
      <nav id="navigation" role="navigation" aria-labelledby="navigation-label" class="nav-text">
        <h2 id="navigation-label" class="accessible">Main Navigation</h2>
        <?php wp_nav_menu( array( 'container' => '', 'theme_location' => 'main_menu', 'menu_class'=>'menu-main-menu') ); ?>
      </nav>

      <?php print_social_icons(); ?>
    </div>

  </div>
</div>