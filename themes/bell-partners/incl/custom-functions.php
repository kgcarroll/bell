<?php

// This file holds the different custom functions.  Most of these functions refer to
// fields created with the ACF plugin, and will not work unless the fields have been defined.
// There is a .json file that is included with the respository that can be imported to create the fields.


// Responsive Images for ACF fields
function print_responsive_image($image,$image_size,$alt = null){
  // echo '<pre>'.print_r($image).'</pre>';
  // Check the image ID is not blank
  if($image['ID'] != '') {
    // Set the default src image size
    $image_src = wp_get_attachment_image_url( $image['ID'], $image_size );

    // Get image width from image array.
    $image_width = $image['sizes'][''.$image_size.'-width'];
        
    // Set the srcset with various image sizes
    $image_srcset = wp_get_attachment_image_srcset( $image['ID'], $image_size );
    // Generate the markup for the responsive image
    echo '<img src="'.$image_src.'" srcset="'.$image_srcset.'" sizes="(max-width: '.$image_width.'px) 100vw, '.$image_width.'px" alt="'.$image['alt'].'" />';
  }
}

function print_header_GTM_code(){
  if($gtm = get_field('gtm','options')) {
    foreach ($gtm as $key => $v) {
      echo "<!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','".$v['gtm_id']."');</script>
            <!-- End Google Tag Manager --> \r\n";
    }
  }
}

function print_body_GTM_code(){
  if($gtm = get_field('gtm','options')) {
    foreach ($gtm as $key => $v) {
      echo "<!-- Google Tag Manager (noscript) -->
            <noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=".$v['gtm_id']."\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) --> \r\n";
    }
  }
}

function print_favicons(){
  if($icon = get_field('favicon','options')) {
    //echo '<link rel="shortcut icon" href="/'.$icon['filename'].'"/>';
    echo '<link rel="shortcut icon" href="'.$icon['url'].'"/>';
  }
}

// Custom Excerpt function for ACFs in flex content field
function flex_content_excerpt($flex, $layout, $subfield) {
  global $post;
  if (have_rows($flex)) {
    while(have_rows($flex)){
      the_row();
      if( get_row_layout() == $layout ) { 
        $text = get_sub_field($subfield);
        if ( '' != $text ) {
          $text = strip_shortcodes($text);
          $text = apply_filters('the_content', $text);
          $text = str_replace(']]&gt;', ']]&gt;', $text);  // Strip out HTML
          $excerpt_length = 15; // Number of words.
          $excerpt_more = apply_filters('excerpt_more', '...');
          $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
        }
      }
    }
  }
  return apply_filters('the_excerpt', $text);
}

function print_logo(){
  if($logo = get_field('logo', 'options')) {
    echo '<div id="logo">';
 	  	echo '<a class="logo" href="'. home_url('') .'">';
   			echo '<img src="'. $logo['url'] .'" />';
    	echo '</a>';
      echo '<a class="logo-scroll" href="'. home_url('') .'">';
        echo get_bloginfo('name');
      echo '</a>';
    echo '</div>';
  }
}

function print_trigger() {
  echo '<div role="button" aria-label="Open Navigation" aria-pressed="false" id="nav-trigger" class="inactive nav-trigger" tabindex="0">';
    echo '<div class="trigger-wrap">';
      echo '<span class="line"></span>';
      echo '<span class="line"></span>';
      echo '<span class="line"></span>';
    echo '</div>';
  echo '</div>';
}


function print_slideout_content(){
  $button = get_field('trigger_text','options');
  $title = get_field('slideout_headline','options');
  $copy = get_field('slideout_copy','options');
  $phoneIncluded = (get_field('include_phone_number','options'));
  $phone = (get_field('phone','options'));
  $link = get_field('slideout_content_link','options');
  echo '<div id="slideout" aria-expanded="" role="region" class="">';
    echo '<div id="slideout-button-container">';
      echo '<div class="button-wrap" role="button" tabindex="0" aria-label="Open Special Content" aria-pressed="false">';
        echo $button.' <i class="fas fa-chevron-down"></i>';
        // echo $button;
      echo '</div>';
    echo '</div>';
    echo '<div id="slideout-content" style="">';
      if($title) { echo '<div class="title">'.$title.'</div>'; }
      if($copy) { echo '<div class="copy">'.$copy.'</div>'; }
      if($disclaimer = get_field('slideout_disclaimer','options')) { echo '<div class="disclaimer">'.$disclaimer.'</div>'; }
      if($phoneIncluded) { echo '<div class="phone"><a href="tel:+1'.clean_phone($phone).'">Call '.$phone.' for details.</a></div>'; }
      if($link) { print_link($link, 'testing'); }
    echo '</div>';
  echo '</div>';
}

