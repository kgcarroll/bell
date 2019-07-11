<?php

add_action ('init', 'init_template');

function init_template(){
  setup_template_theme();
  add_action( 'wp_enqueue_scripts', 'enqueue_template_scripts' );
  add_action( 'wp_enqueue_scripts', 'enqueue_template_styles' );
}

// Override 'Howdy' message, because we're professionals.
function howdy_message($translated_text, $text, $domain) {
  $new_message = str_replace('Howdy', 'Welcome', $text);
  return $new_message;
}
add_filter('gettext', 'howdy_message', 10, 3);


function setup_template_theme(){
  // Set up schema
  add_action('wp_head', 'create_schema');
  function create_schema() {
    $schema = array(
      '@context' => "http://schema.org", // Tell search engines that this is structured data
      '@type' => "ApartmentComplex", // Tell search engines the content type it is looking at 
      'name' => get_bloginfo('name'), // Provide search engines with the site name and address 
      'url' => get_home_url(),
      'address' => array(
        '@type' => "PostalAddress",
        'addressCountry' => "United States",
        'streetAddress' => get_field('address', 'options'),
        'postalCode' => get_field('zip', 'options'),
        'addressLocality' => get_field('city', 'options'),
        'addressRegion' => get_field('state', 'options'),
        'telephone' => clean_phone(get_field('phone', 'options')),
        'description' => get_field('schema_description', 'options')
      ),
      'map' => get_field('google_places_map_link', 'options'),
      'telephone' => '+1' . clean_phone(get_field('phone', 'options')) // Provide the company address - needs country code
    );

    // If there is a company logo...
    if ($logo = get_field('logo', 'options')) {
      $schema['logo'] = $logo['url'];// ...then add it to the schema array
    }
    // Check for social media links
    if (have_rows('social_media_accounts', 'options')) {
      $schema['sameAs'] = array();  
      // For each instance...
      while (have_rows('social_media_accounts', 'options')) : the_row();
        array_push($schema['sameAs'], get_sub_field('url')); // ...add it to the schema array
      endwhile;
    }
    echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
  }


  // Remove added padding from Admin Bar
  add_action('get_header', 'remove_admin_login_header');
  function remove_admin_login_header() {
    remove_action('wp_head', '_admin_bar_bump_cb');
  }

  // Setting up theme
  if (function_exists('add_theme_support')) {
    add_theme_support('menus');
    add_theme_support( 'post-thumbnails' );
  }

  if (function_exists('register_sidebar')){
    register_sidebar(array('name'=>'Sidebar %d'));
  }

  if (function_exists('register_nav_menu')) {
    register_nav_menu( 'main_menu', 'Main Menu' );
    register_nav_menu( 'secondary_menu', 'Secondary Menu' );
    register_nav_menu( 'footer_menu', 'Footer Menu' );
  }

  if (function_exists('add_image_size')){
    add_image_size('home_floor_plan', 0, 260);
    add_image_size('home_slide', 1480, 835, true);
    add_image_size('home_slide_full', 1680, 835, true);
    add_image_size('home_slide_secondary', 540, 365, true);
    add_image_size('home_slide_secondary_sm', 410, 270, true);
    add_image_size('features_header', 680, 320, true);
    add_image_size('mobile_home_slide', 375, 435, true);
    add_image_size('mobile_home_slide_full', 375, 553, true);
    add_image_size('variable_img_small', 212, 385, true);
    add_image_size('variable_img_large', 525, 385, true);
    add_image_size('copy_and_images_img', 300, 300, true);
    add_image_size('list_img_single', 525, 610, true);
    add_image_size('list_img_lrg', 0, 340, true);
    add_image_size('list_img_small', 252, 252, true);
    add_image_size('masonry_thumb_hardcrop', 408, 272, true);
    add_image_size('masonry_thumb_softcrop', 408, 272);
    add_image_size('masonry_featured', 838, 564, true);
    add_image_size('full', 838, 564);
    add_image_size('slider', 0, 675);
    add_image_size('slider_thumbnail', 0, 115);
    add_image_size('gallery_thumb', 265, 160);
    add_image_size('blog_thumb', 390, 264, true); 
    add_image_size('three_images', 408, 278, true); 
    add_image_size('blog_full', 845, 475);
    add_image_size('sylvan_blog_full', 622, 408, true);
    add_image_size('in_blog', 425, 239);
    add_image_size('kingsley', 143, 174);
  }
  
  if( function_exists('acf_add_options_page') ) {
  	acf_add_options_page(array(
  		'page_title' 	=> 'Global Settings',
  		'menu_title'	=> 'Global Settings',
  		'menu_slug' 	=> 'global-settings',
  		'capability'	=> 'edit_posts',
  		'redirect'		=> false
  	));  	
  }

  // Set Google Maps API for Advanced Custom Fields use
  function my_acf_google_map_api( $api ){
    $api['key'] = 'AIzaSyBLGDbL7PkNbZLKg2BRtIb6anAkSnl0Y_Y';
    return $api;
  }
  add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');
}

