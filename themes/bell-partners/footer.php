    <?php print_footer_pattern(); ?>
    <footer id="footer">
        <div class="content">
					<div class="contact-info">
						<?php echo '<div class="property-name">'.get_bloginfo('name').'</div>'; ?>
						<?php print_address(); ?>
						<?php print_phone_number(); ?>
						<?php print_social_icons(); ?>
					</div>
					<div class="management-info">
						<div class="bal">
							<a href="https://www.bellapartmentliving.com/" target="_blank" title="Bell Apartment Living"><span class="icon-bell_partners_footer"></span></a>
						</div>
						<?php print_footer_logos(); ?>
						<div class="sub-footer">
							<nav id="footer-navigation">
								<?php wp_nav_menu( array( 'container' => '', 'theme_location' => 'footer_menu', 'menu_class'=>'menu-footer-menu') ); ?>
							</nav>
							<div class="eho-handicap">
								<span class="icon-handi"></span>
								<span class="icon-eho"></span>
							</div>
						</div>
					</div>
        </div>
        <?php enable_chat_bot(); ?>
        <?php enable_text_bot(); ?>
    </footer>
	<?php wp_footer();?>
	</body>
</html>