// Print the pop out if it meets the logic below.
function print_slideout($slide = true) {
  // Check if scheduled. (Overrides default activation switch.)
  if($scheduled = get_field('schedule_specials','options')) {
    $start = strtotime(get_field('start_time','options'));
    $end = strtotime(get_field('end_time','options'));
    $today = (time() - 14400);  // UTC with 4 hours subtracted to make Eastern Time.
    // Checks to make sure today is within scheduled time.
    if($today >= $start && $today <= $end) {
      if($slide) { print_slideout_content(); }
      if(!$slide) { echo 'active'; } // Header class helper.
    }
  }
  // Check to see if its even active.
  elseif ($activated = get_field('activate_slideout','options')) {
    if($slide) { print_slideout_content(); }
    if(!$slide) { echo 'active'; } // Header class helper.
  }
}

function print_social_icons(){
  if($social = get_field('social_media_accounts','options') ){
    echo '<div class="social-icons">';
      foreach ($social as $icon) {
        $domain = parse_url($icon['url']);
        $type = strtolower($icon['social_media_type']);
        echo '<a class="icon" href="'.$icon['url'].'" target="_blank" aria-label="'.substr($domain['host'],4).'">';
          echo '<i class="'.$type.'"></i>';
        echo '</a>';
      }
    echo '</div>';
  }
}

function print_phone_number(){
  if ($phone = get_field('phone','options')){
    echo '<div class="phone">';
      echo '<a href="tel:+1'.clean_phone($phone).'" class="phone-number ease">'.$phone.'</a>';
    echo '</div>';
  }
}

function print_address() {
  $address = get_field('address', 'options');
  $city = get_field('city', 'options');
  $state = get_field('state', 'options');
  $zip = get_field('zip', 'options');

  if($address){
    echo '<div class="address-container">';
      echo '<a href="https://www.google.com/maps/place/'.$address.'+'.$city.'+'.$state.'+'.$zip.'" target="_blank">';
        echo '<div class="address line-1">'.$address.'</div><div class="address break">,&nbsp;</div>';
        echo '<div class="address line-2">'.$city.', '.$state.', '.$zip.'</div>';
        echo '</a>';
    echo '</div>';
  }
}

function print_mobile_address_link(){
  $address = get_field('address', 'options');
  $city = get_field('city', 'options');
  $state = get_field('state', 'options');
  $zip = get_field('zip', 'options');
  if($address){
    echo '<div class="link mobile-icon"><a class="address-link" href="https://maps.google.com/?daddr='.$address.', '.$city.', '.$state.' '.$zip.'" target="_blank"><i class="fas fa-map-marker-alt"></i></a></div>';
  }
}

function print_mobile_phone_link(){
  if ($phone = get_field('phone','options')){
    echo '<div class="link mobile-icon"><a href="tel:+1'.clean_phone($phone).'" class="phone-link" aria-label="Click to Call"><i class="fas fa-phone"></i></a></div>';
  }
}


function print_resident_portal_link() {
  if($residents = get_field('resident_portal_link', 'options')) {
    echo '<div class="residents"><a href="'.$residents['url'].'" target="'.$residents['target'].'">'.$residents['title'].'</a></div>';
  }
}

function print_office_hours(){
  if($hours = get_field('office_hours','options') ) {
    echo '<div class="office-hours">';
      echo '<h2 class="label">Office Hours</h2>';
    foreach($hours as $day) {
      echo '<div class="day"><span class="hours">'.$day['day'].' - '.$day['hours'].'</span></div>';
    }
    echo '</div>';
  }
}

function enable_chat_bot(){
  if(get_field('enable_chat_bot','options')){
    $chatID = get_field('chat_us_id','options');
    echo '<script defer="defer" src="https://uc-widget.realpageuc.com/widget?wid='.$chatID.'"; type="text/javascript"></script>';
  }
}