function get_property_type() {
  $type = get_field('metro_sylvan','options');
  return $type;
}

function get_color_scheme() {
  $theme_color = get_field(get_property_type().'_color_scheme','options');
  $color_scheme = (isset($theme_color)) ? $theme_color : 'default' ;
  return $color_scheme;
}

function get_pattern_id() {
  $patternID = get_field(get_property_type().'_patterns','options');
  return $patternID;
}


function get_density() {
  $pattern_density = 'pattern-'.get_field('pattern_density','options');
  return $pattern_density;
}


function get_font_set() {
  $font = get_field(get_property_type().'_font','options');
  $link = array();
  if(get_property_type() === 'metro') {
    switch ($font) {
      case 'one':
        $link['font'] = 'https://fonts.googleapis.com/css?family=Nunito+Sans|Rokkitt:900';
        $link['style'] = 'metro-one';
        break;
      case 'two':
        $link['font'] = 'https://fonts.googleapis.com/css?family=Fjalla+One|Nunito+Sans';
        $link['style'] = 'metro-two';
        break;
      case 'three':
        $link['font'] = 'https://fonts.googleapis.com/css?family=IBM+Plex+Serif';
        $link['style'] = 'metro-three';
        break;
      default:
        $link['font'] = 'https://fonts.googleapis.com/css?family=Nunito+Sans|Rokkitt';
        $link['style'] = 'metro-one';
        break;
    }
  } elseif(get_property_type() === 'sylvan') {
    switch ($font) {
      case 'one':
        $link['font'] = 'https://fonts.googleapis.com/css?family=IBM+Plex+Serif|Nunito+Sans';
        $link['style'] = 'sylvan-one';
        break;
      case 'two':
        $link['font'] = 'https://fonts.googleapis.com/css?family=Chivo|IBM+Plex+Serif';
        $link['style'] = 'sylvan-two';
        break;
      case 'three':
        $link['font'] = 'https://fonts.googleapis.com/css?family=Nunito+Sans|Poppins:600';
        $link['style'] = 'sylvan-three';
        break;
      default:
        $link['font'] = 'https://fonts.googleapis.com/css?family=IBM+Plex+Serif|Nunito+Sans';
        $link['style'] = 'sylvan-one';
        break;
    }
  }
  return $link;
}

function get_font_css($file) {
  $font = get_font_set();
  $css_file = $font[$file];
  return $css_file;
}

function enqueue_template_styles() {
  // Style Sheet Theme Information
  wp_register_style('style-css', get_bloginfo('template_directory') . '/style.css' );
  wp_enqueue_style('style-css');

  // Core Stylesheet (compiled sass)
  wp_register_style('screen-css', get_bloginfo('template_directory') . '/css/screen.css' );
  wp_enqueue_style('screen-css');

  // Add IE8 Conditional Styles
  wp_enqueue_style( 'ie8-css', get_bloginfo('template_directory') . '/css/ie.css' );
  wp_style_add_data( 'ie8-css', 'conditional', 'lt IE 8' );

  // wp_register_style('icomoon-css','//s3.amazonaws.com/icomoon.io/145852/BellApartmentLiving/style.css?ru11ev');
  // wp_enqueue_style('icomoon-css');

  wp_register_style('icomoon-css','//s3.amazonaws.com/icomoon.io/145852/BellApartmentLiving/style.css?cqw03e');
  wp_enqueue_style('icomoon-css');  

  if(is_front_page() || is_page_template('page-templates/gallery.php') || is_page_template('page-templates/contact.php')) {
    // Slick CSS
    wp_register_style('slick-css','//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css');
    wp_enqueue_style('slick-css');
  }

  // Google Stylesheet
  wp_register_style('google-font', get_font_css('font') );
  wp_enqueue_style('google-font');

  // Font Stylesheet (compiled sass)
  wp_register_style('font-css', get_bloginfo('template_directory') . '/css/fonts/'.get_font_css('style').'.css' );
  wp_enqueue_style('font-css');

  // Pattern Stylesheet (compiled sass)
  wp_register_style('pattern-css', get_bloginfo('template_directory') . '/css/patterns.css' );
  wp_enqueue_style('pattern-css');

  // Color Scheme Stylesheet (compiled sass)
  wp_register_style('colors-css', get_bloginfo('template_directory') . '/css/schemes/'.get_color_scheme().'.css' );
  wp_enqueue_style('colors-css');

  wp_enqueue_style( 'ie-patterns-css', get_bloginfo('template_directory') . '/css/ie_patterns.css' );
  // wp_style_add_data( 'ie-patterns-css');

}


