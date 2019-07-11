<?php

function print_link($link, $class = null) {
  if (isset($link)) {
    echo '<div class="button">';
      if(isset($class)){ echo '<a class="'.$class.' '.str_replace(' ', '-', strtolower($link['title'])).'" href="'.$link['url'].'" target="'.$link['target'].'" aria-label="'.ucfirst(strtolower($link['title'])).' link.">'; }
      else { echo '<a class="'.str_replace(' ', '-', strtolower($link['title'])).'" href="'.$link['url'].'" target="'.$link['target'].'" aria-label="'.ucfirst(strtolower($link['title'])).' link.">'; }
        echo $link['title'];
      echo '</a>';
    echo '</div>';
  }
}

function print_headline($headline) {
  $index = get_row_index();
  if($headline) {
    // Make first block's headline the H1, the rest are H2.
    echo ($index == '1') ? '<h1 class="headline">'.$headline.'</h1>' : '<h2 class="headline">'.$headline.'</h2>';
  }
}

function print_pattern_break_block($id = null) {
  echo '<div id="row-'.$id.'" class="block pattern-wrapper">';
  if(get_density() != 'pattern-none') {
    echo '<div class="pattern pattern-bg pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';
  } else {
    echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';
  }
  echo '</div>';
}

function get_pattern_opacity(){
  if($opacity = get_field('opacity','options')) {
    $style = ($opacity === '100') ? 'style="opacity: 1"' :'style="opacity: .'.$opacity.';"';
  }
  return $style;
}

function print_variable_content() {
  $headline = get_sub_field('headline');
  $copy = get_sub_field('copy');
  $link = get_sub_field('link');
  $links = get_sub_field('links');
  if($headline || $copy || $link) {  
    echo '<div class="variable-content-content">';
      if($headline) { echo '<h3 class="headline">'.$headline.'</h3>'; }
      if($copy) { echo '<div class="copy">'.$copy.'</div>'; }
      if($link) { print_link($link); }
      if($links) {
        
        foreach ($links as $link) {echo '<div class="buttons-wrapper">';
          print_link($link['link']);
        echo '</div>';
        }
      }
    echo '</div>';
  }
}

function print_variable_img() {
  if($image = get_sub_field('lrg_image')) {
    echo '<div class="variable-content-img">';
      // print_responsive_image($image,$image_size,$max_width);
      print_responsive_image($image,'variable_img_large');
    echo '</div>';
  }
}

function print_variable_pattern() {
  $option = get_sub_field('image_or_fill');
  $image = get_sub_field('sm_image');
  if($option == '0' && $image){
    echo '<div class="variable-content-sm-img">';
      echo print_responsive_image($image,'variable_img_small');
    echo '</div>';
  } elseif($option == '1'){
    echo '<div class="variable-content-fill"></div>';
  } elseif($option == '2'){
    echo '<div class="variable-content-fill-pattern"><div class="pattern pattern-'.get_pattern_id().'"></div></div>';
  }
}

function print_variable_content_block($id, $length, $previous = null) {
  $index = get_row_index();
  $row = ($id === 2) ? 'second' : '';
  if(have_rows('variable_block_options')):
    echo (get_density() === 'pattern-full') ? '<div id="row-'.$id.'" class="block variable-content metro-element'.$row.' '.get_density().'">' : '<div id="row-'.$id.'" class="block variable-content metro-element'.$row.'">';
      // Add the accent pattern if ALL the following conditions are met:
      // - is homepage
      // - 'variable content block' is in postion 1
      // - 'content' sub-block is not in the 3rd postion of the 'variable content block'.
      // - pattern density level is set to 'Full' on options page.
      if(is_front_page()) {
        if($index === 1 ) {
          if(get_variable_block_order($index, 'content')) {
            if(get_density() === 'pattern-full') {
              echo '<div class="accent-pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';
            }
          }
        }
      }
      
      while ( have_rows('variable_block_options') ) : the_row();
          
        // Content Layout
        if( get_row_layout() == 'content' ):
          print_variable_content();
        
        // Image Layout
        elseif( get_row_layout() == 'image' ):
          print_variable_img();

        // Image or Color Fill
        elseif( get_row_layout() == 'image_or_color_fill' ):
          print_variable_pattern();
        
        endif;  // End of Blocks
      endwhile;  // End flex content loop.  Nothing more to see here.
    echo '</div>';
    else :
      // No layouts found
  endif;
}