function enable_text_bot(){
  if(get_field('enable_text_bot','options')){
    $text_ID = get_field('text_us_id','options');
    echo '<script src="https://popcard.rentcafe.com/js/TextUsWidget.js?dnis='.$text_ID.'" id="myScript" DNIS="'.$text_ID.'" data-url="'.$text_ID.'"></script>';
  }
}

function print_footer_logos(){
  if($logos = get_field('footer_logos','options') ){
    echo '<div class="management-logos">';
      foreach ($logos as $manager) {
        $domain = parse_url($manager['url']);
        $logo = $manager['logo']['url'];
        $alt = $manager['logo']['alt'];
        if($manager['url'] != ""){
          echo '<div class="manager"><a href="'.$manager['url'].'" target="_blank" aria-label="'.substr($domain['host'],4).'">';
            echo '<img src="'.$logo.'" alt="'.$alt.'" />';
          echo '</a></div>';
        } else { echo '<div class="manager"><img src="'.$logo.'" /></div>'; }
      }
    echo '</div>';
  }
}

function print_footer_pattern() {
  if(is_front_page()){
    if(get_density() != 'pattern-none') {
      echo '<div id="footer-pattern" class="pattern-wrapper"><div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div></div>';
    }
  }
}

function print_categories() {
  echo '<div id="loader"><i class="fas fa-spinner fa-pulse"></i></div>';
  echo '<ul id="categories"></ul>';
}

// Homepage Functions
function print_home_header() {
  // Variable Assignement
  $style = get_field('header_style');
  $headline = get_field('headline_h1');
  $copy = get_field('header_copy');
  $link = get_field('link'); 
  if(get_property_type() === 'sylvan') {
    $font_color = (get_field('sylvan_override_colors')) ? get_field('caption_text_color') : 'default' ;
    $caption_color = (get_field('sylvan_override_colors')) ? get_field('caption_background_color') : 'default' ;
  }
  // For Metro
  else {
    $font_color = (get_field('override_colors')) ? get_field('caption_text_color') : 'default' ;
    $caption_color = (get_field('override_colors')) ? get_field('caption_background_color') : 'default' ;
  }
  $class = ($style === '0') ? $class = 'style-1' : 'style-2' ;

  if($image = get_field('header_image')) {
    $caption = get_field('concept_headline');
    echo '<div id="hero-container">';
      echo '<div id="hero-image" class="'.$class.'">';
        if(get_property_type() === 'sylvan') { echo '<div class="pattern pattern-'.get_pattern_id().' '.$class.' mobile"></div>'; }
        // Image
        echo '<div class="img-container">';
          if($style === '0') {
            if(wp_is_mobile()) {
              print_responsive_image($image,'mobile_home_slide');
            } else {
              print_responsive_image($image,'home_slide');
            }
          } elseif ($style === '1') {
            if(wp_is_mobile()) {
              print_responsive_image($image,'mobile_home_slide_full');
            } else {
              print_responsive_image($image,'home_slide_full');
            }
          }
        echo '</div>';
        // Add Secondary Header Image for Sylvan Header Option 2
        if(get_property_type() === 'sylvan') {
          if($style === '1') {
            if($secondary_image = get_field('secondary_image')) {
              echo '<div class="secondary-image">';
                print_responsive_image($secondary_image,'home_slide_secondary');
              echo '</div>';  
            }
          }
        }
        // Caption
        if($caption) { 
          if(get_property_type() === 'sylvan') {
            echo '<div class="caption '.$caption_color.'"><span class="'.$font_color.'">'.$caption.'</span></div>';
            if($style === '1') { echo '<div class="sylvan-home-caption-background"></div>'; }
          }
          // For Metro
          else {
            if($style === '0') {
              echo '<div class="caption '.$caption_color.'"><span class="'.$font_color.'">'.$caption.'</span></div>';
            } elseif($style === '1'){
             echo '<div class="caption"><span class="wrapper '.$caption_color.'"><span class="text '.$font_color.'">'.$caption.'</span></span></div>';
            }
          }
        }
        // Metro Pattern
        if(get_property_type() === 'metro') { echo '<div class="pattern pattern-'.get_pattern_id().'"></div>'; }
      echo '</div>';
    }
    if($headline || $copy) {
      echo '<div id="hero-content" class="container">';
        echo '<div class="content">';
          echo '<div class="header-content block '.$class.'">';
            echo '<h1 class="headline">'.$headline.'</h1>';
            echo '<div class="copy">'.$copy.'</div>';
            if($link) { print_link($link); }
          echo '</div>';
          // Add Copy Images for Sylvan Header Option 1
          if(get_property_type() === 'sylvan') {
            if($style === '0') {
              if($secondary_images = get_field('secondary_images')) {
                echo '<div class="secondary-images">';
                  foreach ($secondary_images as $image) {
                    echo '<div class="secondary-image">';
                      print_responsive_image($image['image'],'home_slide_secondary_sm');
                    echo '</div>';
                  }
                echo '</div>';
              }
            }
          }
        echo '</div>';
      echo '</div>';
    }
    // Sylvan Pattern and background
    if(get_property_type() === 'sylvan') {
      echo '<div class="pattern pattern-'.get_pattern_id().' '.$class.'"></div>';
      if($style === '0') { echo '<div class="sylvan-home-header-background"></div>'; }
    }
  echo '</div>';
}