function enqueue_template_scripts() {

  // HTML5Shiv for old browsers
  wp_enqueue_script( 'html5shiv', get_bloginfo('template_directory') . '/js/html5shiv.js' );
  wp_script_add_data( 'html5shiv', 'conditional', 'lt IE 8' );

  // Font Awesome
  wp_register_script('font-awesome','//use.fontawesome.com/releases/v5.7.2/js/all.js');
  wp_enqueue_script('font-awesome');
  
  // Google hosted jQuery
  wp_deregister_script( 'jquery' ); 
  wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js' );
  wp_enqueue_script( 'jquery' );

  //Modernizr for dapting images to IE 
  // wp_deregister_script( 'modernizr-js' ); //
  // wp_register_script( 'modernizr-js', get_bloginfo('template_directory') . '/js/vendor/modernizr-custom.js', array(), false, true );
  // wp_enqueue_script( 'modernizr-js' );

  // Custom Theme Script
  wp_register_script( 'custom-js', get_bloginfo('template_directory') . '/js/src/template.js', array ( 'jquery'), false, true );
  wp_enqueue_script( 'custom-js' );


  // if(is_page_template('page-templates/gallery.php')) {
  //   // // Home JS
  //   // wp_register_script('home-js', get_bloginfo('template_directory') . '/js/src/home.js', array ( 'jquery', 'slick-js' ), false, true );
  //   // wp_enqueue_script('home-js');
  // }

  if(is_page_template('page-templates/floor-plans.php') || is_page_template('page-templates/gallery.php')) {
    // Images Loaded JS
    wp_register_script('imagesLoaded-js', '//unpkg.com/imagesloaded@4/imagesloaded.pkgd.js', array ( 'jquery' ), false, true );
    wp_enqueue_script('imagesLoaded-js');

    // Isotopes JS library (for masonry gallery)
    wp_register_script('isotopes-js', '//npmcdn.com/isotope-layout@3/dist/isotope.pkgd.js', array ( 'jquery', 'imagesLoaded-js' ), false, true );
    wp_enqueue_script('isotopes-js');

    // Packery JS (for layout)
    wp_register_script('packery-js', '//npmcdn.com/isotope-packery@2/packery-mode.pkgd.js', array ( 'jquery', 'isotopes-js' ), false, true );
    wp_enqueue_script('packery-js');
  }

  if(is_page_template('page-templates/gallery.php')) {   
    // Slick JS
    wp_register_script('slick-js', '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array ( 'jquery' ), false, true );
    wp_enqueue_script('slick-js');

    // Custom Gallery JS
    wp_register_script( 'gallery-js', get_bloginfo('template_directory') . '/js/src/gallery.js', array ( 'jquery', 'slick-js' ), false, true );
    wp_enqueue_script( 'gallery-js' );
  }

  if(is_page_template('page-templates/neighborhood.php')){
    // Google Maps API
    wp_deregister_script( 'google-maps' );
    wp_register_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBLGDbL7PkNbZLKg2BRtIb6anAkSnl0Y_Y' );
    wp_enqueue_script( 'google-maps' );
    
    // Map JS
    wp_register_script( 'poi-map-js', get_bloginfo('template_directory') . '/js/src/map.js', array ( 'google-maps' ), false, true);
    wp_enqueue_script( 'poi-map-js' );
  }


  if(is_page_template('page-templates/floor-plans.php')){
    // Floor Plans JS
    wp_register_script( 'floorplans-js', get_bloginfo('template_directory') . '/js/src/floorplans.js' );
    wp_enqueue_script( 'floorplans-js' );
  }

  if(is_page_template('page-templates/contact.php')){
    // Slick JS
    wp_register_script('slick-js', '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', array ( 'jquery' ), false, true );
    wp_enqueue_script('slick-js');
  }


  global $post;
  $ajax_theme_data = array(
    'siteURL' => get_bloginfo('url'),
    'templateURL' => get_bloginfo('template_directory'),
    'slug' => $post->post_name,
    'property_name' => get_bloginfo('name'),
    'address' => get_field('address','options'),
    'city' => get_field('city','options'),
    'state' => get_field('state','options'),
    'zip' => get_field('zip','options'),
    'latitude' => get_field('latitude','options'),
    'longitude' => get_field('longitude','options'),
    'property_map_marker' => get_field('property_map_marker','options')
  );
  wp_localize_script('custom-js', 'themeData', $ajax_theme_data);
}

// Adds page slug to Body Class function
function add_slug_body_class( $classes ) {
  global $post;
  if ( isset( $post ) ) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  $classes[] = get_property_type(); // Add property type to body.
  $classes[] = get_color_scheme(); // Add color scheme to body.
  return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );

// Takes an alphabetic character and returns the phone numeric equivalent
function alpha_to_phone($char){
	$conversion = array('a' => 2, 'b' => 2, 'c' => '2', 'd' => 3, 'e' => 3, 'f' => 3, 'g' => 4, 'h' => 4, 'i' => 4, 'j' => 5, 'k' => 5, 'l' => 5, 'm' => 6, 'n' => 6, 'o' => 6, 'p' => 7, 'q' => 7, 'r' => 7, 's' => 7, 't' => 8, 'u' => 8, 'v' => 8, 'w' => 9, 'x' => 9, 'y' => 9, 'z' => 9);
	return $conversion[$char];
}

// Takes phone number, returns cleaned numeric equivalent for mobile link functionality
function clean_phone($phone){
	$chars = str_split(strtolower($phone));
	$phone = '';
	foreach($chars as $char){
		if (ctype_lower($char)){
			$char = alpha_to_phone($char);
		}
		$phone .= $char;
	}
	$phone = preg_replace("/[^0-9]/", "",$phone);
	return $phone;
}
// Create Site Options - uncomment if needed.
// require_once( get_template_directory() . '/incl/acf_site_options.php' );

// Custom Functions
require_once( get_template_directory() . '/incl/custom-functions.php' );

// Page Blocks
require_once( get_template_directory() . '/incl/page-blocks.php' );

// Get Gallery JSON data
require_once( get_template_directory() . '/incl/save-gallery-json.php' );

// Backend hooks for custom features.
require_once( get_template_directory() . '/incl/backend-hooks.php' );


// Custom Metabox with Onclick to open/close flex boxes.
// function add_admin_js($hook) {
//   // Only add to the edit.php admin page.
//   if ('post.php' !== $hook) {
//     return;
//   }
//   wp_enqueue_script('admin-js', get_bloginfo('template_directory') . '/js/src/admin.js');
// }

// add_action('admin_enqueue_scripts', 'add_admin_js');
// function close_flex_content() {
//   add_meta_box(
//     'flex-content-close',         // Unique ID
//     'Close Flex Content Fields',  // Box title
//     'close_flex_content_html',    // Content callback, must be of type callable
//     'page',                        // Post type
//     'side'                         // Postion in sidebar
//   );
// }
// add_action('add_meta_boxes', 'close_flex_content');

// function close_flex_content_html($post){
//   echo '<div class="description"><p>This will close all open flex content layouts.</p></div>';
//   echo '<div name="flex_fields" id="flex_fields" class="button button-primary button-large" onclick="toggle_flex();">Close Flex Content Fields</div>';
// }


// Location for Master Update
require get_template_directory() . '/updater/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
  'https://www.mtcthemes.com/releases/bellpartners/theme.json',
  __FILE__, //Full path to the main plugin file or functions.php.
  'bell-partners'
);

// Remove YOAST JSON-LD SCHEMA (We make our own.)
function bybe_remove_yoast_json($data){
  $data = array();
  return $data;
}
add_filter('wpseo_json_ld_output', 'bybe_remove_yoast_json', 10, 1);

// Hide ACF from WP Admin Menu
// add_filter('acf/settings/show_admin', '__return_false');