function get_variable_block_order($id = '', $layout = 'content') {
  if (class_exists('acf')) { 
    if (have_rows('building_blocks', $id)) { 
      
      $blocks = get_field('building_blocks', $id);

      $content = array();     
      foreach ( $blocks as $block ) {
        if($block['acf_fc_layout'] === 'variable_content_block') {
          foreach ($block['variable_block_options'] as $contentBlock) {
            $content[] = $contentBlock['acf_fc_layout'];
          }
        }
      }

      if($content[2] != $layout) {
        return true;
      } else {
        return false;
      }
    } else {
      return '<p class="error">Advanced Custom Fields is required for <code>get_variable_block_order()</code> to work.</p>';
    } 
  }
}


/* Checks previous layout.
 * @param - $id - the id of the post you are trying to target. get_layout_rows() starts at 1 for some reason, so we need to remove 2 in order to get to 0 (or id you want to target).
 * @param - $layout - the name of the flexible content layout: 'simple_content_block' as the default
 * @return - true/false
*/
function check_previous_flex_layout($id = '', $layout = 'simple_content_block') {
  $index = $id - 2;
  if (class_exists('acf')) { 
    if (have_rows('building_blocks', $id)) { 
      $blocks = get_field('building_blocks', $id);
      $content = array();
      foreach ( $blocks as $block ) {
        $content[] = $block['acf_fc_layout'];
      }
      if($content[$index] === $layout) {
        return true;
      } else {
        return false;
      }
    } else {
      return '<p class="error">Advanced Custom Fields is required for <code>check_previous_flex_layout()</code> to work.</p>';
    } 
  }
}

function print_simple_content_block($id, $length) {
  $index = (get_row_index() === 1) ? 'first' : '';  
  $lastIndex = ($id === $length) ? 'last' : '';
  $copy = get_sub_field('copy');
  $link = get_sub_field('call_to_action_button');

  // Define dynamic Headline.
  if(is_single()) {  // Check to see if this is a post or page
    if($index != 1) { // Make sure it is not first block.
      if(get_sub_field('headline')) {  // Check to see if headline subfield has content
        $headline = get_sub_field('headline');
      } else {
        $headline = null;
      }
    } else { // This is the first block's scenario.
      if(get_sub_field('headline')) {  // Check to see if headline subfield has content
        $headline = get_sub_field('headline');
      } else {
        $headline = get_the_title(); // Grab's post title as default.
      }
    }
  } elseif(is_page()) { // Use the defined Headline (H1) field for page H1 when viewing page.
    $headline = get_sub_field('headline');
  }

  if($headline || $copy || $link) {  
    echo '<div id="row-'.$id.'" class="block simple-content '.$index.' '.$lastIndex.' '.get_density().'">';
      if($headline != null) { print_headline($headline); }
      if(is_single() && get_row_index() === 1) { echo '<div class="date">'.get_the_date().'</div>'; } // Print Date on blog posts.
      if($copy) { echo '<div class="copy">'.$copy.'</div>'; }
      if($link) { print_link($link); }
    echo '</div>';
  }
}