// Gallery Functions
function get_youtube_id($source) {
  preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $source, $matches); 
  $video_ID = $matches[1]; 
  return '<iframe width="1072" height="603" src="https://www.youtube.com/embed/'.$video_ID.'?controls=0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}

function print_gallery() {
  if(have_rows('image_gallery')){
    $type = get_field('gallery_type');
    if($type === "0") {
      get_template_part('template-parts/gallery', 'slider');
    } elseif($type === "1"){
      get_template_part('template-parts/gallery', 'masonry');
    } else { echo 'Whoops, something went wrong.'; }
  }
}

function print_gallery_headline() {
  echo '<div id="gallery-header-content">';
    echo '<div class="header-content block">';
    if($headline = get_field('headline')) { echo '<h1 class="headline">'.$headline.'</h1>'; }
    if($copy =get_field('copy')) { echo '<div class="copy">'.$copy.'</div>'; }
    echo '</div>';
  echo '</div>';
}

function print_videos() {
  $type = get_field('gallery_type');
  if($type === "0") {
    if($videos = get_field('videos')) {
      echo '<div id="videos-wrapper">';
      $i = 0;
      foreach ($videos as $video) { $i++;
        echo '<div class="video-container row-'.$i.' block">';
          echo '<div class="container">';
            echo '<div class="content">';
              echo '<div class="video">'.get_youtube_id($video['content']).'</div>';
              echo '<div class="video-content">';
                echo '<h2 class="headline">'.$video['headline'].'</h2>';
                echo '<div class="copy">'.$video['caption'].'</div>';
              echo '</div>';
            echo '</div>';
          echo '</div>';
        echo '</div>';
      }
      echo '</div>';
    }
  }
}