function print_floor_plan_block($id, $length) {
  $headline = get_sub_field('headline');
  $copy = get_sub_field('copy');
  $link = get_sub_field('link');
  $floorplans = get_sub_field('floor_plan_images');
  if($headline || $copy || $link) {  
    echo '<div id="row-'.$id.'" class="block floor-plans">';
      echo '<div class="floor-plan-container">';
        print_headline($headline);
        if($copy) { echo '<div class="copy">'.$copy.'</div>'; }
        echo '<div class="floor-plan-outer-wrap">';
          if($floorplans) {
            echo '<div class="floor-plan-carousel">';
            foreach ($floorplans as $image) {
              echo '<div class="floor-plan-wrapper">';
                echo '<div class="floor-plan-image">';
                  print_responsive_image($image,'home_floor_plan');
                echo '</div>';
                echo '<div class="label">'.$image['caption'].'</div>';
              echo '</div>';
            }
            if(get_property_type() === 'sylvan') {
              if($link) {
                echo '<div class="fp-block-link">';
                  echo '<a href="'.$link['url'].'" target="'.$link['target'].'" aria-label="'.ucfirst(strtolower($link['title'])).' link.">';
                    echo '<div class="title-wrapper">';
                      echo '<span class="icon-nav-arrow"></span>';
                      echo '<div class="link-title">'.$link['title'].'</div>';
                    echo '</div>';
                  echo '</a>';
                echo '</div>';
              }
            }
            echo '</div>';
          }
          if(get_property_type() === 'metro'){
            if($link) { print_link($link, 'body-content'); }
          }
        echo '</div>';
      echo '</div>';
    echo '</div>';
  }
}

// Instagram Functions
function print_instagram_block($id, $length) {
  $headline = get_sub_field('headline');
  $layout = get_sub_field('layout');
  if($headline || $layout) {
    echo '<div id="row-'.$id.'" class="block instagram">';
      print_headline($headline);
      get_instagram_pics($layout);
    echo '</div>';
  }
}

function instagram_three_image_layout($imageData){
  echo '<div id="insta-feed" class="layout-three">';
    $i = 0;
    foreach ($imageData as $image) {
      if($i == 3) break;
      $profile = $image['user']['username'];
      $full_name = $image['user']['full_name'];
      echo '<a href="https://www.instagram.com/'.$profile.'" target="_blank" aria-label="'.$full_name.' Instagram"><div class="image-wrap block" style="background-image: url('.$image['images']['standard_resolution']['url'].')" aria-label="'.$image['caption']['text'].'"></div></a>';
      $i++;
    }     
    echo '<div class="social-wrap block">';
      echo '<div class="social-wrap-pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';  
      echo '<div class="social-wrap-inner">';
        echo '<div class="label">Follow Us</div>';
        print_social_icons();
      echo '</div>';
    echo '</div>';    
  echo '</div>';
}

function instagram_six_image_layout($imageData){
  echo '<div id="insta-feed" class="layout-six">';
    $i = 0;
    foreach ($imageData as $image) {
      if($i == 5) break;
      $profile = $image['user']['username'];
      $full_name = $image['user']['full_name'];
      echo '<a href="https://www.instagram.com/'.$profile.'" target="_blank" aria-label="'.$full_name.' Instagram"><div class="image-wrap block" style="background-image: url('.$image['images']['standard_resolution']['url'].')" aria-label="'.$image['caption']['text'].'"></div></a>';
      $i++;
    }
    echo '<div class="social-wrap block">';
      echo '<div class="social-wrap-pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>';  
      echo '<div class="social-wrap-inner">';
        echo '<div class="label">Follow Us</div>';
        print_social_icons();
      echo '</div>';
    echo '</div>';  
  echo '</div>';
}

function get_instagram_pics($layout){
  $token = get_field('instagram_api_token','options');
  $userId = get_field('instagram_user','options');
  $apiCall = wp_remote_get( "https://api.instagram.com/v1/users/" . $userId . "/media/recent/?access_token=" . $token );   
  $images = json_decode($apiCall['body'] , true);
  if( $apiCall['response']['code'] == 200 ) {
    $imageData = $images['data'];
    ($layout == '0') ? instagram_three_image_layout($imageData) : instagram_six_image_layout($imageData);   
  } elseif ( $apiCall['response']['code'] == 400 ) {
    echo '<b>' . $apiCall['response']['message'] . ': </b>' . $images->meta->error_message;
  }
}