function print_tours() {
  $type = get_field('gallery_type');
  if($type === "0") {
    if($tours = get_field('tours')) {
      echo '<div id="tours-wrapper">';
      $i = 0;
      foreach ($tours as $tour) { $i++;
        echo '<div class="tour-container row-'.$i.' block">';
          echo '<div class="tour-wrapper">';
            echo '<div class="container">';
              echo '<div class="content">';
                echo '<div class="tour">'.get_youtube_id($tour['360_tour']).'</div>';
              echo '</div>';
            echo '</div>';
            if(get_density() === 'pattern-full') { echo '<div class="tours-pattern pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
          echo '</div>';
          echo '<div class="tour-content block">';
            echo '<div class="container">';
              echo '<div class="content">';
                echo '<h2 class="headline">'.$tour['headline'].'</h2>';
                echo '<div class="copy">'.$tour['caption'].'</div>';
                echo '</div>';
              echo '</div>';
            echo '</div>';
          echo '</div>';
        }
      echo '</div>';
    }
  }
}

function print_simple_headline($source = null) {
  $option = null;
  if($source == 'blog') {
    $option = get_option('page_for_posts');
  } elseif(is_category()) {
    $option = get_queried_object();
  }
  $headline = get_field('headline_h1', $option);
  $copy = get_field('copy', $option);

  if($headline || $copy) {
    echo '<div id="hero-content">';
      echo '<div class="header-content block">';
        if($headline) { echo '<h1 class="headline">'.$headline.'</h1>'; }
        if($copy) { echo '<div class="copy">'.$copy.'</div>'; }
      echo '</div>';
    echo '</div>';
  }
}

function print_poi_map() {
  echo '<div id="map-container" class="block" aria-label="Points of Interest Map">';
    echo '<div class="ddl-label">Filter by Category:</div>';
    echo '<div class="category-filter">';
      echo '<ul id="map-categories"></ul>';
    echo '</div>';
    echo '<div class="map-wrap">';
      print_categories();
      echo '<div id="map"></div>';
    echo '</div>';
  echo '</div>';
}

function print_neighborhood_content() {
  // Assign alignment class if Sylvan.
  $alignment = get_field('alignment');
  $alignment_class = (get_property_type() === 'sylvan') ? ' '.$alignment : '' ;
  echo '<div class="neighborhood-page-content'.$alignment_class.'">';
    print_simple_headline();
    if(get_property_type() === 'sylvan') {
      $image_layout = get_field('image_layout');
      $list_image = get_field('image');
      $large_image = get_field('large_image');
      $left_image = get_field('left_image');
      $right_image = get_field('right_image');
      if($list_image || $large_image || $left_image || $right_image) {
        echo '<div class="image-content">';
          if($image_layout === 'single') {
            echo '<div class="image-single">';
              echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';
              print_responsive_image($list_image, 'list_img_single');
            echo '</div>';
          }
          else if($image_layout === 'three') {
            echo '<div class="image-large">';
              print_responsive_image($large_image, 'list_img_lrg');
            echo '</div>';
            echo '<div class="bottom-images">';
              echo '<div class="sm-img left">';
              if ($alignment === 'right') { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
                print_responsive_image($left_image, 'list_img_small');
              echo '</div>';
              echo '<div class="sm-img right">';
                if ($alignment === 'left') { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
                print_responsive_image($right_image, 'list_img_small');
              echo '</div>';
            echo '</div>';
          }
        echo '</div>';
      }
    }
  echo '</div>';
}

function print_header_no_image($source = null){
  $class = (get_density() != 'pattern-none') ? '' : 'no-pattern';
  echo '<div id="hero" class="no-image '.$class.'">';
    if(get_density() != 'pattern-none') { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
    echo '<div class="container">';
      print_simple_headline($source);
    echo '</div>';
  echo '</div>';
}

function print_hero_image($source = null){
  $option = null;
  if($source == 'blog') { $option = get_option('page_for_posts'); }
  // Regular page
  if(is_page()) {
    echo '<div id="hero-container">';
      if($image = get_field('header_image', $option)){
        if(get_property_type() == 'sylvan') {
          if(is_page_template('page-templates/features.php') || is_page_template('page-templates/pet-policy.php') ) { echo '<div class="sylvan-header-background"></div>'; }
        }
        echo '<div id="hero-image">';
          echo '<div class="img-container">';
          if(get_property_type() == 'sylvan') {
            if(is_page_template('page-templates/features.php') || is_page_template('page-templates/pet-policy.php') ) {
              echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';
              print_responsive_image($image,'features_header','680');
            }
          } else {
            print_responsive_image($image,'home_slide_full','1680');
          }
          echo '</div>';            
        echo '</div>';            
        print_simple_headline($source);
      } else {
        print_header_no_image($source);
      }
    echo '</div>';
  }
  // Blog landing page
  elseif(is_home() || is_category()) {
    echo '<div id="hero-container">';
      print_header_no_image($source);
    echo '</div>';
  }
  // Blog Post
  elseif(is_single()) { // Grab post feature image.
    if (has_post_thumbnail()) {
      echo '<div id="hero-container">';
        if(get_property_type() == 'sylvan') { echo '<div class="sylvan-header-background"></div>'; }
        echo '<div id="hero-image" class="blog-post">';
          echo '<div class="img-wrapper">'; 
            echo '<div class="img-container">'; 
              if(get_property_type() == 'sylvan') { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
              (get_property_type() == 'sylvan')? the_post_thumbnail('sylvan_blog_full') : the_post_thumbnail('blog_full');
            echo '</div>'; 
          echo '</div>';
        echo '</div>';
      echo '</div>';
    }
  } 
}

function print_crosslink_block($source = null){
  $option = null;
  if($source == 'blog') { $option = get_option('page_for_posts'); }
  $headline = get_field('headline_h2',$option);
  $copy = get_field('cross_link_copy',$option);
  $crosslinks = get_field('cross_links',$option);

  if($headline || $copy || $crosslinks) {
    if(!is_page_template('page-templates/contact.php')) {
      if(get_density() != 'pattern-none') {
        echo '<div id="cross-links-pattern" class="pattern-wrapper"><div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div></div>';
      }
    }
    echo '<div id="cross-links" class="block crosslink-block">';
      echo '<div class="inner-wrapper">';
        if($headline || $copy) {
          if($headline) { echo '<h2 class="headline">'.$headline.'</h2>'; }
          if($copy) { echo '<div class="copy">'.$copy.'</div>'; }
        }
        if($crosslinks) {
          echo '<div class="button-wrapper">';
            foreach ($crosslinks as $link) {
              if(!empty($link['link'])) { // Check to make sure there is a URL
                print_link($link['link']);
              }
            }
          echo '</div>';
        }
      echo '</div>';
    echo '</div>';
  }
}

function print_blog_categories(){
  echo '<div class="categories">';
    echo '<ul class="categories-list">';
         wp_list_categories( array('title_li' => '') ); 
    echo '</ul>';
    echo '<div class="mobile-categories">';
      echo '<h2>View By Category</h2>';
      wp_dropdown_categories(array('show_count' => 1, 'title_li' => ''));
    echo '</div>';
  echo '</div>';
}

function print_blog_posts(){
  echo '<div class="posts-container">';
   if (have_posts()) : 
     while (have_posts()) : the_post();
      echo '<div class="post">';
        echo '<div class="image">';
          if (has_post_thumbnail()) { 
            echo '<a class="post-link" href="'.get_the_permalink($post).'">';
              the_post_thumbnail('blog_thumb');
            echo '</a>';
          } else {
            echo '<a class="post-link placeholder" href="'.get_the_permalink($post).'">';
              echo '<div class="no-feature-image pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';
            echo '</a>';
          }
        echo '</div>';
        echo '<div class="post-info">';
          echo '<a class="post-link" href="'.get_the_permalink($post).'">';
            echo '<h2 class="title">'.get_the_title().'</h2>';
          echo '</a>';
         echo '<h3 class="date">'.get_the_date().'</h3>';
         echo '<div class="excerpt">'.flex_content_excerpt('building_blocks', 'simple_content_block', 'copy').'</div>';
         echo '<a class="read-more" href="'.get_the_permalink($post).'">Read More</a>';
        echo '</div>';

      echo '</div>';

     endwhile;
   endif;
  echo '</div>';
}

function print_no_results_copy(){
  if($no_results = get_field('no_results_copy')) {
    echo '<div id="no-results" style="display:none;">'.$no_results.'</div>';
  }
}

function print_floorplan_disclaimer_copy(){
  if($fp_disclaimer = get_field('disclaimer_copy')) {
    echo '<div id="disclaimer" style="display:none;">'.$fp_disclaimer.'</div>';
  }
}

function print_back_to_blog(){
  $page_for_posts = get_option('page_for_posts');
  $blog_url = get_permalink($page_for_posts);
  echo '<div id="back-to-blog">';
    echo '<a href="' . $blog_url . '">Back To Main</a>';
  echo '</div>';
}

function print_pet_policy_content(){
  $headline = get_field('headline_h2');
  $copy = get_field('page_copy');
  $disclaimer = get_field('disclaimer');
  $table = get_field('info_table');

  if($headline || $copy || $table || $disclaimer){
    echo '<div class="pet-policy block">';
      echo '<div class="left">';
        if($headline) echo '<h1 class="headline">'.$headline.'</h1>';
        if($copy) echo '<div class="copy">'.$copy.'</div>';
        if($disclaimer) echo '<div class="disclaimer">'.$disclaimer.'</div>';
      echo '</div>';
      echo '<div class="right">';
        if($table){
          echo '<div class="pet-policy">';
            foreach ($table as $row) {
              echo '<div class="policy-row">';
                echo '<span class="label">'.$row['label'].'</span>';
                echo '<span class="value">'.$row['value'].'</span>';
              echo '</div>';
            }
          echo '</div>';
        }
      echo '</div>';
    echo '</div>';
  }
}

// Print box content for Thank You and 404 pages
function print_box_content($source=null){
  $style = '';
  if($source === '404') { $option = 'options'; }
  $headline = get_field('headline_h1',$option);
  $copy = get_field('copy',$option);
  $crosslink = get_field('cross_link',$option);
  if($bg = get_field('background_image',$option)){
    $style = 'style="background-image:url('.$bg['url'].')"';
  }
  echo '<div id="thank-you" role="main" '.$style.'>';
    if(get_property_type() === 'metro') { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
    echo '<div class="overlay"></div>';
    echo '<div class="thank-you-content container">';
      if(get_property_type() === 'sylvan') { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
      echo '<div class="content">';
        echo '<div class="box">';
          if($headline || $copy || $crosslink) {
            echo '<div class="inner-wrapper block">';
              if($headline) { echo '<h1 class="headline">'.$headline.'</h1>'; }
              if($copy) { echo '<div class="copy">'.$copy.'</div>'; }
              if($crosslink) {
                echo '<div class="button-wrapper">';
                  print_link($crosslink);
                echo '</div>';
              }
            echo '</div>';
          }
        echo '</div>';
      echo '</div>';
    echo '</div>';
  echo '</div>';
}

// Saves area data to json file on page save
function save_neighborhood_json($post_id){
  if (get_page_template_slug($post_id) == 'page-templates/neighborhood.php'){
    if ($rows = get_field('points_of_interest_map',$post_id)){
      $pois = array(
        'categories' => array()
      );

      // Categories
      foreach($rows as $category){

        // POI Location
        $locations = array();
        if (!empty($category['pois'])){
          foreach ($category['pois'] as $poi){
            $poi_data = array(
              'name' => $poi['name'],
              'url' => $poi['website']
            );
            if ($poi['address']){
              $poi_data['address'] = str_replace(', United States','',$poi['address']['address']); // Strip country which ACF Google Maps plugin sometimes appends
              $poi_data['lat'] = (float) $poi['address']['lat'];
              $poi_data['lng'] = (float) $poi['address']['lng'];
            }
            $locations[] = $poi_data;
          }
        }
       
        // Marker information array
        $marker = array();

        $marker_data = array(
          'width' => $category['category_marker']['width'],
          'height' => $category['category_marker']['height'],
          'url' => $category['category_marker']['url']
        );
        
        $marker = $marker_data;

        // Assemble Category Array
        $pois['categories'][] = array(
          'name' => $category['category'],
          'marker' => $marker,
          'pois' => $locations
        );
      }

      // Property Information
      $property = array(
        'property_name' => get_bloginfo('name'),
        'address' => get_field('address', 'options'),
        'city' => get_field('city', 'options'),
        'state' => get_field('state', 'options'),
        'zip' => get_field('zip', 'options'),
        'lat' => (float) get_field('latitude','options'),
        'lng' => (float) get_field('longitude','options')
      );
      if ($property_map_marker = get_field('property_map_marker', 'options')){
          $property['property_map_marker'] = array(
            'url' => $property_map_marker['url'],
            'width' => $property_map_marker['width'],
            'height' => $property_map_marker['height']
          );
      } else {
        $property['property_map_marker'] = array(
          'url' => get_bloginfo('url').'/wp-content/uploads/property-marker.png',
          'width' => 60,
          'height' => 60
        );
      }
      $pois['property'] = $property;

      file_put_contents(get_template_directory() . '-child/JSON/neighborhood.json',json_encode($pois));
    }
  }
}
add_action( 'save_post', 'save_neighborhood_json' );


function time_ago($date,$granularity=2) {
  $difference = time() - $date;
  $retval = '';
  $periods = array('decade' => 315360000,
    'year' => 31536000,
    'month' => 2628000,
    'week' => 604800, 
    'day' => 86400,
    'hour' => 3600,
    'minute' => 60,
    'second' => 1);
  if ($difference < 60) { // less than 60 seconds ago, let's say "just now"
    $retval = "added just now";
    return $retval;
  } else {                            
    foreach ($periods as $key => $value) {
      if ($difference >= $value) {
        $time = floor($difference/$value);
        $difference %= $value;
        $retval .= ($retval ? ' ' : '').$time.' ';
        $retval .= (($time > 1) ? $key.'s' : $key);
        $granularity--;
      }
      if ($granularity == '0') { break; }
    }
    return $retval.' ago';      
  }
}


function get_stars($starNumber){
  $number = floatval($starNumber);
  echo '<div class="stars">';
    for($x = 1; $x <= $number; $x++) {
      echo '<span class="icon-star"></span>';
    }
    if (strpos($number,'.')) {
      $decimal = floatval(substr($number, 1));
      if($decimal > 0.01 && $decimal <= 0.25) {
        echo '<span class="icon-star-empty"></span>';
      } elseif($decimal >= 0.26 && $decimal <= 0.50){
        echo '<span class="icon-star-25"></span>';
      } elseif($decimal >= 0.51 && $decimal <= 0.75){
        echo '<span class="icon-star-50"></span>';
      } elseif($decimal >= 0.76 && $decimal <= 0.99) {
        echo '<span class="icon-star-75"></span>';
      }
      $x++;
    }
    while ($x <= 5) {
      echo '<span class="icon-star-empty"></span>';
      $x++;
    }
  echo '</div>';
}


function get_kingsley_reviews(){
  $show_badges = get_field('show_badges', 'options');
  $show_average = get_field('show_average_review', 'options');
  $show_reviews = get_field('show_reviews', 'options');
  $api_token = get_field('kingsley_user_id','options');
  // If any of the options are true, start building Kingley module.
  if($show_badges || $show_average || $show_reviews) {
    echo '<div id="kingsley-content">';
      if($show_badges){
        if($badges = get_field('ratings_badges', 'options')){
          echo '<div class="badge-container">';
            foreach ($badges as $image) {
              echo '<div class="badge">';
              if($image['url']){
                $link_label = ($image['badge_image']['caption']) ? 'Link to '.$image['badge_image']['caption'] : '';
                echo '<a href="'.$image['url'].'" aria-label="'.$link_label.'" target="_blank">';
                  print_responsive_image($image['badge_image'],'kingsley');
                echo '</a>';
              } else {
                print_responsive_image($image['badge_image'],'kingsley');
              }
              echo '</div>';
            }
          echo '</div>';
        }
      }    
      $remote_wp = wp_remote_get( "https://api.kingsleyportal.com/api/standard/reviewdata/ReviewsFeedData?userid=".$api_token );
      $response = json_decode( $remote_wp['body'] );
      if( $remote_wp['response']['code'] == 200 ) {
        // echo '<pre>'; print_r($response); echo '</pre>'; // Uncomment this for Kingsley response data.
        $overallReviewData = $response->QuestionList[0];
        $reviewData = $response->CommentList;
        if($api_token) {
          if($show_average) {
            if(isset($overallReviewData) && $overallReviewData->RatingScore > 3) {
              echo ($show_badges) ? '<div class="average-rating">' : '<div class="average-rating no-badges">' ;
                get_stars($overallReviewData->RatingScore);
                echo '<div class="review-info">';
                  echo '<div class="average">'.$overallReviewData->RatingScore.' Star Rating</div>';
                  echo '<div class="total-reviews">'.$overallReviewData->ResponseCount.' reviews</div>';
                echo '</div>';
              echo '</div>';
            }
          }
          if($show_reviews){
            $class_modifier = ($show_badges)? '-has-badges' : '' ;
            $class = (!isset($overallReviewData) || $overallReviewData->RatingScore < 3)? ' no-reviews'.$class_modifier : '' ;
            echo ($show_badges || $show_average) ? '<div class="review-wrapper'.$class.'">' : '<div class="review-wrapper solo">' ;
              foreach( $reviewData as $review ) {
                if(strlen($review->CommentText) > 500) {
                    unset($review);
                } else {
                  $time = strtotime($review->CommentDate);
                  // Build reviews
                  echo '<div class="review">';
                    echo '<div class="star-container">';
                      if(isset($review->OSat) && $review->OSat > 3) { 
                        get_stars($review->OSat);
                      }
                    echo '</div>';
                    $user = ($review->RespondentName === "Certified Resident") ? 'Current Resident' : $review->RespondentName;
                    echo '<div class="review-details"><span class="user">'.$user.'</span><span class="date">'.time_ago($time, 1).'</span></div>';
                    echo '<div class="review-text">"'.$review->CommentText.'"</div>';
                  echo '</div>';
                }
              }
            echo '</div>';
          }    
        echo '</div>';   
      }
      elseif ( $remote_wp['response']['code'] == 400 ) {
        echo '<div class="config-error">' . $remote_wp['response']['message'] . ': </div>' . $response->meta->error_message;
      }
      echo '</div>';
    } else {
      echo '<div class="config-error">Hmm, something seems to be configured incorrectly.</div>';
      echo '</div>';
    }
  }
}



?>