function print_list_layouts_block($id, $length) {
  $headline = get_sub_field('headline');
  $list_items = get_sub_field('list_items');
  $disclaimer = get_sub_field('disclaimer');
  // Sylvan Variables
  $alignment = get_sub_field('alignment');
  $image_layout = get_sub_field('image_layout');
  $list_image = get_sub_field('image');
  $large_image = get_sub_field('large_image');
  $left_image = get_sub_field('left_image');
  $right_image = get_sub_field('right_image');
  if($list_items) {
    // Assign alignment class if Sylvan.

    if(get_property_type() === 'sylvan') {
      $alignment_class = ($list_image || $large_image || $left_image || $right_image) ? ' '.$alignment : '' ;
    }

    echo '<div id="row-'.$id.'" class="block list-layout'.$alignment_class.'">';
      echo '<div class="list-content">';
        print_headline($headline);
        if(get_property_type() === 'sylvan') { echo '<div class="line"></div>'; }
        echo '<div class="lists-container">';
          echo '<div class="list-wrapper">';
            echo '<ul>';
              foreach ($list_items as $item) {
                echo '<li>'.$item['bullet_point'].'</li>';
              }
              if(get_property_type() === 'sylvan') {
                if($disclaimer){ echo '<li class="disclaimer">'.$disclaimer.'</li>'; }
              }
            echo '</ul>';
          echo '</div>'; 
          if(get_property_type() === 'metro') {
            if($disclaimer){ echo '<div class="disclaimer">'.$disclaimer.'</div>'; }
          }
        echo '</div>'; 
      echo '</div>'; 
      if(get_property_type() === 'sylvan') {
        if($list_image || $large_image || $left_image || $right_image) {
          echo '<div class="image-content">';
            if($image_layout === 'single') {
              echo '<div class="image-single">';
                print_responsive_image($list_image, 'list_img_single');
              echo '</div>';
            }
            else if($image_layout === 'three') {
              echo '<div class="image-large">';
              // if(wp_is_mobile()) {
              //   print_responsive_image($large_image, 'list_img_small');
              // } else {              
                print_responsive_image($large_image, 'list_img_lrg');
              // }
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
}

function print_three_image_layout_block($id, $length) {
  $images = get_sub_field('images');
  $size = 'three_images';
  if($images) {
    echo '<div id="row-'.$id.'" class="block three-image-layout metro-element">';
      foreach ($images as $image) {
        echo '<div class="img-wrap">';
          print_responsive_image($image,'three_images');
        echo '</div>';
      }
    echo '</div>';
  }
}

function print_full_image_block($id, $length) {
  if($image = get_sub_field('full_image')) {
    echo '<div id="row-'.$id.'" class="block full-image">';
      echo '<div class="in-content-image full-width">';
        print_responsive_image($image, 'blog_full');
        if($image['caption']) {
          echo '<div class="img-caption">'.$image['caption'].'</div>';
        }
      echo '</div>';
    echo '</div>';
  }
}

function print_content_with_image_block($id, $length) {
  $content = get_sub_field('content');
  $image = get_sub_field('image');
  if($content) {
    echo '<div id="row-'.$id.'" class="block content-with-image">';
      echo '<div class="copy">';
        if($image) {
          $alignment = 'img-'.get_sub_field('alignment');
          echo '<div class="in-content-image '.$alignment.'">';
            print_responsive_image($image, 'in_blog');
            if($image['caption']) {
              echo '<div class="img-caption">'.$image['caption'].'</div>';
            }
          echo '</div>';
        }
        echo $content;
      echo '</div>';
    echo '</div>';
  }
}

function print_content_and_images_block($id, $length) {
  $length = (count(get_sub_field('copy_and_image_row')) === 1) ? ' single' : '';
  if ( have_rows('copy_and_image_row') ) : $row = 0;
    echo '<div id="row-'.$id.'" class="block content-and-images sylvan-element'.$length.'">';
      while ( have_rows('copy_and_image_row') ) : the_row(); $row++;
        $row_class = ($row % 2 === 0) ? 'even' : 'odd';
        echo '<div class="row-wrapper row-'.$row.' '.$row_class.'">';
          echo '<div class="inner-wrapper">';
          $imageL = get_sub_field('image_left');
          $imageR = get_sub_field('image_right');
          $content = get_sub_field('copy_area');
          if($imageL) {
            echo '<div class="block-img left">';
              if ($row % 2 != 0) { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
              print_responsive_image($imageL,'copy_and_images_img');
            echo '</div>';
          }
          if($content) {
            $headline = $content['headline'];
            $copy = $content['copy'];
            $link = $content['link'];
            echo '<div class="block-content">';
              if($headline) { echo '<h3>'.$headline.'</h3>'; }
              if($copy) { echo '<div class="block-content-copy">'.$copy.'</div>'; }
              if($link) { print_link($link); }
            echo '</div>';
          }
          if($imageR) {
            echo '<div class="block-img right">';
              if ($row % 2 === 0) { echo '<div class="pattern pattern-'.get_pattern_id().'" '.get_pattern_opacity().'></div>'; }
              print_responsive_image($imageR,'copy_and_images_img');
            echo '</div>';
          }
          echo '</div>';
        echo '</div>';
      endwhile;
    echo '</div>';
  endif;
}

// Flexible Content (Page Building Blocks)
// This references NUMEROUS functions within this file. Take care when modifying this function.
function print_page_building_blocks($source = null) {
  $option = null;
  if($source == 'blog') {
    $option = get_option('page_for_posts');
  } elseif(is_category()) {
    $option = get_queried_object();
  }
  // Check if the flexible content field has rows of data
  if(have_rows('building_blocks', $option)): $id = 0; // Set ID before incrementing.
    // Loop through the rows of data
    $length = count(get_field('building_blocks', $option));
    while ( have_rows('building_blocks', $option) ) : the_row(); $id++;  // Increment ID
      
      // Simple Content Block
      if( get_row_layout() == 'simple_content_block' ):
        print_simple_content_block($id, $length);
      
      // Variable Content Block (Metro Only)
      elseif( get_row_layout() == 'variable_content_block' ):
        print_variable_content_block($id, $length);
      
      // Pattern Break Block
      elseif( get_row_layout() == 'pattern_break' ):
        print_pattern_break_block($id, $length);
      
      // Floor Plan Block
      elseif( get_row_layout() == 'floor_plan_block' ):
        print_floor_plan_block($id, $length);
      
      // Instagram Block
      elseif( get_row_layout() == 'instagram_feed' ):
        print_instagram_block($id, $length);

      // List Layout Block
      elseif( get_row_layout() == 'list_layouts' ):
        print_list_layouts_block($id, $length);

      // Three Image Layout Block (Metro only)
      elseif( get_row_layout() == 'three_image_layout' ):
        print_three_image_layout_block($id, $length);
      
      // Blog Full Image
      elseif( get_row_layout() == 'full_image' ):
       print_full_image_block($id, $length);

      // Blog Content with Image
      elseif( get_row_layout() == 'content_with_image' ):
       print_content_with_image_block($id, $length);

      // Copy and Images Block (Sylvan 0nly)
      elseif( get_row_layout() == 'copy_and_images' ):
        print_content_and_images_block($id, $length);

      endif;  // End of Blocks
    endwhile;  // End flex content loop.  Nothing more to see here.
  else :
    // No layouts found
  endif;
}

function print_blog_page_building_blocks(){
  // Check if the flexible content field has rows of data
  if(have_rows('sub_content_building_blocks')): $id = 0; // Set ID before incrementing.
    echo '<div class="post-sub-content">';
    // Loop through the rows of data
    $length = count(get_field('sub_content_building_blocks'));
    while ( have_rows('sub_content_building_blocks') ) : the_row(); $id++;  // Increment ID
      
      // Simple Content Block
      if( get_row_layout() == 'simple_content_block' ):
        print_simple_content_block($id, $length);

      // Variable Content Block (Metro Only)
      elseif( get_row_layout() == 'variable_content_block' ):
        print_variable_content_block($id, $length);
      
      // Pattern Break Block
      elseif( get_row_layout() == 'pattern_break' ):
        print_pattern_break_block($id, $length);
      
      // Copy and Images Block (Sylvan 0nly)
      elseif( get_row_layout() == 'copy_and_images' ):
        print_content_and_images_block($id, $length);

      endif;  // End of Blocks
    endwhile;  // End flex content loop.  Nothing more to see here.
  else :
    // No layouts found
    echo '</div>';
  endif;
}

?>